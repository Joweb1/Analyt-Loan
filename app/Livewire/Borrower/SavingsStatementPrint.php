<?php

namespace App\Livewire\Borrower;

use App\Models\Borrower;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Livewire\Component;

class SavingsStatementPrint extends Component
{
    public Borrower $borrower;

    public $savingsAccount;

    public $transactions;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load(['user', 'organization']);
        $this->savingsAccount = SavingsAccount::where('borrower_id', $this->borrower->id)->firstOrFail();
        $this->transactions = SavingsTransaction::where('savings_account_id', $this->savingsAccount->id)
            ->with('staff')
            ->latest('transaction_date')
            ->get();
    }

    public function render()
    {
        return view('livewire.borrower.savings-statement-print')->layout('layouts.print', ['title' => 'Savings Statement - '.$this->borrower->user->name]);
    }
}
