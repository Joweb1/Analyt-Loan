<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowerList extends Component
{
    use WithPagination;

    public $viewMode = 'grid';

    public $search = '';

    public $portfolioId = null;

    public $portfolios = [];

    protected $updatesQueryString = ['search', 'portfolioId'];

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPortfolioId()
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
                    ->orWhere('custom_id', 'like', '%'.$this->search.'%')
                    ->orWhere('bvn', 'like', '%'.$this->search.'%')
                    ->orWhere('national_identity_number', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
        }

        $borrowers = $query->latest()->paginate(11);

        return view('livewire.borrower-list', [
            'borrowers' => $borrowers,
        ])->layout('layouts.app', ['title' => 'Borrower Directory']);
    }
}
