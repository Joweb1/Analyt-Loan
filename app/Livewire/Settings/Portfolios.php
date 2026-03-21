<?php

namespace App\Livewire\Settings;

use App\Models\Borrower;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Portfolios extends Component
{
    use WithPagination;

    public $showModal = false;

    public $portfolioId;

    public $name;

    public $description;

    public $staffIds = [];

    public $searchBorrower = '';

    // For assigning borrowers in the modal
    public $availableBorrowers = [];

    public $selectedBorrowerIds = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'staffIds' => 'array',
        'selectedBorrowerIds' => 'array',
    ];

    public function mount()
    {
        $this->availableBorrowers = Borrower::with('user')
            ->whereNull('portfolio_id')
            ->take(20)
            ->get();
    }

    public function updatedSearchBorrower()
    {
        $this->availableBorrowers = Borrower::with('user')
            ->where(function ($q) {
                $q->whereNull('portfolio_id')
                    ->orWhere('portfolio_id', $this->portfolioId);
            })
            ->whereHas('user', function ($q) {
                $q->where('name', 'like', '%'.$this->searchBorrower.'%');
            })
            ->take(20)
            ->get();
    }

    public function save()
    {
        $this->validate();

        $orgId = Auth::user()->organization_id;

        if ($this->portfolioId) {
            $portfolio = Portfolio::findOrFail($this->portfolioId);
            $portfolio->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $message = 'Portfolio updated successfully.';
        } else {
            $portfolio = Portfolio::create([
                'organization_id' => $orgId,
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $message = 'Portfolio created successfully.';
        }

        // Sync Staff
        $portfolio->staff()->sync($this->staffIds);

        // Assign Borrowers (Move selected to this portfolio, others in selection that were previously here stay?)
        // Actually, it's better to just move the selected ones here.
        if (! empty($this->selectedBorrowerIds)) {
            Borrower::whereIn('id', $this->selectedBorrowerIds)->update(['portfolio_id' => $portfolio->id]);
            // Also sync loans
            \App\Models\Loan::whereIn('borrower_id', $this->selectedBorrowerIds)->update(['portfolio_id' => $portfolio->id]);
        }

        $this->reset(['showModal', 'portfolioId', 'name', 'description', 'staffIds', 'selectedBorrowerIds', 'searchBorrower']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $portfolio = Portfolio::with(['staff', 'borrowers'])->findOrFail($id);
        $this->portfolioId = $portfolio->id;
        $this->name = $portfolio->name;
        $this->description = $portfolio->description;
        $this->staffIds = $portfolio->staff->pluck('id')->toArray();
        $this->selectedBorrowerIds = $portfolio->borrowers->pluck('id')->toArray();
        $this->showModal = true;

        $this->updatedSearchBorrower();
    }

    public function delete($id)
    {
        $portfolio = Portfolio::findOrFail($id);
        // Unassign borrowers and loans before deleting
        Borrower::where('portfolio_id', $portfolio->id)->update(['portfolio_id' => null]);
        \App\Models\Loan::where('portfolio_id', $portfolio->id)->update(['portfolio_id' => null]);

        $portfolio->delete();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Portfolio removed.']);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $allStaff = User::where('organization_id', $orgId)
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'Borrower');
            })->get();

        return view('livewire.settings.portfolios', [
            'portfolios' => Portfolio::with(['staff', 'borrowers'])->latest()->paginate(10),
            'allStaff' => $allStaff,
        ])->layout('layouts.app', ['title' => 'Portfolio Management']);
    }
}
