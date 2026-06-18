<?php

namespace App\Livewire\Cashbook;

use App\Models\CashbookEntry;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ValidationModal extends Component
{
    public CashbookEntry $entry;
    public $bank_deposit;
    public $physical_cash;
    public $card_payments;
    public $excess_cash;
    public $shortfall_report;
    public $showModal = false;

    public function mount(CashbookEntry $entry)
    {
        $this->entry = $entry->fresh(['organization']);
        
        // Initialize fields with current values
        $this->bank_deposit = $this->entry->bank_deposit_amount?->getMajorAmount();
        $this->physical_cash = $this->entry->actual_cash_at_hand?->getMajorAmount();
        $this->card_payments = $this->entry->card_payments?->getMajorAmount();
        $this->excess_cash = $this->entry->excess_cash?->getMajorAmount();
        $this->shortfall_report = $this->entry->shortfall_report;

        // Check if day is not locked and needs validation
        if ($this->entry->status === 'pending') {
            $this->showModal = true;
        }
    }

    public function getExpectedBankProperty()
    {
        $org = $this->entry->organization;
        $currency = $org->currency_code;
        $zero = new Money(0, $currency);
        
        $baseInflow = ($this->entry->loan_repayments ?? $zero)
            ->add($this->entry->loan_interest ?? $zero)
            ->add($this->entry->savings_deposits ?? $zero)
            ->add($this->entry->daily_savings ?? $zero)
            ->add($this->entry->registration_fees ?? $zero)
            ->add($this->entry->loan_processing_fees ?? $zero)
            ->add($this->entry->insurance_fees ?? $zero)
            ->add($this->entry->default_amount ?? $zero);

        $enteredCard = Money::fromMajor($this->card_payments ?: 0, $currency);
        $enteredExcess = Money::fromMajor($this->excess_cash ?: 0, $currency);
        
        return $baseInflow->add($enteredCard)->add($enteredExcess);
    }

    public function getIsBankMatchedProperty(): bool
    {
        $org = $this->entry->organization;
        $currency = $org->currency_code;
        $enteredBank = Money::fromMajor($this->bank_deposit ?: 0, $currency);
        
        return $enteredBank->getMinorAmount() >= $this->expected_bank->getMinorAmount();
    }

    public function validateAndClose()
    {
        $org = $this->entry->organization;
        $currency = $org->currency_code;
        
        $enteredBank = Money::fromMajor($this->bank_deposit ?: 0, $currency);
        $expectedBank = $this->expected_bank;
        
        // 1. Bank Validation: Must be entered and must meet threshold
        if (empty($this->bank_deposit) || $this->bank_deposit <= 0) {
            $this->addError('bank_deposit', 'Total bank deposit amount is mandatory.');
            return;
        }

        if ($enteredBank->getMinorAmount() < $expectedBank->getMinorAmount()) {
            $this->addError('bank_deposit', 'Entered bank deposit is lower than expected. Please audit or contact Admin.');
            return;
        }

        // 2. Physical Cash Validation
        $expectedCash = $this->entry->expected_cash_at_hand;
        $enteredPhysical = Money::fromMajor($this->physical_cash ?: 0, $currency);

        if ($enteredPhysical->getMinorAmount() < $expectedCash->getMinorAmount() && empty($this->shortfall_report)) {
            $this->addError('shortfall_report', 'Physical cash shortfall detected. Please provide a report.');
            return;
        }
// Update Entry
$this->entry->update([
    'bank_deposit_amount' => $enteredBank,
    'actual_cash_at_hand' => $enteredPhysical,
    'card_payments' => Money::fromMajor($this->card_payments ?: 0, $currency),
    'excess_cash' => Money::fromMajor($this->excess_cash ?: 0, $currency),
    'shortfall_report' => $this->shortfall_report,
    'status' => 'pending', // Keep as pending until verified
]);

$this->showModal = false;
$this->dispatch('validation-complete');
$this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Cashbook validated.']);
}

public function override()
{
if (!Auth::user()->isAdmin()) {
    return;
}

$org = $this->entry->organization;
$currency = $org->currency_code;

// Save whatever was entered, but skip the strict check
$this->entry->update([
    'bank_deposit_amount' => Money::fromMajor($this->bank_deposit ?: 0, $currency),
    'actual_cash_at_hand' => Money::fromMajor($this->physical_cash ?: 0, $currency),
    'card_payments' => Money::fromMajor($this->card_payments ?: 0, $currency),
    'excess_cash' => Money::fromMajor($this->excess_cash ?: 0, $currency),
    'shortfall_report' => $this->shortfall_report,
    'status' => 'pending',
]);

$this->showModal = false;
$this->dispatch('validation-complete');
$this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Validation overridden by Admin. Data saved.']);
}

    public function render()
    {
        return view('livewire.cashbook.validation-modal');
    }
}
