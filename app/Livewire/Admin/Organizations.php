<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Organizations extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $kycFilter = '';

    public $selectedOrg = null;

    public $showDetailsModal = false;

    public function mount()
    {
        if (! Auth::user()->isAppOwner()) {
            abort(403);
        }
    }

    public function updating($property)
    {
        if (in_array($property, ['search', 'statusFilter', 'kycFilter'])) {
            $this->resetPage();
        }
    }

    public function viewDetails($orgId)
    {
        $this->selectedOrg = Organization::with(['users' => function ($q) {
            $q->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', '!=', 'Borrower');
            })->latest();
        }])->withCount(['borrowers', 'loans', 'staff'])->find($orgId);

        $this->showDetailsModal = true;
    }

    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedOrg = null;
    }

    public function toggleStatus($orgId)
    {
        $org = Organization::find($orgId);
        $org->status = $org->status === 'active' ? 'suspended' : 'active';
        $org->save();

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "Organization {$org->status} successfully."]);

        if ($this->selectedOrg && $this->selectedOrg->id === $orgId) {
            $this->selectedOrg->status = $org->status;
        }
    }

    public function approveKyc($orgId)
    {
        $org = Organization::find($orgId);
        $org->kyc_status = 'approved';
        $org->save();

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "KYC approved for {$org->name}."]);

        if ($this->selectedOrg && $this->selectedOrg->id === $orgId) {
            $this->selectedOrg->kyc_status = 'approved';
        }
    }

    public function render()
    {
        $query = Organization::withCount(['borrowers', 'loans', 'staff'])
            ->when($this->search, function ($q) {
                $term = '%'.strtolower(trim($this->search)).'%';
                $q->where(function ($sq) use ($term) {
                    $sq->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                        ->orWhere('rc_number', 'like', $term);
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->kycFilter, function ($q) {
                $q->where('kyc_status', $this->kycFilter);
            })
            ->latest();

        return view('livewire.admin.organizations', [
            'organizations' => $query->paginate(10),
        ])->layout('layouts.app', ['title' => 'Manage Organizations']);
    }
}
