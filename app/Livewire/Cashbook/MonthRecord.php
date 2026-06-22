<?php

namespace App\Livewire\Cashbook;

use App\Models\AccountBalance;
use App\Models\CashbookEntry;
use App\Models\ExpenseBudget;
use App\Models\Organization;
use App\Models\Transaction;
use App\Services\CashbookService;
use App\Services\TransactionService;
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

    public $entry;

    protected $queryString = ['dateString' => ['as' => 'date']];

    public function mount(CashbookService $service)
    {
        $org = Organization::current();
        if (! $this->dateString) {
            $this->dateString = $org->getSystemTime()->toDateString();
        }

        $this->entry = $service->getEntryForDate(Carbon::parse($this->dateString), $org);

        $date = Carbon::parse($this->dateString);
        $this->month = $date->month;
        $this->year = $date->year;

        $this->loadBudgetData();
        $this->loadBalanceData();
    }

    public function loadBudgetData()
    {
        $org = Organization::current();
        $service = app(CashbookService::class);
        $date = Carbon::create($this->year, $this->month, 1);

        $this->totalBudget = $service->getTotalBudget($date, $org);
        $this->remainingBudget = $service->getRemainingBudget($date, $org);
        $this->newBudgetAmount = $this->totalBudget->getMajorAmount();
    }

    public function loadBalanceData()
    {
        $org = Organization::current();
        $service = app(CashbookService::class);

        $balance = AccountBalance::where('organization_id', $org->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $this->openingBalance = $balance ? $balance->opening_balance : new Money(0, $org->currency_code);
        $this->newOpeningBalanceAmount = $this->openingBalance->getMajorAmount();

        // Live balance calculation
        $this->liveBalance = $service->getLiveAccountBalance(Carbon::parse($this->dateString), $org);
    }

    public function openBudgetModal()
    {
        if (! auth()->user()->isAdmin()) {
            $this->dispatch('notify', type: 'error', message: 'Unauthorized: Only admins can set budgets.');

            return;
        }
        $this->showBudgetModal = true;
    }

    public function openBalanceModal()
    {
        if (! auth()->user()->isAdmin()) {
            $this->dispatch('notify', type: 'error', message: 'Unauthorized: Only admins can set initial balance.');

            return;
        }
        $this->showBalanceModal = true;
    }

    public function saveBalance()
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $org = Organization::current();
        $amount = Money::fromMajor($this->newOpeningBalanceAmount, $org->currency_code);

        $existing = AccountBalance::where('organization_id', $org->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $balance = AccountBalance::updateOrCreate([
            'organization_id' => $org->id,
            'month' => $this->month,
            'year' => $this->year,
        ], [
            'opening_balance' => $amount,
        ]);

        if ($existing) {
            $oldAmount = $existing->opening_balance;
            $difference = $amount->subtract($oldAmount);

            // Find previous transaction
            $originalTransaction = Transaction::where('related_id', $balance->id)
                ->where('related_type', get_class($balance))
                ->where('type', 'balance_update')
                ->whereNull('parent_id')
                ->first();

            TransactionService::record(
                type: 'adjustment',
                amount: $difference,
                related: $balance,
                notes: 'Adjustment for Monthly opening balance update. Original: ₦'.$oldAmount->format().', New: ₦'.$amount->format(),
                parentId: $originalTransaction?->id
            );
        } else {
            TransactionService::record(
                type: 'balance_update',
                amount: $amount,
                related: $balance,
                notes: 'Monthly opening balance set for '.Carbon::create($this->year, $this->month, 1)->format('F Y')
            );
        }

        $this->showBalanceModal = false;
        $this->loadBalanceData();

        $this->dispatch('notify', type: 'success', message: 'Monthly initial balance updated successfully.');
    }

    public function saveBudget()
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $org = Organization::current();
        $amount = Money::fromMajor($this->newBudgetAmount, $org->currency_code);

        $existing = ExpenseBudget::where('organization_id', $org->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $budget = ExpenseBudget::updateOrCreate([
            'organization_id' => $org->id,
            'month' => $this->month,
            'year' => $this->year,
        ], [
            'total_budget_amount' => $amount,
        ]);

        if ($existing) {
            $oldAmount = $existing->total_budget_amount;
            $difference = $amount->subtract($oldAmount);

            // Find previous transaction
            $originalTransaction = Transaction::where('related_id', $budget->id)
                ->where('related_type', get_class($budget))
                ->where('type', 'budget_update')
                ->whereNull('parent_id')
                ->first();

            TransactionService::record(
                type: 'adjustment',
                amount: $difference,
                related: $budget,
                notes: 'Adjustment for Monthly expense budget update. Original: ₦'.$oldAmount->format().', New: ₦'.$amount->format(),
                parentId: $originalTransaction?->id
            );
        } else {
            TransactionService::record(
                type: 'budget_update',
                amount: $amount,
                related: $budget,
                notes: 'Monthly expense budget set for '.Carbon::create($this->year, $this->month, 1)->format('F Y')
            );
        }

        $this->showBudgetModal = false;
        $this->loadBudgetData();

        $this->dispatch('notify', type: 'success', message: 'Monthly expense budget updated successfully.');
    }

    public function render()
    {
        $org = Organization::current();
        $entries = CashbookEntry::where('organization_id', $org->id)
            ->whereMonth('entry_date', $this->month)
            ->whereYear('entry_date', $this->year)
            ->orderBy('entry_date', 'desc')
            ->get();

        $stats = [
            'days_count' => $entries->count(),
            'verified_count' => $entries->where('status', 'verified')->count(),
            'total_inflow' => $entries->reduce(fn ($carry, $e) => $carry->add($e->total_inflow), new Money(0, $org->currency_code)),
            'total_outflow' => $entries->reduce(fn ($carry, $e) => $carry->add($e->total_outflow), new Money(0, $org->currency_code)),
        ];

        return view('livewire.cashbook.month-record', [
            'entry' => $this->entry,
            'groupedEntries' => $entries->groupBy(fn ($e) => 'Week '.($e->entry_date->weekOfMonth)),
            'stats' => $stats,
            'currentMonthName' => Carbon::create($this->year, $this->month, 1)->format('F Y'),
        ])->layout('layouts.app', ['title' => 'Monthly Vault Record']);
    }
}
