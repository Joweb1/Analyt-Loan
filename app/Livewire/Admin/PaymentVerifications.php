<?php

namespace App\Livewire\Admin;

use App\Models\PaymentProof;
use App\Models\Repayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentVerifications extends Component
{
    use WithPagination;

    public function approve($id)
    {
        $proof = PaymentProof::findOrFail($id);

        if ($proof->status !== 'applied') {
            return;
        }

        $loan = $proof->loan;
        $amount = $proof->amount;
        $currency = $amount->getCurrency();

        $nextSchedule = $loan->scheduledRepayments()
            ->whereIn('status', ['applied', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->first();

        if ($nextSchedule) {
            /** @var \App\Models\ScheduledRepayment $nextSchedule */
            $sugInterest = $nextSchedule->interest_amount;
            $sugFee = $nextSchedule->penalty_amount;
            $sugPrincipal = $nextSchedule->principal_amount;

            $remaining = $amount;

            // Prioritize Interest
            $interestPart = new \App\ValueObjects\Money(min($remaining->getMinorAmount(), $sugInterest->getMinorAmount()), $amount->getCurrency());
            $remaining = $remaining->subtract($interestPart);

            // Then Fee
            $feePart = new \App\ValueObjects\Money(min($remaining->getMinorAmount(), $sugFee->getMinorAmount()), $amount->getCurrency());
            $remaining = $remaining->subtract($feePart);

            // Then Principal
            $principalPart = new \App\ValueObjects\Money(min($remaining->getMinorAmount(), $sugPrincipal->getMinorAmount()), $amount->getCurrency());
            $remaining = $remaining->subtract($principalPart);

            // Rest is Extra
            $extraPart = $remaining;
        } else {
            // No schedule? Just treat as principal
            $principalPart = $amount;
            $interestPart = new \App\ValueObjects\Money(0, $currency);
            $feePart = new \App\ValueObjects\Money(0, $currency);
            $extraPart = new \App\ValueObjects\Money(0, $currency);
        }

        // Create Repayment
        $loan->repayments()->create([
            'amount' => $amount,
            'payment_method' => 'Bank Transfer',
            'collected_by' => Auth::id(),
            'paid_at' => $proof->paid_at ?? now(),
            'principal_amount' => $principalPart,
            'interest_amount' => $interestPart,
            'fee_amount' => $feePart,
            'extra_amount' => $extraPart,
        ]);

        $proof->update([
            'status' => 'approved',
            'admin_notes' => 'Approved by '.Auth::user()->name,
        ]);

        $loan->refreshRepaymentStatus();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Payment approved and recorded.']);
    }

    public function reject($id)
    {
        $proof = PaymentProof::findOrFail($id);
        $proof->update(['status' => 'rejected', 'admin_notes' => 'Rejected by '.Auth::user()->name]);
        $this->dispatch('custom-alert', ['type' => 'info', 'message' => 'Payment proof rejected.']);
    }

    public function render()
    {
        $proofs = PaymentProof::where('organization_id', Auth::user()->organization_id)
            ->where('status', 'applied')
            ->with(['borrower.user', 'loan'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.payment-verifications', ['proofs' => $proofs])
            ->layout('layouts.app', ['title' => 'Payment Verifications']);
    }
}
