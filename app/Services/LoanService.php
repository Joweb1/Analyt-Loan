<?php

namespace App\Services;

use App\DTOs\LoanApplicationDTO;
use App\Jobs\ProcessLoanAttachment;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\ScheduledRepayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            // Apply Risk-Based Pricing if interest_rate is not explicitly set (or 0)
            if ($data['interest_rate'] <= 0) {
                $borrower = \App\Models\Borrower::find($data['borrower_id']);
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

            $loan = Loan::create($data);

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }

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
        $principalShare = (float) $loan->amount / $numRepayments;
        $totalInterest = $loan->getTotalExpectedInterest();
        $interestShare = $totalInterest / $numRepayments;

        // Fees added to first installment during creation
        $processingFee = (float) ($loan->processing_fee ?? 0);
        $insuranceFee = (float) ($loan->insurance_fee ?? 0);

        $startDate = \Carbon\Carbon::parse($loan->release_date ?? $loan->created_at);
        $cycle = $loan->repayment_cycle ?? 'monthly';

        // Delete existing schedules if any (to allow regeneration)
        $loan->scheduledRepayments()->delete();

        for ($i = 1; $i <= $numRepayments; $i++) {
            $dueDate = $startDate->copy();

            match ($cycle) {
                'daily' => $dueDate->addDays($i),
                'weekly' => $dueDate->addWeeks($i),
                'biweekly' => $dueDate->addWeeks($i * 2),
                'monthly' => $dueDate->addMonths($i),
                'yearly' => $dueDate->addYears($i),
                default => $dueDate->addMonths($i),
            };

            $penaltyForInstallment = 0;
            if ($i === 1) {
                $penaltyForInstallment = $processingFee + $insuranceFee;
            }

            ScheduledRepayment::create([
                'loan_id' => $loan->id,
                'due_date' => $dueDate,
                'principal_amount' => $principalShare,
                'interest_amount' => $interestShare,
                'penalty_amount' => $penaltyForInstallment,
                'installment_number' => $i,
                'status' => 'applied',
            ]);
        }

        $loan->refreshRepaymentStatus();
    }

    /**
     * Suggest an interest rate based on the borrower's trust score.
     */
    public function suggestInterestRate(\App\Models\Borrower $borrower, float $baseRate): float
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
            $data = array_filter($dto->toArray(), fn ($value) => ! is_null($value));
            $loan->update($data);

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }
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
