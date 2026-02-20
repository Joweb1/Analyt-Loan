<?php

namespace App\Livewire\Borrower;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Account extends Component
{
    public function logout(Logout $logout)
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.borrower.account', [
            'user' => Auth::user(),
        ])->layout('layouts.borrower', ['title' => 'My Profile']);
    }
}
