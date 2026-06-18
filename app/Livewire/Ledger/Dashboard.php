<?php

namespace App\Livewire\Ledger;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\ScheduledRepayment;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    #[Url]
    public $selectedDate;

    public $currentWeekInfo;

    public $stats = [];

    public $groups = [];

    public function mount()
    {
        $org = Organization::current();
        $this->selectedDate = $org->getSystemTime()->toDateString();
        $this->loadStats();
        $this->loadGroups();
    }

    public function updatedSelectedDate()
    {
        $this->loadStats();
        $this->loadGroups();
    }

    public function loadStats()
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
            ->whereYear('due_date', $selected->year)
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
        $this->stats['active_weekly_groups'] = Loan::where('organization_id', $org->id)
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('collection_group')
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

        $this->groups = [];

        $definedGroups = [
            'Monday Group', 'Tuesday Group', 'Wednesday Group',
            'Thursday Group', 'Friday Group', 'Monthly Collections',
        ];

        foreach ($definedGroups as $groupName) {
            $isMonthly = $groupName === 'Monthly Collections';

            $loanQuery = Loan::where('collection_group', $groupName)
                ->where('organization_id', $org->id)
                ->whereIn('status', ['active', 'overdue']);

            if ($isMonthly) {
                $loanQuery->where('repayment_cycle', 'monthly');
            } else {
                $loanQuery->where('repayment_cycle', '!=', 'monthly');
            }

            $loans = $loanQuery->get();
            $borrowerIds = $loans->pluck('borrower_id')->unique();
            $loanIds = $loans->pluck('id');

            $collectedMinor = 0;
            $groupOverdueMinor = 0;
            $unpaidIndicator = 0;
            $isPassed = false;

            if ($isMonthly) {
                $collectedMinor = (int) Repayment::whereIn('loan_id', $loanIds)
                    ->whereMonth('paid_at', $selected->month)
                    ->whereYear('paid_at', $selected->year)
                    ->sum('amount');

                $groupOverdueMinor = (int) ScheduledRepayment::whereIn('loan_id', $loanIds)
                    ->whereMonth('due_date', $selected->month)
                    ->whereYear('due_date', $selected->year)
                    ->where('status', '!=', 'paid')
                    ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount - paid_amount) as total')
                    ->value('total');

                $unpaidIndicator = Loan::where('collection_group', $groupName)
                    ->where('organization_id', $org->id)
                    ->whereIn('status', ['active', 'overdue'])
                    ->where('repayment_cycle', 'monthly')
                    ->whereNotExists(function ($query) use ($selected) {
                        $query->select(DB::raw(1))
                            ->from('repayments')
                            ->whereRaw('repayments.loan_id = loans.id')
                            ->whereMonth('paid_at', $selected->month)
                            ->whereYear('paid_at', $selected->year);
                    })->count();

                // Monthly is "passed" if the selected month is in the past
                $isPassed = $selected->copy()->endOfMonth()->isPast();
            } else {
                $collectedMinor = (int) Repayment::whereIn('loan_id', $loanIds)
                    ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
                    ->sum('amount');

                $groupOverdueMinor = (int) ScheduledRepayment::whereIn('loan_id', $loanIds)
                    ->where('due_date', '<=', $endOfWeek)
                    ->where('status', '!=', 'paid')
                    ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount - paid_amount) as total')
                    ->value('total');

                $dayOfWeekMap = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];
                $groupDayName = str_replace(' Group', '', $groupName);
                $groupDayIndex = $dayOfWeekMap[$groupDayName] ?? 0;

                $groupDateInSelectedWeek = $startOfWeek->copy()->addDays($groupDayIndex - 1);
                $isPassed = $groupDateInSelectedWeek->isPast();

                $unpaidIndicator = Loan::where('collection_group', $groupName)
                    ->where('organization_id', $org->id)
                    ->whereIn('status', ['active', 'overdue'])
                    ->where('repayment_cycle', '!=', 'monthly')
                    ->whereNotExists(function ($query) use ($startOfWeek, $endOfWeek) {
                        $query->select(DB::raw(1))
                            ->from('repayments')
                            ->whereRaw('repayments.loan_id = loans.id')
                            ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59']);
                    })->count();
            }

            $this->groups[] = [
                'name' => $groupName,
                'members_count' => count($borrowerIds),
                'collected_amount' => new Money($collectedMinor, $currency),
                'overdue_amount' => new Money($groupOverdueMinor, $currency),
                'performance' => count($borrowerIds) > 0 ? round((($countPaidThisWeek = count($borrowerIds) - $unpaidIndicator) / count($borrowerIds)) * 100) : 0,
                'unpaid_indicator' => $unpaidIndicator,
                'is_passed' => $isPassed,
                'is_overdue_warning' => $isPassed && $unpaidIndicator > 0,
            ];
        }
    }

    public function render()
    {
        return view('livewire.ledger.dashboard')
            ->layout('layouts.app', ['title' => 'Collection Ledger']);
    }
}
