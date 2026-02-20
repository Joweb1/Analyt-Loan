<?php

namespace App\Livewire\Borrower\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PersonalDetails extends Component
{
    public $name;

    public $email;

    public $phone;

    public $address;

    public $dob;

    public function mount()
    {
        $user = Auth::user();
        $borrower = $user->borrower;

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $borrower->address;
        $this->dob = $borrower->date_of_birth;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'phone' => $this->phone,
        ]);

        $user->borrower->update([
            'address' => $this->address,
        ]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Personal details updated.']);
    }

    public function render()
    {
        return view('livewire.borrower.account.personal-details')->layout('layouts.borrower', ['title' => 'Personal Details']);
    }
}
