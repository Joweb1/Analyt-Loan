<?php

namespace App\Livewire;

use App\Models\Borrower;
use Livewire\Component;

class BorrowerList extends Component
{
    public $borrowers;

    public function mount()
    {
        $this->borrowers = Borrower::with('user')->get();
    }

    public function render()
    {
        return view('livewire.borrower-list');
    }
}
