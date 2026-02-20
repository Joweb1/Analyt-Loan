<?php

namespace App\Livewire\Borrower\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Support extends Component
{
    public $organization;

    public function mount()
    {
        $this->organization = Auth::user()->organization;
    }

    public function render()
    {
        return view('livewire.borrower.account.support')->layout('layouts.borrower', ['title' => 'Help & Support']);
    }
}
