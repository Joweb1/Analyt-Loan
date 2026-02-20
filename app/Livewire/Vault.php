<?php

namespace App\Livewire;

use App\Models\Collateral;
use Livewire\Component;
use Livewire\WithPagination;

class Vault extends Component
{
    use WithPagination;

    public $filter = 'all';

    public $search = '';

    // View Modal State
    public $viewingAsset = null;

    public $showViewModal = false;

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function viewAsset($id)
    {
        $this->viewingAsset = Collateral::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with('loan.borrower.user')
            ->find($id);
        $this->showViewModal = true;
    }

    public function deleteAsset($id)
    {
        $asset = Collateral::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)->find($id);
        if ($asset) {
            $asset->delete();
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Asset deleted from vault.']);
        }
        $this->showViewModal = false;
    }

    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $query = Collateral::where('organization_id', $orgId)->with('loan.borrower.user');

        if ($this->filter === 'in_vault') {
            $query->where('status', 'in_vault');
        } elseif ($this->filter === 'returned') {
            $query->where('status', 'returned');
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('loan', function ($q) use ($search) {
                        $q->where('loan_number', 'like', "%{$search}%")
                            ->orWhereHas('borrower', function ($q) use ($search) {
                                $q->where('national_identity_number', 'like', "%{$search}%")
                                    ->orWhere('bvn', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    });
                            });
                    });
            });
        }

        $assets = $query->latest()->paginate(10);

        $totalValue = Collateral::where('organization_id', $orgId)->where('status', 'in_vault')->sum('value');
        $inVaultCount = Collateral::where('organization_id', $orgId)->where('status', 'in_vault')->count();
        $returnedCount = Collateral::where('organization_id', $orgId)->where('status', 'returned')->count();

        return view('livewire.vault', [
            'assets' => $assets,
            'totalValue' => $totalValue,
            'inVaultCount' => $inVaultCount,
            'returnedCount' => $returnedCount,
        ])->layout('layouts.app', ['title' => 'Collateral Vault']);
    }
}
