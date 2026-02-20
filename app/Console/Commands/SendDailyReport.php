<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily business summary to organization admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Services\SystemHealthService::log('Reports', 'info', 'Generating daily business summary...');
        $organizations = \App\Models\Organization::where('status', 'active')->get();

        foreach ($organizations as $org) {
            $today = now()->startOfDay();

            // Stats
            $repayments = \App\Models\Repayment::whereHas('loan', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })->where('paid_at', '>=', $today)->sum('amount');

            $newLoansCount = \App\Models\Loan::where('organization_id', $org->id)
                ->where('created_at', '>=', $today)->count();

            $newBorrowersCount = \App\Models\Borrower::where('organization_id', $org->id)
                ->where('created_at', '>=', $today)->count();

            $message = 'Daily Summary for '.now()->format('M d, Y').":\n";
            $message .= '• Repayments: ₦'.number_format($repayments)."\n";
            $message .= '• New Loans: '.$newLoansCount."\n";
            $message .= '• New Customers: '.$newBorrowersCount;

            \App\Helpers\SystemLogger::log(
                'Daily Performance Report',
                $message,
                'success',
                'report',
                $org,
                false,
                null,
                'medium'
            );
        }

        \App\Services\SystemHealthService::log('Reports', 'success', 'Daily performance reports dispatched.');
        $this->info('Daily reports sent successfully.');
    }
}
