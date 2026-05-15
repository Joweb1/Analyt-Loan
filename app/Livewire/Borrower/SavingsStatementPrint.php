<?php

namespace App\Livewire\Borrower;

use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use Livewire\Component;

class SavingsStatementPrint extends Component
{
    public User $user;

    public $savingsAccount;

    public $transactions;

    public function mount(User $user)
    {
        $this->user = $user->load(['organization']);
        $this->savingsAccount = SavingsAccount::where('user_id', $this->user->id)->firstOrFail();
        $this->transactions = SavingsTransaction::where('savings_account_id', $this->savingsAccount->id)
            ->with('staff')
            ->latest('transaction_date')
            ->get();
    }

    public function render()
    {
        return view('livewire.borrower.savings-statement-print')->layout('layouts.print', ['title' => 'Savings Statement - '.$this->user->name]);
    }
}
