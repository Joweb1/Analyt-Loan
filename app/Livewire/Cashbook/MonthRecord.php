<?php

namespace App\Livewire\Cashbook;

use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Services\CashbookService;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Livewire\Component;

class MonthRecord extends Component
{
    public $month;

    public $year;

    public $dateString;

    public $remainingBudget;

    public $totalBudget;

    public $showBudgetModal = false;

    public $showBalanceModal = false;

    public $newBudgetAmount = 0;

    public $newOpeningBalanceAmount = 0;

    public $openingBalance;

    public $liveBalance;

    protected $queryString = ['dateString' => ['as' => 'date']];

    public function mount()
    {
        $org = Organization::current();
        if (! $this->dateString) {
            $this->dateString = $org->getSystemTime()->toDateString();
        }

        $date = Carbon::parse($this->dateString);
        $this->month = $date->month;
        $this->year = $date->year;

        $this->loadBudgetData();
        $this->loadBalanceData();
    }

    public function loadBudgetData()
    {
        $org = Organization::current();
        $date = Carbon::create($this->year, $this->month, 1);
        $service = app(CashbookService::class);

        $this->remainingBudget = $service->getRemainingBudget($date, $org);

        $budget = \App\Models\ExpenseBudget::where('organization_id', $org->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $this->totalBudget = $budget ? $budget->total_budget_amount : new \App\ValueObjects\Money(0, $org->currency_code);
        $this->newBudgetAmount = $this->totalBudget->getMajorAmount();
    }

    public function loadBalanceData()
    {
        $org = Organization::current();
        $date = Carbon::create($this->year, $this->month, 1);
        $service = app(CashbookService::class);

        $balance = \App\Models\AccountBalance::where('organization_id', $org->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $this->openingBalance = $balance ? $balance->opening_balance : new \App\ValueObjects\Money(0, $org->currency_code);
        $this->newOpeningBalanceAmount = $this->openingBalance->getMajorAmount();

        // Live balance calculation
        $this->liveBalance = $service->getLiveAccountBalance(Carbon::parse($this->dateString), $org);
    }

    public function openBudgetModal()
    {
        $this->showBudgetModal = true;
    }

    public function openBalanceModal()
    {
        if (! auth()->user()->isAdmin()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized: Only admins can set initial balance.']);

            return;
        }
        $this->showBalanceModal = true;
    }

    public function saveBalance()
    {
        $org = Organization::current();

        \App\Models\AccountBalance::updateOrCreate([
            'organization_id' => $org->id,
            'month' => $this->month,
            'year' => $this->year,
        ], [
            'opening_balance' => \App\ValueObjects\Money::fromMajor($this->newOpeningBalanceAmount, $org->currency_code),
        ]);

        $this->showBalanceModal = false;
        $this->loadBalanceData();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Monthly initial balance updated successfully.',
        ]);
    }

    public function saveBudget()
    {
        $org = Organization::current();

        $budget = \App\Models\ExpenseBudget::updateOrCreate([
            'organization_id' => $org->id,
            'month' => $this->month,
            'year' => $this->year,
        ], [
            'total_budget_amount' => \App\ValueObjects\Money::fromMajor($this->newBudgetAmount, $org->currency_code),
        ]);

        $this->showBudgetModal = false;
        $this->loadBudgetData();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Monthly expense budget updated successfully.',
        ]);
    }

    public function render()
    {
        $org = Organization::current();
        $entries = CashbookEntry::where('organization_id', $org->id)
            ->whereMonth('entry_date', $this->month)
            ->whereYear('entry_date', $this->year)
            ->orderBy('entry_date', 'desc')
            ->get();

        // Group by week
        $groupedEntries = $entries->groupBy(function ($entry) {
            return 'Week '.ceil($entry->entry_date->day / 7);
        });

        // Calculate Month Stats
        $stats = [
            'total_inflow' => new Money($entries->sum(fn ($e) => $e->total_inflow->getMinorAmount()), $org->currency_code),
            'total_outflow' => new Money($entries->sum(fn ($e) => $e->total_outflow->getMinorAmount()), $org->currency_code),
            'total_expenses' => new Money($entries->sum(fn ($e) => $e->daily_expense_amount->getMinorAmount()), $org->currency_code),
            'days_count' => $entries->count(),
            'verified_count' => $entries->where('status', 'verified')->count(),
        ];

        return view('livewire.cashbook.month-record', [
            'groupedEntries' => $groupedEntries,
            'stats' => $stats,
            'currentMonthName' => Carbon::create($this->year, $this->month, 1)->format('F Y'),
        ])->layout('layouts.app', ['title' => 'Monthly Audit Ledger']);
    }
}
