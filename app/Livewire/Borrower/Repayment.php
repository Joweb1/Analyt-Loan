<?php

namespace App\Livewire\Borrower;

use App\Models\PaymentProof;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Repayment extends Component
{
    use WithFileUploads;

    /** @var \App\Models\Loan|null */
    public $activeLoan;

    public $hasPendingApplication = false;

    public $amount;

    public $receipt;

    public $showUploadModal = false;

    public $referenceCode;

    public function mount()
    {
        $user = Auth::user();
        if (! $user->borrower || $user->borrower->kyc_status !== 'approved') {
            return redirect()->route('borrower.home');
        }

        /** @var \App\Models\Loan|null $activeLoan */
        $activeLoan = $user->borrower->loans()
            ->whereIn('status', ['active', 'overdue', 'approved'])
            ->with(['organization', 'scheduledRepayments', 'repayments'])
            ->latest()
            ->first();

        $this->activeLoan = $activeLoan;

        if ($this->activeLoan) {
            $this->referenceCode = $this->activeLoan->loan_number.'-'.strtoupper(substr(uniqid(), -4));
        } else {
            $this->hasPendingApplication = $user->borrower->loans()
                ->whereIn('status', ['applied', 'pending', 'verification_pending'])
                ->exists();
        }
    }

    public function openUploadModal()
    {
        $this->showUploadModal = true;
    }

    public function submitProof()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'receipt' => 'nullable|image|max:5120', // 5MB
        ]);

        $path = null;
        if ($this->receipt) {
            $path = $this->receipt->store('payment-proofs', 'public');
        }

        PaymentProof::create([
            'organization_id' => $this->activeLoan->organization_id,
            'loan_id' => $this->activeLoan->id,
            'borrower_id' => $this->activeLoan->borrower_id,
            'amount' => $this->amount,
            'payment_method' => 'Bank Transfer',
            'reference_code' => $this->referenceCode,
            'receipt_path' => $path,
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        $this->showUploadModal = false;
        $this->reset(['amount', 'receipt']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Payment proof submitted. Awaiting verification.']);
    }

    public function render()
    {
        $pendingProofs = PaymentProof::where('borrower_id', Auth::user()->borrower->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('livewire.borrower.repayment', [
            'pendingProofs' => $pendingProofs,
        ])->layout('layouts.borrower', ['title' => 'Repay Loan']);
    }
}
