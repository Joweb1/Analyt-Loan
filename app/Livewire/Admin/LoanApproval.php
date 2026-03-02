<?php

namespace App\Livewire\Admin;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanApproval extends Component
{
    use WithPagination;

    public function approveLoan($id)
    {
        $loan = Loan::findOrFail($id);

        // Approve the loan
        $loan->update(['status' => 'approved']);

        // Log the action
        \App\Helpers\SystemLogger::success(
            'Loan Approved',
            'Loan #'.$loan->loan_number.' was approved.',
            'loan',
            $loan
        );

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Loan #'.$loan->loan_number.' Approved']);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        // Looking for 'applied' or 'pending' depending on system flow.
        // Assuming 'applied' is the initial state before approval based on PendingLoans.php.
        $loans = Loan::with('borrower.user')
            ->where('organization_id', $orgId)
            ->whereIn('status', ['applied', 'pending'])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.loan-approval', [
            'loans' => $loans,
        ])->layout('layouts.app', ['title' => 'Loan Approval']);
    }
}
