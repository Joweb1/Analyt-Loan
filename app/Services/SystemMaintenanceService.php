<?php

namespace App\Services;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\ScheduledRepayment;
use Carbon\Carbon;

class SystemMaintenanceService
{
    /**
     * Run daily maintenance for a specific organization on a specific date.
     */
    public static function runMaintenanceForDate(string $organizationId, Carbon $date): void
    {
        // Temporarily set time to the target date
        Carbon::setTestNow($date);

        try {
            self::syncLoanStatuses($organizationId, $date);
            self::generateDailyTasks($organizationId, $date);
            self::updateTrustScores($organizationId);
        } finally {
            // Reset is handled by the caller or middleware, but for safety:
            // Carbon::setTestNow();
        }
    }

    /**
     * Identify overdue loans and apply penalties.
     */
    public static function syncLoanStatuses(string $organizationId, Carbon $date): void
    {
        $startOfDay = $date->copy()->startOfDay();

        Loan::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue'])
            ->chunk(50, function ($loans) use ($startOfDay) {
                foreach ($loans as $loan) {
                    /** @var Loan $loan */
                    $overdueSchedules = $loan->scheduledRepayments()
                        ->where('due_date', '<', $startOfDay)
                        ->whereIn('status', ['applied', 'partial', 'overdue'])
                        ->get();

                    if ($overdueSchedules->isNotEmpty()) {
                        if ($loan->status !== 'overdue') {
                            $loan->update(['status' => 'overdue']);
                        }

                        foreach ($overdueSchedules as $schedule) {
                            /** @var ScheduledRepayment $schedule */
                            if ($schedule->status !== 'overdue') {
                                $schedule->update(['status' => 'overdue']);
                            }

                            self::applyPenalty($loan, $schedule, $startOfDay);
                        }
                    } else {
                        // Check if loan should be reverted to active (if backdated)
                        $futureSchedules = $loan->scheduledRepayments()
                            ->where('due_date', '>=', $startOfDay)
                            ->whereIn('status', ['applied', 'partial'])
                            ->count();

                        $anyOverdue = $loan->scheduledRepayments()
                            ->where('due_date', '<', $startOfDay)
                            ->whereIn('status', ['applied', 'partial', 'overdue'])
                            ->exists();

                        if (! $anyOverdue && $loan->status === 'overdue') {
                            $loan->update(['status' => 'active']);
                        }
                    }
                }
            });
    }

    private static function applyPenalty(Loan $loan, ScheduledRepayment $schedule, Carbon $today): void
    {
        if (! ($loan->penalty_value->isPositive())) {
            return;
        }

        $shouldApply = false;
        $daysOverdue = (int) $schedule->due_date->diffInDays($today);

        match ($loan->penalty_frequency) {
            'one_time' => $shouldApply = ($daysOverdue === 1),
            'daily' => $shouldApply = true,
            'weekly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 7 === 0),
            'monthly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 30 === 0),
            'yearly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 365 === 0),
            default => $shouldApply = false,
        };

        if ($shouldApply) {
            $penaltyToAdd = $loan->penalty_type === 'fixed'
                ? $loan->penalty_value
                : ($schedule->principal_amount->multiply($loan->penalty_value->getMajorAmount() / 100));

            $currentPenalty = $schedule->penalty_amount ?? new \App\ValueObjects\Money(0, $schedule->principal_amount->getCurrency());
            $schedule->penalty_amount = $currentPenalty->add($penaltyToAdd);
            $schedule->save();
        }
    }

    public static function generateDailyTasks(string $organizationId, Carbon $date): void
    {
        ActionTaskService::generateDailyTasks($organizationId);
    }

    public static function updateTrustScores(string $organizationId): void
    {
        Borrower::where('organization_id', $organizationId)->chunk(50, function ($borrowers) {
            foreach ($borrowers as $borrower) {
                /** @var Borrower $borrower */
                $borrower->update([
                    'trust_score' => TrustScoringService::calculate($borrower),
                ]);
            }
        });
    }
}
