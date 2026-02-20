<?php

namespace App\Livewire\Borrower\Onboarding;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.borrower', ['title' => 'Employment Info'])]
class Employment extends Component
{
    public $employer_name;

    public $monthly_income;

    public $employment_status;

    public function mount()
    {
        $borrower = Auth::user()->borrower;
        if ($borrower->onboarding_step < 3) {
            return redirect()->route('borrower.onboarding.bank');
        }

        $info = $borrower->employment_information ?? [];
        $this->employer_name = $info['employer_name'] ?? '';
        $this->monthly_income = $info['monthly_income'] ?? '';
        $this->employment_status = $info['employment_status'] ?? 'Employed';
    }

    public function save()
    {
        $this->validate([
            'employer_name' => 'required|string|min:3',
            'monthly_income' => 'required|numeric|min:1000',
            'employment_status' => 'required|string',
        ]);

        $borrower = Auth::user()->borrower;
        $borrower->update([
            'employment_information' => [
                'employer_name' => $this->employer_name,
                'monthly_income' => $this->monthly_income,
                'employment_status' => $this->employment_status,
                'job_title' => 'Not Specified', // Default for now
            ],
            'onboarding_step' => 4,
            'kyc_status' => 'approved', // Auto-approve for demo purposes
        ]);

        return redirect()->route('borrower.home');
    }

    public function render()
    {
        return view('livewire.borrower.onboarding.employment');
    }
}
