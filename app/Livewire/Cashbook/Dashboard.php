<?php

namespace App\Livewire\Cashbook;

use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Services\CashbookService;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $currentDate;

    public $entry;

    public $remainingBudget;

    public $accountBalance;

    public $showShortfallModal = false;

    public $shortfall_report = '';

    public $errorMessage = '';

    protected $queryString = ['currentDate' => ['as' => 'date']];

    protected $listeners = ['validation-complete' => 'loadEntry'];

    public $manualFields = [
        'description' => '',
        'card_payments' => 0,
        'excess_cash' => 0,
        'default_amount' => 0,
        'bank_withdrawals' => 0,
        'charges' => 0,
        'bonuses' => 0,
        'daily_expense_amount' => 0,
        'actual_cash_at_hand' => 0,
        'bank_deposit_amount' => 0,
        'shortfall_report' => '',
    ];

    public function mount()
    {
        $org = Organization::current();
        if (! $this->currentDate) {
            $this->currentDate = $org->getSystemTime()->toDateString();
        }
        $this->loadEntry();
    }

    public function loadEntry()
    {
        $service = app(CashbookService::class);
        $org = Organization::current();
        $date = Carbon::parse($this->currentDate);

        $this->entry = $service->getEntryForDate($date, $org);

        // Auto-fetch data if it's a new or pending entry
        if ($this->entry->status === 'pending') {
            $service->fetchSystemData($this->entry);
        }

        $this->remainingBudget = $service->getRemainingBudget($date, $org);
        $this->accountBalance = $service->getLiveAccountBalance($date, $org);
        $this->shortfall_report = $this->entry->shortfall_report;
        $this->fillManualFields();
    }

    public function fillManualFields()
    {
        foreach (array_keys($this->manualFields) as $field) {
            $value = $this->entry->{$field};
            $this->manualFields[$field] = $value instanceof Money
                ? $value->getMajorAmount()
                : $value;
        }
    }

    public function updatedManualFields()
    {
        $this->saveManualFields();
    }

    public function saveManualFields()
    {
        if ($this->entry->status === 'verified') {
            return;
        }

        // Authorization: Check for manage_vault OR record_cashbook
        if (! auth()->user()->can('manage_vault') && ! auth()->user()->can('record_cashbook')) {
            $this->dispatch('notify', type: 'error', message: 'Unauthorized to record cashbook entries.');

            return;
        }

        foreach ($this->manualFields as $field => $value) {
            // Restriction: Only Admin can edit Charges and Bonuses
            if (in_array($field, ['charges', 'bonuses']) && ! auth()->user()->isAdmin()) {
                continue;
            }
            $this->entry->{$field} = $value;
        }

        $service = app(CashbookService::class);
        $service->recalculateExpectedCash($this->entry);
        $this->remainingBudget = $service->getRemainingBudget(Carbon::parse($this->currentDate), Organization::current());
        $this->accountBalance = $service->getLiveAccountBalance(Carbon::parse($this->currentDate), Organization::current());
        $this->entry->save();
        $this->errorMessage = '';
    }

    public function submitShortfallReport()
    {
        $this->entry->shortfall_report = $this->shortfall_report;
        $this->entry->save();
        $this->showShortfallModal = false;
        $this->verify();
    }

    public function unlock()
    {
        $user = auth()->user();
        $org = Organization::current();

        // 1. Admin Bypass
        if ($user->isAdmin()) {
            return $this->performUnlock('Record unlocked by Administrator.');
        }

        // 2. Staff Authorization
        if (! $user->can('record_cashbook') && ! $user->can('manage_vault')) {
            return $this->dispatch('notify', type: 'error', message: 'Unauthorized.');
        }

        // 3. Staff Trial Check
        if (! ($org->allow_staff_cashbook_unlock ?? true)) {
            return $this->dispatch('notify', type: 'error', message: 'Staff unlocking is disabled by Administrator.');
        }

        $limit = $org->cashbook_unlock_limit ?? 3;
        if ($this->entry->staff_unlock_count >= $limit) {
            return $this->dispatch('notify', type: 'error', message: "Unlock failed. All {$limit} staff trials for this date have been exhausted. Please contact Admin.");
        }

        // 4. Perform Staff Unlock (Increment Trial)
        $this->entry->increment('staff_unlock_count');

        return $this->performUnlock('Record unlocked. Trials used: '.$this->entry->staff_unlock_count.'/'.$limit);
    }

    protected function performUnlock(string $message)
    {
        $this->entry->status = 'pending';
        $this->entry->verified_at = null;
        $this->entry->audit_hash = null;
        $this->entry->shortfall_report = null;
        $this->entry->save();

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function verify()
    {
        $service = app(CashbookService::class);
        $this->errorMessage = '';

        // Requirement: Bank Deposit must have a value
        if ($this->entry->bank_deposit_amount->isZero()) {
            $this->errorMessage = 'Verification Failed: Bank Deposit Amount is mandatory to close the day.';

            return;
        }

        // Requirement: Bank Deposit must match Expected Deposit (unless Admin)
        if ($this->entry->bank_deposit_amount->getMinorAmount() < $this->entry->expected_deposit->getMinorAmount()) {
            if (! auth()->user()->isAdmin()) {
                $this->errorMessage = 'Verification Failed: Entered bank deposit is lower than the expected bank deposit ('.$this->entry->expected_deposit->format().'). Please reconcile or contact Admin.';

                return;
            }
        }

        if ($service->verifyEntry($this->entry)) {
            $this->dispatch('notify', type: 'success', message: 'Cashbook verified and locked successfully.');
        } else {
            $this->dispatch('notify', type: 'error', message: 'Discrepancy detected! Please reconcile the cash at hand.');
        }
    }

    public function render()
    {
        $history = CashbookEntry::orderBy('entry_date', 'desc')
            ->limit(10)
            ->get()
            ->groupBy(fn ($e) => $e->entry_date->format('F Y'));

        return view('livewire.cashbook.dashboard', [
            'history' => $history,
        ])->layout('layouts.app', ['title' => 'Digital Cashbook']);
    }
}
