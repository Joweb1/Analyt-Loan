<?php

namespace App\Livewire;

use Livewire\Component;

class Records extends Component
{
    public function render()
    {
        return view('livewire.records')
            ->layout('layouts.app', ['title' => 'Financial Records']);
    }
}
