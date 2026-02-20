<?php

namespace App\Livewire\Borrower\Account;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanAgreements extends Component
{
    public function render()
    {
        $loans = Loan::where('borrower_id', Auth::user()->borrower->id)
            ->whereIn('status', ['active', 'repaid', 'overdue'])
            ->latest()
            ->get();

        return view('livewire.borrower.account.loan-agreements', [
            'loans' => $loans,
        ])->layout('layouts.borrower', ['title' => 'Loan Agreements']);
    }
}
