<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SidebarProfile extends Component
{
    public function render()
    {
        return view('livewire.components.sidebar-profile', [
            'user' => Auth::user()
        ]);
    }
}
