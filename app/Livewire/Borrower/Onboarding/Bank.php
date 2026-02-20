<?php

namespace App\Livewire\Borrower\Onboarding;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.borrower', ['title' => 'Bank Details'])]
class Bank extends Component
{
    public $bank_name;

    public $account_number;

    public $account_name;

    public function mount()
    {
        $borrower = Auth::user()->borrower;

        if ($borrower->onboarding_step < 2) {
            return redirect()->route('borrower.onboarding.identity');
        }
        if ($borrower->onboarding_step > 2) {
            return redirect()->route('borrower.onboarding.employment');
        }

        $details = $borrower->bank_account_details ?? [];
        $this->bank_name = $details['bank_name'] ?? '';
        $this->account_number = $details['account_number'] ?? '';
        $this->account_name = $details['account_name'] ?? '';
    }

    public function save()
    {
        $this->validate([
            'bank_name' => 'required|string|min:3',
            'account_number' => 'required|string|min:10|max:10',
            'account_name' => 'required|string|min:5',
        ]);

        $borrower = Auth::user()->borrower;
        $borrower->update([
            'bank_account_details' => [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
            ],
            'onboarding_step' => 3,
        ]);

        return redirect()->route('borrower.onboarding.employment');
    }

    public function render()
    {
        return view('livewire.borrower.onboarding.bank');
    }
}
