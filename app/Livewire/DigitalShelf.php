<?php

namespace App\Livewire;

use App\Models\Collateral;
use Livewire\Component;

class DigitalShelf extends Component
{
    public $collaterals;

    public function mount()
    {
        $this->collaterals = Collateral::all();
    }

    public function render()
    {
        return view('livewire.components.digital-shelf');
    }
}
