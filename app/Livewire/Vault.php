<?php

namespace App\Livewire;

use App\Models\Collateral;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Vault extends Component
{
    use WithPagination;

    #[Url]
    public $filter = 'all';

    #[Url]
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
        $this->viewingAsset = Collateral::where('organization_id', Auth::user()->organization_id)
            ->with('loan.borrower.user')
            ->find($id);
        $this->showViewModal = true;
    }

    public function deleteAsset($id)
    {
        $asset = Collateral::where('organization_id', Auth::user()->organization_id)->find($id);
        if ($asset) {
            $asset->delete();
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Asset deleted from vault.']);
        }
        $this->showViewModal = false;
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $query = Collateral::where('organization_id', $orgId)->with('loan.borrower.user');

        if ($this->filter === 'in_vault') {
            $query->where('status', 'in_vault');
        } elseif ($this->filter === 'returned') {
            $query->where('status', 'returned');
        }

        if (! empty($this->search)) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(type) LIKE ?', [$term])
                    ->orWhereHas('loan', function ($sq) use ($term) {
                        $sq->where('loan_number', 'like', $term)
                            ->orWhereHas('borrower', function ($bq) use ($term) {
                                $bq->where('national_identity_number', 'like', $term)
                                    ->orWhere('bvn', 'like', $term)
                                    ->orWhere('phone', 'like', $term)
                                    ->orWhereHas('user', function ($uq) use ($term) {
                                        $uq->whereRaw('LOWER(name) LIKE ?', [$term])
                                            ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                                    });
                            });
                    });
            });
        }

        $assets = $query->latest()->paginate(10);

        $totalValue = Collateral::where('organization_id', $orgId)->where('status', 'in_vault')->sum('value') / 100;
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
