<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DistributionPanel extends Component
{
    public function mount()
    {
        if (Auth::user()->email !== 'nahjonah00@gmail.com') {
            abort(403, 'Unauthorized access.');
        }
    }

    public function toggleStatus($orgId)
    {
        $org = Organization::find($orgId);
        $org->status = $org->status === 'active' ? 'suspended' : 'active';
        $org->save();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "Organization {$org->status} successfully."]);
    }

    public function updateKycStatus($orgId, $status)
    {
        $org = Organization::find($orgId);
        $org->kyc_status = $status;
        $org->save();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "KYC status updated to {$status}."]);
    }

    public function render()
    {
        $organizations = Organization::withCount(['borrowers', 'loans', 'staff'])
            ->with(['users' => function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', '!=', 'Borrower');
                });
            }])
            ->withSum(['loans as total_lent' => function ($q) {
                $q->whereIn('status', ['active', 'repaid', 'overdue']);
            }], 'amount')
            ->get();

        // Calculate total collected manually or via a complex query.
        // For simplicity/performance balance, let's do it in the view loop or a separate mapped collection if dataset is small.
        // Assuming reasonably small number of orgs for now.

        foreach ($organizations as $org) {
            $org->total_lent = (float) ($org->total_lent ?? 0) / 100;
            $org->total_collected = \App\Models\Repayment::whereHas('loan', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })->sum('amount') / 100;

            // Monthly Activity
            $org->monthly_lent = $org->loans()
                ->whereIn('status', ['active', 'repaid', 'overdue'])
                ->whereMonth('created_at', now()->month)
                ->sum('amount') / 100;
            $org->monthly_collected = \App\Models\Repayment::whereHas('loan', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })->whereMonth('paid_at', now()->month)->sum('amount') / 100;

            $org->active_loans_count = $org->loans->whereIn('status', ['active', 'overdue'])->count();
        }

        return view('livewire.admin.distribution-panel', [
            'organizations' => $organizations,
        ])->layout('layouts.app', ['title' => 'Distribution Panel']);
    }
}
