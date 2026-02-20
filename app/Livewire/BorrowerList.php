<?php

namespace App\Livewire;

use App\Models\Borrower;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowerList extends Component
{
    use WithPagination;

    public $viewMode = 'grid';

    public $search = '';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleView($mode)
    {
        $this->viewMode = $mode;
    }

    public function render()
    {
        $query = Borrower::with(['user', 'loans', 'savingsAccount']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($uq) {
                    $uq->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('bvn', 'like', '%'.$this->search.'%')
                    ->orWhere('national_identity_number', 'like', '%'.$this->search.'%');
            });
        }

        $borrowers = $query->latest()->paginate(11); // 11 to leave room for "Add Card" in grid

        return view('livewire.borrower-list', [
            'borrowers' => $borrowers,
        ])->layout('layouts.app', ['title' => 'Borrower Directory']);
    }
}
