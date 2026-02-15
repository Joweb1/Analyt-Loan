<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;

class LoanPrint extends Component
{
    public Loan $loan;

    public function mount(Loan $loan)
    {
        $this->loan = $loan->load(['borrower.user', 'organization', 'repayments', 'scheduledRepayments', 'collateral']);
    }

    public function render()
    {
        return view('livewire.loan-print')->layout('layouts.print');
    }
}
