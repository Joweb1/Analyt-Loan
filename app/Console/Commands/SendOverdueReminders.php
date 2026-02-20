<?php

namespace App\Console\Commands;

use App\Helpers\SystemLogger;
use App\Models\Loan;
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
        $overdueLoans = Loan::where('status', 'overdue')->with('borrower.user')->get();

        $this->info("Found {$overdueLoans->count()} overdue loans. Sending notifications...");

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

        \App\Services\SystemHealthService::log('Communications', 'success', "Overdue reminders sent to {$overdueLoans->count()} borrowers.");
        $this->info('Overdue reminders sent successfully.');
    }
}
