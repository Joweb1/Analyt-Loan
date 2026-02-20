<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SidebarProfile extends Component
{
    public function render()
    {
        return view('livewire.components.sidebar-profile', [
            'user' => Auth::user(),
        ]);
    }
}
