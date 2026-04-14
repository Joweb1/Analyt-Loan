<?php

namespace App\Services;

use App\Models\Borrower;
use App\Models\Loan;
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

        $totalWeightedMinor = 0;
        $totalPossibleMinor = 0;

        foreach ($schedules as $schedule) {
            $currency = $schedule->loan->organization->currency_code ?? 'NGN';
            $principal = $schedule->principal_amount ?? new \App\ValueObjects\Money(0, $currency);
            $interest = $schedule->interest_amount ?? new \App\ValueObjects\Money(0, $currency);
            $penalty = $schedule->penalty_amount ?? new \App\ValueObjects\Money(0, $currency);

            $scheduleTotal = $principal->add($interest)->add($penalty);
            $scheduleTotalMinor = $scheduleTotal->getMinorAmount();
            $totalPossibleMinor += $scheduleTotalMinor;

            if ($schedule->status === 'paid') {
                $multiplier = self::getTimelinessMultiplier($schedule);
                $totalWeightedMinor += (int) ($scheduleTotalMinor * $multiplier);
            } elseif ($schedule->status === 'partial') {
                $multiplier = self::getTimelinessMultiplier($schedule);
                $paidMinor = $schedule->paid_amount ? $schedule->paid_amount->getMinorAmount() : 0;
                $totalWeightedMinor += (int) ($paidMinor * $multiplier);
            } elseif ($schedule->status === 'overdue') {
                $totalWeightedMinor += 0;
            } else {
                $totalPossibleMinor -= $scheduleTotalMinor;
            }
        }

        if ($totalPossibleMinor <= 0) {
            return 0;
        }

        $baseScore = ($totalWeightedMinor / $totalPossibleMinor) * 100;

        // Apply Behavioral Bonuses
        $score = self::applyBehavioralBonuses($borrower, $baseScore);

        // Apply Hard Penalties (e.g., Defaulted Loans)
        $score = self::applyHardPenalties($borrower, $score);

        return (int) round(max(0, min(100, $score)));
    }

    /**
     * Apply behavioral bonuses based on loan history.
     */
    private static function applyBehavioralBonuses(Borrower $borrower, float $score): float
    {
        // 1. Loan Velocity: +2 points for every fully repaid loan
        $repaidCount = Loan::where('borrower_id', $borrower->id)->where('status', 'repaid')->count();
        $score += ($repaidCount * 2);

        // 2. Frequency Consistency: Bonus for multiple repayments in a single schedule
        // (Signals proactive financial management)
        $multiPayBonus = Repayment::whereHas('loan', fn ($q) => $q->where('borrower_id', $borrower->id))
            ->count() > ($repaidCount * 2) ? 5 : 0;
        $score += $multiPayBonus;

        return $score;
    }

    /**
     * Apply hard penalties that override the base score.
     */
    private static function applyHardPenalties(Borrower $borrower, float $score): float
    {
        // If the borrower has any defaulted loans, cap the score at 30
        $hasDefaulted = Loan::where('borrower_id', $borrower->id)->where('status', 'defaulted')->exists();

        if ($hasDefaulted) {
            return min(30, $score);
        }

        return $score;
    }

    /**
     * Determine the timeliness multiplier based on due date vs actual payment date.
     */
    private static function getTimelinessMultiplier(ScheduledRepayment $schedule): float
    {
        if ($schedule->status === 'overdue' || ($schedule->status === 'applied' && $schedule->due_date->isPast())) {
            return 0.0;
        }

        $lastRepayment = Repayment::where('loan_id', $schedule->loan_id)
            ->where('paid_at', '<=', \App\Models\Organization::systemNow())
            ->latest('paid_at')
            ->first();

        if (! $lastRepayment) {
            return 1.0;
        }

        $dueDate = $schedule->due_date;
        $paidDate = $lastRepayment->paid_at;

        // Early Payment Bonus: Paid 7+ days early gets a 1.1x multiplier
        if ($paidDate->diffInDays($dueDate, false) >= 7) {
            return 1.1;
        }

        if ($paidDate->lessThanOrEqualTo($dueDate)) {
            return 1.0; // On-time
        }

        $daysLate = $dueDate->diffInDays($paidDate);

        if ($daysLate <= 1) {
            return 0.9; // 1-day grace period
        } elseif ($daysLate <= 3) {
            return 0.8; // Minor late
        } elseif ($daysLate <= 14) {
            return 0.5; // Late
        } elseif ($daysLate <= 30) {
            return 0.2; // Very Late
        }

        return 0.0;
    }
}
