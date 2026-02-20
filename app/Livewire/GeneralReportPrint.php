<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GeneralReportPrint extends Component
{
    public $type;

    public $title;

    public $startDate;

    public $endDate;

    public $metrics = [];

    public $staffData = [];

    public $activityLogs = [];

    public $organization;

    public function mount($type = 'daily')
    {
        $this->type = $type;
        $this->organization = Auth::user()->organization;
        $this->calculatePeriod();
        $this->fetchMetrics();
    }

    protected function calculatePeriod()
    {
        $now = now();
        match ($this->type) {
            'daily' => [
                $this->startDate = $now->copy()->startOfDay(),
                $this->endDate = $now->copy()->endOfDay(),
                $this->title = 'Daily Performance Report',
            ],
            'weekly' => [
                $this->startDate = $now->copy()->startOfWeek(),
                $this->endDate = $now->copy()->endOfWeek(),
                $this->title = 'Weekly Operations Report',
            ],
            'monthly' => [
                $this->startDate = $now->copy()->startOfMonth(),
                $this->endDate = $now->copy()->endOfMonth(),
                $this->title = 'Monthly Business Review',
            ],
            'yearly' => [
                $this->startDate = $now->copy()->startOfYear(),
                $this->endDate = $now->copy()->endOfYear(),
                $this->title = 'Annual Financial Report',
            ],
            'staff' => [
                $this->startDate = $now->copy()->startOfMonth(), // Default to month for staff
                $this->endDate = $now->copy()->endOfMonth(),
                $this->title = 'Staff Performance Analytics',
            ],
            'staff_activity' => [
                $this->startDate = $now->copy()->subDays(30)->startOfDay(),
                $this->endDate = $now->copy()->endOfDay(),
                $this->title = 'Personal Activity Log - '.Auth::user()->name,
            ],
            default => [
                $this->startDate = $now->copy()->startOfDay(),
                $this->endDate = $now->copy()->endOfDay(),
                $this->title = 'Organization Report',
            ]
        };
    }

    protected function fetchMetrics()
    {
        $orgId = $this->organization->id;

        // 1. Personal Activity Log
        if ($this->type === 'staff_activity') {
            $this->activityLogs = \App\Models\SystemNotification::where('user_id', Auth::id())
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->latest()
                ->get();

            return;
        }

        // 1. Disbursement Metrics
        $this->metrics['total_disbursed'] = Loan::where('organization_id', $orgId)
            ->whereBetween('release_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $this->metrics['disbursement_count'] = Loan::where('organization_id', $orgId)
            ->whereBetween('release_date', [$this->startDate, $this->endDate])
            ->count();

        // 2. Collection Metrics
        $this->metrics['total_collected'] = Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->sum('amount');

        $this->metrics['collection_count'] = Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->whereBetween('paid_at', [$this->startDate, $this->endDate])
            ->count();

        // 3. Portfolio Health (Snapshot as of End Date)
        $this->metrics['active_loans'] = Loan::where('organization_id', $orgId)
            ->whereIn('status', ['active', 'approved', 'overdue'])
            ->count();

        $this->metrics['overdue_amount'] = Loan::where('organization_id', $orgId)
            ->where('status', 'overdue')
            ->sum('amount');

        // 4. Customer Metrics
        $this->metrics['new_customers'] = Borrower::where('organization_id', $orgId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        // 5. Vault Value
        $this->metrics['vault_value'] = Collateral::where('organization_id', $orgId)
            ->where('status', 'in_vault')
            ->sum('value');

        // 6. Staff Performance (If requested)
        if ($this->type === 'staff') {
            $staffUsers = User::where('organization_id', $orgId)
                ->whereHas('roles', function ($q) {
                    $q->whereNotIn('name', ['Borrower']);
                })
                ->get();

            foreach ($staffUsers as $user) {
                $collected = Repayment::where('collected_by', $user->id)
                    ->whereBetween('paid_at', [$this->startDate, $this->endDate])
                    ->sum('amount');

                $count = Repayment::where('collected_by', $user->id)
                    ->whereBetween('paid_at', [$this->startDate, $this->endDate])
                    ->count();

                if ($count > 0) {
                    $this->staffData[] = [
                        'name' => $user->name,
                        'role' => $user->getRoleNames()->first() ?? 'Staff',
                        'collected' => $collected,
                        'count' => $count,
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.general-report-print')->layout('layouts.print');
    }
}
