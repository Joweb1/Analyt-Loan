<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\SystemNotification;
use Illuminate\Database\Seeder;

class ActionTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::where('slug', 'analyt-demo')->first();
        if (! $org) {
            return;
        }

        // 1. Pending Loan Approvals
        $pendingLoans = Loan::where('organization_id', $org->id)->where('status', 'applied')->take(2)->get();
        foreach ($pendingLoans as $loan) {
            SystemNotification::create([
                'organization_id' => $org->id,
                'title' => 'Approve Disbursement',
                'message' => "Loan #{$loan->loan_number} for ₦ ".number_format($loan->amount).' is pending approval.',
                'type' => 'info',
                'category' => 'loan',
                'is_actionable' => true,
                'action_link' => route('loan.show', $loan->id),
                'priority' => 'high',
                'subject_id' => $loan->id,
                'subject_type' => Loan::class,
            ]);
        }

        // 2. KYC Reviews
        $borrowers = Borrower::where('organization_id', $org->id)->take(2)->get();
        foreach ($borrowers as $b) {
            SystemNotification::create([
                'organization_id' => $org->id,
                'title' => 'KYC Review Required',
                'message' => "Borrower {$b->user->name} has submitted documents for verification.",
                'type' => 'info',
                'category' => 'kyc',
                'is_actionable' => true,
                'action_link' => route('customer'),
                'priority' => 'medium',
                'subject_id' => $b->id,
                'subject_type' => Borrower::class,
            ]);
        }

        // 3. Overdue Alert
        $overdueLoans = Loan::where('organization_id', $org->id)->where('status', 'overdue')->take(1)->get();
        foreach ($overdueLoans as $loan) {
            SystemNotification::create([
                'organization_id' => $org->id,
                'title' => 'Critical Overdue',
                'message' => "Loan #{$loan->loan_number} is more than 3 days overdue. Immediate recovery action needed.",
                'type' => 'danger',
                'category' => 'collection',
                'is_actionable' => true,
                'action_link' => route('loan.show', $loan->id),
                'priority' => 'critical',
                'subject_id' => $loan->id,
                'subject_type' => Loan::class,
            ]);
        }
    }
}
