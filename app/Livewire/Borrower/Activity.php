<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Activity extends Component
{
    public $tab = 'loans';

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $borrowerId = Auth::user()->borrower->id;

        $loans = [];
        $repayments = [];

        if ($this->tab === 'loans') {
            $loans = Loan::where('borrower_id', $borrowerId)
                ->with(['repayments', 'scheduledRepayments'])
                ->latest()
                ->get();
        } else {
            $repayments = Repayment::whereHas('loan', function ($q) use ($borrowerId) {
                $q->where('borrower_id', $borrowerId);
            })->latest('paid_at')->get();
        }

        return view('livewire.borrower.activity', [
            'loans' => $loans,
            'repayments' => $repayments,
        ])->layout('layouts.borrower', ['title' => 'Activity History']);
    }
}
