<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class UserLoans extends Component
{
    use WithPagination;

    public Borrower $borrower;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load(['user', 'loans.repayments']);
    }

    public function getRiskLevel($score)
    {
        if ($score >= 750) return ['label' => 'Low Risk', 'color' => 'green'];
        if ($score >= 600) return ['label' => 'Medium Risk', 'color' => 'yellow'];
        return ['label' => 'High Risk', 'color' => 'red'];
    }

    public function render()
    {
        $loans = Loan::where('borrower_id', $this->borrower->id)
            ->with(['repayments', 'collateral'])
            ->latest()
            ->paginate(12);

        return view('livewire.user-loans', [
            'loans' => $loans
        ])->layout('layouts.app');
    }
}
