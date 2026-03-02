<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class KycApproval extends Component
{
    use WithPagination;

    public function approveKyc($id)
    {
        $borrower = Borrower::findOrFail($id);
        $borrower->update(['kyc_status' => 'approved']);

        // Log the action
        \App\Helpers\SystemLogger::success(
            'KYC Approved',
            'KYC for '.$borrower->user->name.' was approved.',
            'kyc',
            $borrower
        );

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'KYC Approved for '.$borrower->user->name]);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $borrowers = Borrower::with('user')
            ->where('organization_id', $orgId)
            ->where('kyc_status', 'pending')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.kyc-approval', [
            'borrowers' => $borrowers,
        ])->layout('layouts.app', ['title' => 'KYC Approval']);
    }
}
