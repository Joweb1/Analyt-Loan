<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class PendingLoans extends Component
{
    use WithPagination;

    public function render()
    {
        $loans = Loan::with('borrower.user')
            ->where('status', 'applied')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('livewire.pending-loans', [
            'loans' => $loans
        ])->layout('layouts.app');
    }
}
