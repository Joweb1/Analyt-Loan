<?php

namespace App\Livewire\Borrower\Onboarding;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.borrower', ['title' => 'Identity Verification'])]
class Identity extends Component
{
    public $date_of_birth;

    public $address;

    public $national_identity_number;

    public $bvn;

    public function mount()
    {
        $borrower = Auth::user()->borrower;
        if (! $borrower) {
            // Should not happen if registered correctly, but safeguard
            return redirect()->route('borrower.home');
        }

        if ($borrower->onboarding_step > 1) {
            return redirect()->route('borrower.onboarding.bank');
        }

        $this->date_of_birth = $borrower->date_of_birth;
        $this->address = $borrower->address;
        $this->national_identity_number = $borrower->national_identity_number;
        $this->bvn = $borrower->bvn;
    }

    public function save()
    {
        $this->validate([
            'date_of_birth' => 'required|date|before:18 years ago',
            'address' => 'required|string|min:10',
            'national_identity_number' => 'nullable|string|min:11',
            'bvn' => 'nullable|string|min:11',
        ]);

        $borrower = Auth::user()->borrower;
        $borrower->update([
            'date_of_birth' => $this->date_of_birth,
            'address' => $this->address,
            'national_identity_number' => $this->national_identity_number,
            'bvn' => $this->bvn,
            'onboarding_step' => 2,
        ]);

        return redirect()->route('borrower.onboarding.bank');
    }

    public function render()
    {
        return view('livewire.borrower.onboarding.identity');
    }
}
