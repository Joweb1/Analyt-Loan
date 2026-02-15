<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $borrower = Auth::user()->borrower;
        $loans = $borrower ? Loan::where('borrower_id', $borrower->id)->latest()->get() : collect();
        $activeLoan = $loans->where('status', 'active')->first();
        
        return view('livewire.borrower.dashboard', [
            'loans' => $loans,
            'activeLoan' => $activeLoan,
        ])->layout('layouts.app');
    }
}
