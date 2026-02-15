<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;

class SchedulePrint extends Component
{
    public Loan $loan;

    public function mount(Loan $loan)
    {
        $this->loan = $loan->load(['borrower.user', 'organization', 'scheduledRepayments']);
    }

    public function render()
    {
        return view('livewire.schedule-print')->layout('layouts.print');
    }
}
