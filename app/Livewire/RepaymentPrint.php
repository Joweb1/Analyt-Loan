<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;

class RepaymentPrint extends Component
{
    public Loan $loan;

    public function mount(Loan $loan)
    {
        $this->loan = $loan->load(['borrower.user', 'organization', 'repayments.collector']);
    }

    public function render()
    {
        return view('livewire.repayment-print')->layout('layouts.print');
    }
}
