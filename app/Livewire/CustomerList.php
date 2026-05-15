<?php

namespace App\Livewire;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public $viewMode = 'grid';

    public $search = '';

    public $portfolioId = null;

    public $roleFilter = ''; // borrower or saver

    #[\Livewire\Attributes\Url]
    public $showFilters = false;

    public $portfolios = [];

    protected $updatesQueryString = ['search', 'portfolioId', 'roleFilter'];

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

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function toggleView($mode)
    {
        $this->viewMode = $mode;
    }

    public function render()
    {
        // Query users of type 'customer' belonging to the organization
        $query = User::where('type', 'customer')
            ->where('organization_id', Auth::user()->organization_id)
            ->with(['borrower', 'saver', 'guarantor', 'roles']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->roleFilter) {
            $query->role($this->roleFilter);
        }

        if ($this->portfolioId) {
            // This might need adjustment if portfolio is on profile models
            $query->where(function ($q) {
                $q->whereHas('borrower', fn ($bq) => $bq->where('portfolio_id', $this->portfolioId))
                    ->orWhereHas('saver', fn ($sq) => $sq->where('portfolio_id', $this->portfolioId))
                    ->orWhereHas('guarantor', fn ($gq) => $gq->where('portfolio_id', $this->portfolioId));
            });
        }

        $customers = $query->latest()->paginate(11);

        return view('livewire.customer-list', [
            'customers' => $customers,
        ])->layout('layouts.app', ['title' => 'Manage Customers']);
    }
}
