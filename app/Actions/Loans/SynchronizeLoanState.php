<?php

namespace App\Actions\Loans;

use App\Helpers\SystemLogger;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\ScheduledRepayment;
use App\Support\Tracing;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SynchronizeLoanState
{
    /**
     * Synchronize the status of scheduled repayments based on actual repayments.
     */
    public function execute(Loan $loan): void
    {
        $span = Tracing::startSpan('loan.synchronize', "Synchronizing state for loan #{$loan->loan_number}");

        /** @var Collection<int, Repayment> $repayments */
        $repayments = $loan->repayments()->orderBy('paid_at')->get();
        $schedules = $loan->scheduledRepayments()->orderBy('due_date')->get();

        $currency = $loan->organization->currency_code ?? 'NGN';

        $totalPaidMinor = (int) $repayments->sum(fn (Repayment $r) => $r->amount->getMinorAmount());
        $remaining = new Money($totalPaidMinor, $currency);

        $org = $loan->organization;
        $today = $org->getSystemTime()->startOfDay();

        foreach ($schedules as $s) {
            /** @var ScheduledRepayment $s */
            /** @var Money $principal */
            $principal = $s->principal_amount ?? new Money(0, $currency);
            /** @var Money $interest */
            $interest = $s->interest_amount ?? new Money(0, $currency);
            /** @var Money $penalty */
            $penalty = $s->penalty_amount ?? new Money(0, $currency);

            $totalDue = $principal->add($interest)->add($penalty);

            if ($remaining->getMinorAmount() >= $totalDue->getMinorAmount() && $totalDue->getMinorAmount() > 0) {
                $s->paid_amount = $totalDue;
                $s->status = 'paid';
                $remaining = $remaining->subtract($totalDue);
            } elseif ($remaining->getMinorAmount() > 0) {
                $s->paid_amount = $remaining;
                $s->status = 'partial';
                $remaining = new Money(0, $currency);
            } else {
                $s->paid_amount = new Money(0, $currency);
                // Overdue if due_date is strictly before today
                $s->status = $s->due_date->lt($today) ? 'overdue' : 'applied';
            }
            $s->save();
        }

        // Also sync overall loan status
        $totalInterest = $loan->getTotalExpectedInterest();
        $totalPayable = $loan->amount->add($totalInterest);

        $totalPaid = new Money($totalPaidMinor, $currency);

        if ($totalPaid->getMinorAmount() >= $totalPayable->getMinorAmount() && $totalPayable->getMinorAmount() > 0) {
            if ($loan->status !== 'repaid' && ! in_array($loan->status, ['closed', 'written_off'])) {
                $loan->update(['status' => 'repaid']);
            }

            $this->processExtraRepaymentsToSavings($loan, $repayments);
        } else {
            // Check if any schedule is still overdue (due before today and not fully paid)
            $hasOverdue = $loan->scheduledRepayments()
                ->where('status', 'overdue')
                ->exists();

            $newStatus = $hasOverdue ? 'overdue' : 'active';

            // Only toggle status between 'active' and 'overdue'
            if (in_array($loan->status, ['active', 'overdue']) && $loan->status !== $newStatus) {
                $loan->update(['status' => $newStatus]);
            }

            // If it was 'repaid' but now is not (e.g. repayment deleted), move back to active/overdue
            if ($loan->status === 'repaid' && $totalPaid->getMinorAmount() < $totalPayable->getMinorAmount()) {
                $loan->update(['status' => $newStatus]);
            }
        }

        if ($span) {
            $span->finish();
        }
    }

    /**
     * Process extra repayments and move them to savings.
     */
    protected function processExtraRepaymentsToSavings(Loan $loan, $repayments): void
    {
        $user = $loan->borrower->user;
        /** @var SavingsAccount $account */
        $account = $user->savingsAccount()->firstOrCreate([
            'organization_id' => $user->organization_id,
        ], [
            'account_number' => 'SAV-'.strtoupper(Str::random(8)),
            'balance' => 0,
            'interest_rate' => 0,
            'status' => 'active',
        ]);

        // Process each repayment that has an extra_amount and isn't already linked to a savings transaction
        foreach ($repayments->filter(fn ($r) => $r->extra_amount->isPositive()) as $repayment) {
            /** @var Repayment $repayment */
            if ($repayment->savingsTransactions()->count() === 0) {
                $account->transactions()->create([
                    'repayment_id' => $repayment->id,
                    'amount' => $repayment->extra_amount,
                    'type' => 'deposit',
                    'reference' => 'EXTRA-'.strtoupper(Str::random(8)),
                    'notes' => "Extra balance from Loan #{$loan->loan_number} (Repayment ID: {$repayment->id})",
                    'staff_id' => $repayment->collected_by ?? Auth::id() ?? $loan->loan_officer_id ?? $loan->organization->owner_id,
                    'transaction_date' => now(),
                ]);

                /** @var Money $balance */
                $balance = $account->balance;
                $account->update(['balance' => $balance->add($repayment->extra_amount)]);

                SystemLogger::success(
                    'Extra Balance to Savings',
                    '₦'.$repayment->extra_amount->format()." from Loan #{$loan->loan_number} has been moved to savings.",
                    'savings',
                    $loan->borrower
                );
            }
        }
    }
}
