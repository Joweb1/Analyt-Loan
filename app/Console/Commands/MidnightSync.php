<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MidnightSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:midnight-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily system maintenance: sync loan statuses, apply penalties, and generate tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Midnight Sync ['.now()->toDateTimeString().']...');
        \App\Services\SystemHealthService::log('Scheduler', 'info', 'MidnightSync started.');

        // 1. Sync Loan Statuses & Apply Penalties
        $this->syncLoans();

        // 2. Generate Action Tasks for each Organization
        $this->generateTasks();

        // 3. Recalculate Trust Scores for all active borrowers
        $this->syncTrustScores();

        \App\Services\SystemHealthService::log('Scheduler', 'success', 'MidnightSync completed successfully.');
        $this->info('Midnight Sync completed successfully.');
    }

    private function syncLoans()
    {
        $this->info('Syncing loan statuses and applying penalties...');

        \App\Models\Loan::whereIn('status', ['active', 'overdue'])->chunk(100, function (\Illuminate\Database\Eloquent\Collection $loans) {
            foreach ($loans as $loan) {
                /** @var \App\Models\Loan $loan */
                $overdueSchedules = $loan->scheduledRepayments()
                    ->where('due_date', '<', now()->startOfDay())
                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->get();

                if ($overdueSchedules->isNotEmpty()) {
                    if ($loan->status !== 'overdue') {
                        $loan->update(['status' => 'overdue']);
                    }

                    foreach ($overdueSchedules as $schedule) {
                        /** @var \App\Models\ScheduledRepayment $schedule */
                        if ($schedule->status !== 'overdue') {
                            $schedule->update(['status' => 'overdue']);
                        }

                        // Apply penalties based on frequency
                        $this->applyPenalty($loan, $schedule);
                    }
                }
            }
        });
    }

    private function applyPenalty($loan, $schedule)
    {
        if (! ($loan->penalty_value > 0)) {
            return;
        }

        $shouldApply = false;
        $today = now()->startOfDay();
        $daysOverdue = $schedule->due_date->diffInDays($today);

        match ($loan->penalty_frequency) {
            'one_time' => $shouldApply = ($daysOverdue === 1),
            'daily' => $shouldApply = true,
            'weekly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 7 === 0),
            'monthly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 30 === 0),
            'yearly' => $shouldApply = ($daysOverdue > 0 && $daysOverdue % 365 === 0),
            default => $shouldApply = false,
        };

        if ($shouldApply) {
            $penaltyAmount = $loan->penalty_type === 'fixed'
                ? $loan->penalty_value
                : ($schedule->principal_amount * ($loan->penalty_value / 100));

            $schedule->increment('penalty_amount', $penaltyAmount);
        }
    }

    private function generateTasks()
    {
        $this->info('Generating daily action tasks...');
        $organizations = \App\Models\Organization::where('status', 'active')->get();
        foreach ($organizations as $org) {
            \App\Services\ActionTaskService::generateDailyTasks($org->id);
        }
    }

    private function syncTrustScores()
    {
        $this->info('Recalculating trust scores...');
        \App\Models\Borrower::chunk(100, function (\Illuminate\Database\Eloquent\Collection $borrowers) {
            foreach ($borrowers as $borrower) {
                /** @var \App\Models\Borrower $borrower */
                $borrower->recalculateTrustScore();
            }
        });
    }
}
