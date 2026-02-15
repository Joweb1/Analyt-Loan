<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\User;
use App\Models\SystemNotification;
use App\Helpers\SystemLogger;
use Illuminate\Support\Facades\Auth;

class ActionTaskService
{
    public static function generateDailyTasks($orgId)
    {
        // 1. Check for Overdue Loans > 3 days that don't have a notification yet
        $overdueLoans = Loan::where('organization_id', $orgId)
            ->where('status', 'overdue')
            ->where('updated_at', '<', now()->subDays(3))
            ->get();

        foreach ($overdueLoans as $loan) {
            $exists = SystemNotification::where('organization_id', $orgId)
                ->where('subject_id', $loan->id)
                ->where('subject_type', Loan::class)
                ->where('category', 'overdue')
                ->whereNull('read_at')
                ->exists();

            if (!$exists) {
                SystemLogger::action(
                    'Critical Overdue',
                    "Loan #{$loan->loan_number} is significantly overdue. Recovery needed.",
                    route('loan.show', $loan->id, false),
                    'overdue',
                    $loan,
                    'critical'
                );
            }
        }

        // 2. Check for Inactive Staff (Created > 3 days ago, never logged in)
        $inactiveStaff = User::where('organization_id', $orgId)
            ->whereHas('roles', function($q) { $q->whereNotIn('name', ['Borrower']); })
            ->whereNull('last_login_at')
            ->where('created_at', '<', now()->subDays(3))
            ->get();

        foreach ($inactiveStaff as $staff) {
            $exists = SystemNotification::where('organization_id', $orgId)
                ->where('subject_id', $staff->id)
                ->where('subject_type', User::class)
                ->where('category', 'staff_alert')
                ->whereNull('read_at')
                ->exists();

            if (!$exists) {
                SystemLogger::action(
                    'Inactive Staff Member',
                    "Staff {$staff->name} has not accessed the system yet.",
                    route('settings.team-members', [], false),
                    'staff_alert',
                    $staff,
                    'low'
                );
            }
        }

        // 3. Daily Report Review Task
        $today = now()->startOfDay();
        $exists = SystemNotification::where('organization_id', $orgId)
            ->where('category', 'report')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$exists) {
            SystemLogger::action(
                'Review Daily Report',
                "The daily performance report for " . $today->format('M d, Y') . " is ready for review.",
                route('reports', [], false),
                'report',
                null,
                'low'
            );
        }
    }
}
