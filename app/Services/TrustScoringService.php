<?php

namespace App\Services;

use App\Models\Borrower;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;

class TrustScoringService
{
    /**
     * Recalculate the trust score for a specific borrower.
     */
    public static function calculate(Borrower $borrower): int
    {
        $schedules = ScheduledRepayment::whereHas('loan', function ($q) use ($borrower) {
            $q->where('borrower_id', $borrower->id);
        })->get();

        if ($schedules->isEmpty()) {
            return 0; // Unscored
        }

        $totalWeightedValue = 0;
        $totalPossibleValue = 0;

        foreach ($schedules as $schedule) {
            $scheduleTotal = $schedule->principal_amount + $schedule->interest_amount + $schedule->penalty_amount;
            $totalPossibleValue += $scheduleTotal;

            if ($schedule->status === 'paid') {
                // Find the actual repayment date for this installment
                // Note: This is an approximation as repayments are pooled in our current sync logic
                $multiplier = self::getTimelinessMultiplier($schedule);
                $totalWeightedValue += ($scheduleTotal * $multiplier);
            } elseif ($schedule->status === 'partial') {
                $multiplier = self::getTimelinessMultiplier($schedule);
                $totalWeightedValue += ($schedule->paid_amount * $multiplier);
            } elseif ($schedule->status === 'overdue') {
                // Multiplier is 0 for overdue/unpaid past due date
                $totalWeightedValue += 0;
            } else {
                // Pending (future) - don't count towards current possible value to avoid penalizing new loans
                $totalPossibleValue -= $scheduleTotal;
            }
        }

        if ($totalPossibleValue <= 0) {
            return 0;
        }

        $score = ($totalWeightedValue / $totalPossibleValue) * 100;

        return (int) round(max(0, min(100, $score)));
    }

    /**
     * Determine the timeliness multiplier based on due date vs actual payment date.
     */
    private static function getTimelinessMultiplier(ScheduledRepayment $schedule): float
    {
        // If unpaid and overdue, it's 0
        if ($schedule->status === 'overdue' || ($schedule->status === 'pending' && $schedule->due_date->isPast())) {
            return 0.0;
        }

        // If paid or partial, we check how late it was
        // Since our sync logic aggregates, we look at the last repayment for this loan
        $lastRepayment = Repayment::where('loan_id', $schedule->loan_id)
            ->where('paid_at', '<=', now())
            ->latest('paid_at')
            ->first();

        if (! $lastRepayment) {
            return 1.0; // Should not happen if status is paid/partial
        }

        $dueDate = $schedule->due_date;
        $paidDate = $lastRepayment->paid_at;

        if ($paidDate->lessThanOrEqualTo($dueDate)) {
            return 1.0; // On-time
        }

        $daysLate = $dueDate->diffInDays($paidDate);

        if ($daysLate <= 3) {
            return 0.8; // Grace Period
        } elseif ($daysLate <= 14) {
            return 0.5; // Late
        } elseif ($daysLate <= 30) {
            return 0.2; // Very Late
        }

        return 0.0; // Default/Defaulted
    }
}
