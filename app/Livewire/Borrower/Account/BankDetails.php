<?php

namespace App\Livewire\Borrower\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BankDetails extends Component
{
    public $bank_name;

    public $account_number;

    public $account_name;

    public function mount()
    {
        $borrower = Auth::user()->borrower;
        $details = $borrower->bank_account_details ?? [];

        $this->bank_name = $details['bank_name'] ?? '';
        $this->account_number = $details['account_number'] ?? '';
        $this->account_name = $details['account_name'] ?? '';
    }

    public function save()
    {
        $this->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string|min:10|max:10',
            'account_name' => 'required|string',
        ]);

        Auth::user()->borrower->update([
            'bank_account_details' => [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
            ],
        ]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Bank details updated.']);
    }

    public function render()
    {
        return view('livewire.borrower.account.bank-details')->layout('layouts.borrower', ['title' => 'Bank Details']);
    }
}
