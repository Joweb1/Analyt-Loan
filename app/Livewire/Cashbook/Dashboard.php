<?php

namespace App\Livewire\Cashbook;

use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Services\CashbookService;
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
            $this->manualFields[$field] = $value instanceof \App\ValueObjects\Money
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
        $this->entry->save();

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function verify()
    {
        $service = app(CashbookService::class);
        $this->errorMessage = '';

        $systemCashInflow = $this->entry->total_inflow->subtract($this->entry->expected_bank_transfers);
        $minimumCritical = $this->entry->registration_fees->add($this->entry->card_payments);

        // Validation: If there is a shortfall in physical cash...
        if ($this->entry->actual_cash_at_hand->getMinorAmount() < $systemCashInflow->getMinorAmount()) {

            // If no report has been provided yet, we check if we should show the modal or an error.
            if (empty($this->entry->shortfall_report)) {
                // The user specifically requested a report if cash is less than (Reg Fees + Card Payments)
                if ($this->entry->actual_cash_at_hand->getMinorAmount() < $minimumCritical->getMinorAmount()) {
                    $this->showShortfallModal = true;

                    return;
                }

                // For other cash shortfalls, we'll also allow them to be explained via the same modal
                // since the user mentioned "it should not prevent the verification".
                $this->showShortfallModal = true;

                return;
            }

            // If a report IS present, we allow the code to proceed to the bank deposit check.
        }

        // New Validation Rule: Total Bank Deposit must cover physical cash plus mandatory recorded inflows.
        $otherCashInflows = $systemCashInflow->subtract($minimumCritical);
        $threshold = $this->entry->actual_cash_at_hand->add($otherCashInflows);

        if ($this->entry->bank_deposit_amount->getMinorAmount() < $threshold->getMinorAmount()) {
            $this->errorMessage = 'Verification Failed: Total bank deposit is less than physical cash plus mandatory inflows. Please ensure all cash is accounted for.';

            return;
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
