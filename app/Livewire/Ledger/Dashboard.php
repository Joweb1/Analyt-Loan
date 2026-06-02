<?php

namespace App\Livewire\Ledger;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\ScheduledRepayment;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];

    public $groups = [];

    public $currentWeekInfo = '';

    public $selectedDate; // The reference date for the week

    public $today;

    public function mount()
    {
        $org = Organization::current();
        $this->today = $org->getSystemTime()->toDateString();
        $this->selectedDate = $this->today;

        $this->refreshData();
    }

    public function updatedSelectedDate()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->groups = [];
        $this->calculateStats();
        $this->loadGroups();
    }

    public function calculateStats()
    {
        $org = Organization::current();
        $selected = Carbon::parse($this->selectedDate);
        $startOfWeek = $selected->copy()->startOfWeek();
        $endOfWeek = $selected->copy()->endOfWeek();
        $currency = $org->currency_code ?? config('app.currency', 'NGN');

        $this->currentWeekInfo = 'Week '.$selected->weekOfMonth.' of '.$selected->format('F Y');

        // 1. Total Repayments Made (This Week)
        $repaymentsWeekMinor = (int) Repayment::whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
            ->whereHas('loan', fn ($q) => $q->where('organization_id', $org->id))
            ->sum('amount');
        $this->stats['total_repayments_week'] = new Money($repaymentsWeekMinor, $currency);

        // 2. Total Savings Balance (All Users - Regular only, Thrift excluded)
        $allSavings = SavingsAccount::where('organization_id', $org->id)->get();
        $totalSavingsMinor = $allSavings->sum(fn ($a) => $a->balance->getMinorAmount());
        $this->stats['total_savings_all'] = new Money($totalSavingsMinor, $currency);

        // 3. Monthly Collections Due (For this month)
        $monthlyDueMinor = (int) ScheduledRepayment::whereMonth('due_date', $selected->month)
            ->whereYear('due_date', $selected->month)
            ->whereHas('loan', fn ($q) => $q->where('organization_id', $org->id)->where('repayment_cycle', 'monthly'))
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount) as total')
            ->value('total');
        $this->stats['monthly_due'] = new Money($monthlyDueMinor, $currency);

        // 4. Weekly Collections Due (For this week)
        $weeklyDueMinor = (int) ScheduledRepayment::whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->whereHas('loan', fn ($q) => $q->where('organization_id', $org->id)->where('repayment_cycle', 'weekly'))
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount) as total')
            ->value('total');
        $this->stats['weekly_due'] = new Money($weeklyDueMinor, $currency);

        // 5. Overdue Amount (Total missed scheduled payments up to end of week)
        $overdueMinor = (int) ScheduledRepayment::where('due_date', '<=', $endOfWeek)
            ->where('status', '!=', 'paid')
            ->whereHas('loan', fn ($q) => $q->where('organization_id', $org->id))
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount - paid_amount) as total')
            ->value('total');
        $this->stats['overdue_amount'] = new Money($overdueMinor, $currency);

        // Active Weekly Groups
        $this->stats['active_weekly_groups'] = Borrower::where('organization_id', $org->id)
            ->whereNotNull('collection_group')
            ->whereHas('loans', fn ($q) => $q->where('status', 'active'))
            ->distinct('collection_group')
            ->count('collection_group');
    }

    public function loadGroups()
    {
        $org = Organization::current();
        $currency = $org->currency_code ?? config('app.currency', 'NGN');
        $selected = Carbon::parse($this->selectedDate);
        $startOfWeek = $selected->copy()->startOfWeek();
        $endOfWeek = $selected->copy()->endOfWeek();

        $definedGroups = [
            'Monday Group', 'Tuesday Group', 'Wednesday Group',
            'Thursday Group', 'Friday Group', 'Saturday Group',
        ];

        foreach ($definedGroups as $groupName) {
            $borrowers = Borrower::where('collection_group', $groupName)
                ->where('organization_id', $org->id)
                ->whereHas('loans', fn ($q) => $q->whereIn('status', ['active', 'overdue'])->where('repayment_cycle', '!=', 'monthly'))
                ->get();
            $borrowerIds = $borrowers->pluck('id');

            $collectedMinor = (int) Repayment::whereIn('borrower_id', $borrowerIds)
                ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
                ->sum('amount');

            // Total missed scheduled payments for this group (Total Overdue as of end of week)
            $groupOverdueMinor = (int) ScheduledRepayment::whereIn('loan_id', Loan::whereIn('borrower_id', $borrowerIds)->pluck('id'))
                ->where('due_date', '<=', $endOfWeek)
                ->where('status', '!=', 'paid')
                ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount - paid_amount) as total')
                ->value('total');

            // Indicator for passed days: Unpaid members for this week
            $dayOfWeekMap = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
            $groupDayName = str_replace(' Group', '', $groupName);
            $groupDayIndex = $dayOfWeekMap[$groupDayName] ?? 0;

            // We check if the group's day in the SELECTED week is already passed relative to TODAY
            // But if we are viewing a past week, it's definitely passed.
            $groupDateInSelectedWeek = $startOfWeek->copy()->addDays($groupDayIndex - 1); // Monday is start
            $isPassed = $groupDateInSelectedWeek->isPast();

            $unpaidIndicator = Borrower::where('collection_group', $groupName)
                ->where('organization_id', $org->id)
                ->whereNotExists(function ($query) use ($startOfWeek, $endOfWeek) {
                    $query->select(DB::raw(1))
                        ->from('repayments')
                        ->whereRaw('repayments.borrower_id = borrowers.id')
                        ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59']);
                })->count();

            $this->groups[] = [
                'name' => $groupName,
                'members_count' => count($borrowerIds),
                'collected_amount' => new Money($collectedMinor, $currency),
                'overdue_amount' => new Money($groupOverdueMinor, $currency),
                'performance' => count($borrowerIds) > 0 ? round((($countPaidThisWeek = count($borrowerIds) - $unpaidIndicator) / count($borrowerIds)) * 100) : 0,
                'unpaid_indicator' => $unpaidIndicator,
                'is_passed' => $isPassed,
            ];
        }

        // Add Monthly Collections as a "Group"
        $monthlyBorrowers = Borrower::where('organization_id', $org->id)
            ->whereHas('loans', fn ($q) => $q->where('repayment_cycle', 'monthly'))
            ->get();
        $monthlyBorrowerIds = $monthlyBorrowers->pluck('id');

        $monthlyCollectedMinor = (int) Repayment::whereIn('borrower_id', $monthlyBorrowerIds)
            ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
            ->sum('amount');

        $monthlyOverdueMinor = (int) ScheduledRepayment::whereIn('loan_id', Loan::whereIn('borrower_id', $monthlyBorrowerIds)->pluck('id'))
            ->where('due_date', '<=', $endOfWeek)
            ->where('status', '!=', 'paid')
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount - paid_amount) as total')
            ->value('total');

        $monthlyUnpaidCount = Borrower::whereIn('id', $monthlyBorrowerIds)
            ->whereNotExists(function ($query) use ($selected) {
                $query->select(DB::raw(1))
                    ->from('repayments')
                    ->whereRaw('repayments.borrower_id = borrowers.id')
                    ->whereMonth('paid_at', $selected->month)
                    ->whereYear('paid_at', $selected->year);
            })->count();

        $this->groups[] = [
            'name' => 'Monthly Collections',
            'members_count' => count($monthlyBorrowerIds),
            'collected_amount' => new Money($monthlyCollectedMinor, $currency),
            'overdue_amount' => new Money($monthlyOverdueMinor, $currency),
            'performance' => count($monthlyBorrowerIds) > 0 ? round(((count($monthlyBorrowerIds) - $monthlyUnpaidCount) / count($monthlyBorrowerIds)) * 100) : 0,
            'is_monthly' => true,
            'unpaid_indicator' => $monthlyUnpaidCount,
            'is_passed' => false,
        ];
    }

    public function render()
    {
        return view('livewire.ledger.dashboard')
            ->layout('layouts.app', ['title' => 'Collection Ledger']);
    }
}
