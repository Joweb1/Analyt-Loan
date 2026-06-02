<?php

namespace App\Console\Commands;

use App\Helpers\SystemLogger;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Services\SystemHealthService;
use Carbon\Carbon;
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
        SystemHealthService::log('Reports', 'info', 'Generating daily business summary...');
        $organizations = Organization::where('status', 'active')->get();

        foreach ($organizations as $org) {
            // Set test time if manual date is enabled
            if ($org->system_date) {
                Carbon::setTestNow($org->getSystemTime());
            } else {
                Carbon::setTestNow(); // Use real time
            }

            $today = now()->startOfDay();

            // Stats
            $repayments = Repayment::whereHas('loan', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })->where('paid_at', '>=', $today)->sum('amount') / 100;

            $newLoansCount = Loan::where('organization_id', $org->id)
                ->where('created_at', '>=', $today)->count();

            $newBorrowersCount = Borrower::where('organization_id', $org->id)
                ->where('created_at', '>=', $today)->count();

            $message = 'Daily Summary for '.now()->format('M d, Y').":\n";
            $message .= '• Repayments: ₦'.number_format($repayments)."\n";
            $message .= '• New Loans: '.$newLoansCount."\n";
            $message .= '• New Customers: '.$newBorrowersCount;

            SystemLogger::log(
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

        // Reset
        Carbon::setTestNow();

        SystemHealthService::log('Reports', 'success', 'Daily performance reports dispatched.');
        $this->info('Daily reports sent successfully.');
    }
}
