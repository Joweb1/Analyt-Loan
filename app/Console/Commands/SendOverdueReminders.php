<?php

namespace App\Console\Commands;

use App\Helpers\SystemLogger;
use App\Models\Loan;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:overdue-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications to borrowers with overdue loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Services\SystemHealthService::log('Communications', 'info', 'Scanning for overdue loans...');

        Organization::where('status', 'active')->chunk(50, function ($organizations) {
            foreach ($organizations as $org) {
                // Set test time if manual date is enabled
                if ($org->use_manual_date && $org->operating_date) {
                    Carbon::setTestNow($org->operating_date);
                } else {
                    Carbon::setTestNow();
                }

                $overdueLoans = Loan::where('organization_id', $org->id)
                    ->where('status', 'overdue')
                    ->with('borrower.user')
                    ->get();

                foreach ($overdueLoans as $loan) {
                    if ($loan->borrower->user_id) {
                        SystemLogger::log(
                            'Payment Reminder',
                            "Your loan #{$loan->loan_number} is overdue. Please make a payment to avoid further penalties.",
                            'warning',
                            'loan',
                            $loan,
                            false,
                            null,
                            'high',
                            $loan->borrower->user_id // RECIPIENT
                        );
                    }
                }
            }
        });

        // Reset
        Carbon::setTestNow();

        \App\Services\SystemHealthService::log('Communications', 'success', 'Overdue reminders process completed.');
        $this->info('Overdue reminders sent successfully.');
    }
}
