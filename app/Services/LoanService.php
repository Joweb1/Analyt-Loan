<?php

namespace App\Services;

use App\DTOs\LoanApplicationDTO;
use App\Jobs\ProcessLoanAttachment;
use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\ValueObjects\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoanService
{
    /**
     * Create a new loan.
     */
    public function createLoan(LoanApplicationDTO $dto, $attachment = null, $collateralId = null): Loan
    {
        $loan = DB::transaction(function () use ($dto, $collateralId) {
            $user = Auth::user();
            $data = $dto->toArray();
            $data['organization_id'] ??= $user->organization_id;
            $data['loan_officer_id'] ??= $user->id;

            if (($data['repayment_cycle'] ?? null) === 'monthly') {
                $data['collection_group'] = 'Monthly Collections';
            }

            // Apply Risk-Based Pricing if interest_rate is not explicitly set (or 0)
            if ($data['interest_rate'] <= 0) {
                $borrower = Borrower::find($data['borrower_id']);
                if ($borrower) {
                    // Default to 10% if product not found or rate missing
                    $defaultRate = 10.0;
                    $data['interest_rate'] = $this->suggestInterestRate($borrower, $defaultRate);
                }
            }

            // If an Admin creates a loan, approve it immediately
            if ($user->hasRole('Admin')) {
                $data['status'] = 'approved';
            } else {
                $data['status'] ??= 'applied';
            }

            // Ensure release_date defaults to organization's business date
            $systemNow = now();
            Log::info('Loan Creation - System Now resolved to: '.$systemNow->toDateTimeString());

            $data['release_date'] ??= $systemNow;
            Log::info('Loan Creation - Release Date set to: '.($data['release_date'] instanceof Carbon ? $data['release_date']->toDateTimeString() : $data['release_date']));

            // Ensure loan_number is set
            if (empty($data['loan_number'])) {
                $year = now()->year;
                $data['loan_number'] = 'LN-'.$year.'-'.strtoupper(Str::random(5));
            }

            $loan = Loan::create($data);

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }

            // Record Upfront Fees immediately upon creation/application
            $this->recordUpfrontFees($loan);

            // Automatically generate schedule upon creation
            $this->generateRepaymentSchedule($loan);

            return $loan;
        });

        if ($attachment) {
            // Store temporarily and dispatch background job
            $tempPath = $attachment->store('temp-attachments', 'local');
            ProcessLoanAttachment::dispatch($loan, $tempPath, $attachment->getClientOriginalName());
        }

        return $loan;
    }

    /**
     * Generate the repayment schedule for a loan.
     */
    public function generateRepaymentSchedule(Loan $loan): void
    {
        $numRepayments = max(1, $loan->num_repayments ?? 1);

        /** @var Money $principal */
        $principal = $loan->amount;
        $principalShare = $principal->divide($numRepayments);

        // Interest is distributed evenly.
        $totalInterest = $loan->getTotalExpectedInterest();
        $interestShare = $totalInterest->divide($numRepayments);

        $currency = $principal->getCurrency();
        $startDate = Carbon::parse($loan->release_date ?? now());
        $cycle = $loan->repayment_cycle ?? 'monthly';

        // Collection Day Mapping
        $dayMap = [
            'Monday Group' => 1,
            'Tuesday Group' => 2,
            'Wednesday Group' => 3,
            'Thursday Group' => 4,
            'Friday Group' => 5,
        ];
        $targetDayIndex = $loan->collection_group ? ($dayMap[$loan->collection_group] ?? null) : null;

        // Delete existing schedules
        $loan->scheduledRepayments()->delete();

        $currentDate = $startDate->copy();

        for ($i = 1; $i <= $numRepayments; $i++) {
            if ($cycle === 'daily') {
                $currentDate->addDay();
                // Skip Saturday (6) and Sunday (0)
                while ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                }
            } else {
                match ($cycle) {
                    'weekly' => $currentDate->addWeek(),
                    'biweekly' => $currentDate->addWeeks(2),
                    'monthly' => $currentDate->addWeeks(4), // 4 weeks = 1 month
                    'yearly' => $currentDate->addWeeks(48), // 12 months * 4 weeks
                    default => $currentDate->addWeeks(4),
                };
            }

            $dueDate = $currentDate->copy();

            // Snap to collection day for non-daily and non-monthly cycles
            // Monthly loans follow their release date offset precisely
            if ($targetDayIndex !== null && ! in_array($cycle, ['daily', 'monthly'])) {
                // Find the nearest target day in the same week
                // If it's already past that day in the week, it goes to the target day of that week
                // We use setISODate to force it to the target day of the current week of $dueDate
                $dueDate->setISODate($dueDate->year, $dueDate->weekOfYear, $targetDayIndex);
            }

            ScheduledRepayment::create([
                'organization_id' => $loan->organization_id,
                'loan_id' => $loan->id,
                'due_date' => $dueDate,
                'principal_amount' => $principalShare,
                'interest_amount' => $interestShare,
                'penalty_amount' => new Money(0, $currency),
                'installment_number' => $i,
                'status' => 'applied',
            ]);
        }

        $loan->refreshRepaymentStatus();
    }

    /**
     * Record upfront fees as transactions.
     */
    protected function recordUpfrontFees(Loan $loan): void
    {
        $processingFee = $loan->getCalculatedProcessingFee();
        if ($processingFee->isPositive()) {
            TransactionService::record(
                type: 'processing_fee',
                amount: $processingFee,
                user: $loan->borrower->user,
                related: $loan,
                paymentMethod: 'bank_transfer',
                notes: "Upfront Processing Fee for Loan #{$loan->loan_number}"
            );
        }

        $insuranceFee = $loan->getCalculatedInsuranceFee();
        if ($insuranceFee->isPositive()) {
            TransactionService::record(
                type: 'insurance_fee',
                amount: $insuranceFee,
                user: $loan->borrower->user,
                related: $loan,
                paymentMethod: 'bank_transfer',
                notes: "Upfront Insurance Fee for Loan #{$loan->loan_number}"
            );
        }
    }

    /**
     * Suggest an interest rate based on the borrower's trust score.
     */
    public function suggestInterestRate(Borrower $borrower, float $baseRate): float
    {
        $score = $borrower->trust_score;

        if ($score >= 80) {
            // Excellent: 20% discount on base rate
            return max(1.0, $baseRate * 0.8);
        } elseif ($score >= 60) {
            // Good: 10% discount
            return max(1.0, $baseRate * 0.9);
        } elseif ($score < 30) {
            // High Risk: 25% premium
            return $baseRate * 1.25;
        } elseif ($score < 10) {
            // Extremely High Risk: 50% premium
            return $baseRate * 1.5;
        }

        return $baseRate;
    }

    /**
     * Update an existing loan.
     */
    public function updateLoan(Loan $loan, LoanApplicationDTO $dto, $attachment = null, $collateralId = null): Loan
    {
        DB::transaction(function () use ($loan, $dto, $collateralId) {
            $loan->update($dto->toArray());

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }

            // Regenerate schedule upon update
            $this->generateRepaymentSchedule($loan);
        });

        if ($attachment) {
            // Store temporarily and dispatch background job
            $tempPath = $attachment->store('temp-attachments', 'local');
            ProcessLoanAttachment::dispatch($loan, $tempPath, $attachment->getClientOriginalName());
        }

        return $loan;
    }

    /**
     * Activate a loan if collateral is sufficient.
     */
    public function activateLoan(Loan $loan): Loan
    {
        // Collateral check is no longer mandatory
        $loan->status = 'active';
        $loan->save();

        // Record Disbursement Transaction
        TransactionService::record(
            type: 'loan_disbursement',
            amount: $loan->amount,
            reference: 'DISB-'.$loan->loan_number,
            paymentMethod: 'bank_transfer', // Default for disbursement
            user: $loan->borrower->user,
            related: $loan,
            notes: "Loan disbursed to {$loan->borrower->user->name}"
        );

        return $loan;
    }

    /**
     * Link collateral to a loan.
     */
    protected function linkCollateral(Loan $loan, $collateralId): void
    {
        // Detach previous collateral if any
        Collateral::where('loan_id', $loan->id)->update(['loan_id' => null, 'status' => 'deposited']);

        $collateral = Collateral::find($collateralId);
        if ($collateral) {
            $collateral->update([
                'loan_id' => $loan->id,
                'status' => 'in_vault',
            ]);
        }
    }
}
