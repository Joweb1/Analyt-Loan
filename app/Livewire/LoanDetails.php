<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanDetails extends Component
{
    public Loan $loan;

    public $staffs;

    public $pendingProofs = [];

    // Repayment Modal State
    public $showRepaymentsModal = false;

    public $showAddForm = false;

    public $editingRepaymentId = null;

    // Schedule Modal State
    public $showScheduleModal = false;

    public $editingScheduleId = null;

    public $schedulePrincipal;

    public $scheduleInterest;

    public $schedulePenalty;

    // Fees Modal State
    public $showFeesModal = false;

    public $feeProcessing;

    public $feeInsurance;

    public $feePenaltyValue;

    public $feePenaltyType = 'fixed';

    public $feePenaltyFrequency = 'one_time';

    public $overridePenalty = false;

    // Comments Modal State
    public $showCommentsModal = false;

    public $newComment = '';

    // Collateral Modal State
    public $showCollateralModal = false;

    // Delete Modal State
    public $showDeleteModal = false;

    // Repayment Form Defaults
    public $suggestedPrincipal = 0;

    public $suggestedInterest = 0;

    // Repayment Form Fields
    public $amount;

    public $payment_method = 'Cash';

    public $collected_by;

    public $paid_at;

    public $principal_amount = 0;

    public $interest_amount = 0;

    public $extra_amount = 0;

    public function mount(Loan $loan)
    {
        $this->loan = $loan->load(['borrower.user', 'repayments.collector', 'collateral', 'scheduledRepayments', 'comments.user']);

        $orgId = Auth::user()->organization_id;
        $this->staffs = User::where('organization_id', $orgId)
            ->role(['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist'])
            ->get();

        $this->paid_at = now()->format('Y-m-d');

        $this->calculateSuggestions();
        $this->loadPendingProofs();
    }

    public function loadPendingProofs()
    {
        $this->pendingProofs = \App\Models\PaymentProof::where('loan_id', $this->loan->id)
            ->where('status', 'applied')
            ->latest()
            ->get();
    }

    public function approveProof($id)
    {
        $proof = \App\Models\PaymentProof::findOrFail($id);

        if ($proof->status !== 'applied') {
            return;
        }

        // Distribution Logic (Match with PaymentVerifications logic)
        $amount = $proof->amount;
        $nextSchedule = $this->loan->scheduledRepayments()
            ->whereIn('status', ['applied', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->first();

        $interestPart = 0;
        $principalPart = 0;
        $extraPart = 0;

        if ($nextSchedule) {
            /** @var \App\Models\ScheduledRepayment $nextSchedule */
            $interestPart = min($amount, $nextSchedule->interest_amount);
            $remaining = $amount - $interestPart;
            $principalPart = min($remaining, $nextSchedule->principal_amount);
            $extraPart = $remaining - $principalPart;
        } else {
            $principalPart = $amount;
        }

        // Create Repayment
        $this->loan->repayments()->create([
            'amount' => $amount,
            'payment_method' => $proof->payment_method ?? 'Bank Transfer',
            'collected_by' => Auth::id(),
            'paid_at' => $proof->paid_at ?? now(),
            'principal_amount' => $principalPart,
            'interest_amount' => $interestPart,
            'extra_amount' => $extraPart,
        ]);

        $proof->update([
            'status' => 'approved',
            'admin_notes' => 'Approved by '.Auth::user()->name.' via Loan Details',
        ]);

        $this->loan->load('repayments');
        $this->loan->refreshRepaymentStatus();
        $this->loadPendingProofs();
        $this->calculateSuggestions();

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Payment proof approved and repayment recorded.']);
    }

    public function declineProof($id)
    {
        $proof = \App\Models\PaymentProof::findOrFail($id);
        $proof->update([
            'status' => 'rejected',
            'admin_notes' => 'Rejected by '.Auth::user()->name.' via Loan Details',
        ]);

        $this->loadPendingProofs();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Payment proof declined.']);
    }

    private function calculateSuggestions()
    {
        // Find the earliest schedule that isn't fully paid
        $nextSchedule = $this->loan->scheduledRepayments->sortBy('due_date')->first(function ($schedule) {
            return in_array($schedule->status, ['applied', 'overdue', 'partial']);
        });

        if ($nextSchedule) {
            $this->suggestedPrincipal = $nextSchedule->principal_amount;
            // Calculate remaining interest + penalty needed
            $totalDueForSchedule = $nextSchedule->principal_amount + $nextSchedule->interest_amount + $nextSchedule->penalty_amount;
            $remainingDue = max(0, $totalDueForSchedule - $nextSchedule->paid_amount);

            // We attribute the 'suggested interest' to be the remaining amount needed after principal
            // This is a simplification for the UI suggestion
            $this->suggestedInterest = max(0, $remainingDue - $this->suggestedPrincipal);
        } else {
            $numRepayments = max(1, $this->loan->num_repayments ?? 1);
            $this->suggestedPrincipal = $this->loan->amount / $numRepayments;

            $totalInterestNaira = $this->loan->amount * (($this->loan->interest_rate ?? 0) / 100);
            $this->suggestedInterest = $totalInterestNaira / $numRepayments;
        }

        if (! $this->editingRepaymentId) {
            $this->principal_amount = round($this->suggestedPrincipal, 2);
            $this->interest_amount = round($this->suggestedInterest, 2);
            $this->amount = $this->principal_amount + $this->interest_amount;
        }
    }

    public function openScheduleModal()
    {
        if ($this->loan->scheduledRepayments->isEmpty()) {
            $this->generateSchedule();
        }
        $this->loan->refreshRepaymentStatus();
        $this->showScheduleModal = true;
    }

    public function generateSchedule()
    {
        $numRepayments = max(1, $this->loan->num_repayments ?? 1);
        $principalShare = $this->loan->amount / $numRepayments;
        $totalInterest = $this->loan->amount * (($this->loan->interest_rate ?? 0) / 100);
        $interestShare = $totalInterest / $numRepayments;

        $startDate = Carbon::parse($this->loan->release_date ?? now());
        $cycle = $this->loan->repayment_cycle ?? 'monthly';

        for ($i = 1; $i <= $numRepayments; $i++) {
            $dueDate = $startDate->copy();

            match ($cycle) {
                'daily' => $dueDate->addDays($i),
                'weekly' => $dueDate->addWeeks($i),
                'biweekly' => $dueDate->addWeeks($i * 2),
                'monthly' => $dueDate->addMonths($i),
                'yearly' => $dueDate->addYears($i),
                default => $dueDate->addMonths($i),
            };

            ScheduledRepayment::create([
                'loan_id' => $this->loan->id,
                'due_date' => $dueDate,
                'principal_amount' => $principalShare,
                'interest_amount' => $interestShare,
                'penalty_amount' => 0,
                'installment_number' => $i,
                'status' => 'applied',
            ]);
        }

        $this->loan->load('scheduledRepayments');
        $this->loan->refreshRepaymentStatus();
    }

    public function editSchedule($id)
    {
        $schedule = ScheduledRepayment::find($id);
        $this->editingScheduleId = $id;
        $this->schedulePrincipal = $schedule->principal_amount;
        $this->scheduleInterest = $schedule->interest_amount;
        $this->schedulePenalty = $schedule->penalty_amount;
    }

    public function cancelEditSchedule()
    {
        $this->editingScheduleId = null;
        $this->schedulePrincipal = null;
        $this->scheduleInterest = null;
        $this->schedulePenalty = null;
    }

    public function saveSchedule()
    {
        $this->validate([
            'schedulePrincipal' => 'required|numeric|min:0',
            'scheduleInterest' => 'required|numeric|min:0',
            'schedulePenalty' => 'required|numeric|min:0',
        ]);

        ScheduledRepayment::where('id', $this->editingScheduleId)->update([
            'principal_amount' => $this->schedulePrincipal,
            'interest_amount' => $this->scheduleInterest,
            'penalty_amount' => $this->schedulePenalty,
        ]);

        $this->cancelEditSchedule();
        $this->loan->load('scheduledRepayments');
        $this->loan->refreshRepaymentStatus();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Schedule updated successfully.']);
    }

    public function openFeesModal()
    {
        $this->feeProcessing = $this->loan->processing_fee;
        $this->feeInsurance = $this->loan->insurance_fee;
        $this->feePenaltyValue = $this->loan->penalty_value;
        $this->feePenaltyType = $this->loan->penalty_type ?? 'fixed';
        $this->feePenaltyFrequency = $this->loan->penalty_frequency ?? 'one_time';
        $this->overridePenalty = $this->loan->override_system_penalty;
        $this->showFeesModal = true;
    }

    public function saveFees()
    {
        $this->validate([
            'feeProcessing' => 'nullable|numeric|min:0',
            'feeInsurance' => 'nullable|numeric|min:0',
            'feePenaltyValue' => 'nullable|numeric|min:0',
            'feePenaltyType' => 'required|in:fixed,percentage',
            'feePenaltyFrequency' => 'required|in:one_time,daily,weekly,monthly,yearly',
            'overridePenalty' => 'boolean',
        ]);

        $this->loan->update([
            'processing_fee' => $this->feeProcessing,
            'insurance_fee' => $this->feeInsurance,
            'penalty_value' => $this->feePenaltyValue,
            'penalty_type' => $this->feePenaltyType,
            'penalty_frequency' => $this->feePenaltyFrequency,
            'override_system_penalty' => $this->overridePenalty,
        ]);

        $this->showFeesModal = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Loan fees configuration updated.']);
    }

    public function openCommentsModal()
    {
        $this->showCommentsModal = true;
    }

    public function postComment()
    {
        $this->validate([
            'newComment' => 'required|string|max:1000',
        ]);

        $this->loan->comments()->create([
            'user_id' => Auth::id(),
            'body' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->loan->load('comments.user');
        // $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Comment posted.']);
    }

    public function openCollateralModal()
    {
        $this->showCollateralModal = true;
    }

    public function goToAddCollateral()
    {
        return redirect()->route('collateral.create', ['loan_id' => $this->loan->id]);
    }

    public function deleteCollateral()
    {
        if ($this->loan->collateral) {
            $this->loan->collateral->delete();
            $this->loan->load('collateral');
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Collateral removed.']);
            $this->showCollateralModal = false;
        }
    }

    public function toggleAddForm()
    {
        $this->showAddForm = ! $this->showAddForm;
        if (! $this->showAddForm) {
            $this->editingRepaymentId = null;
            $this->resetRepaymentForm();
        }
    }

    public function openRepaymentsModal()
    {
        $this->showRepaymentsModal = true;
    }

    public function addRepayment()
    {
        $allowFlexible = Auth::user()->organization->allow_flexible_repayments ?? false;
        $minRequired = $this->suggestedPrincipal + $this->suggestedInterest;

        $rules = [
            'amount' => 'required|numeric|min:'.($allowFlexible ? 1 : $minRequired),
            'payment_method' => 'required|string',
            'collected_by' => 'required|exists:users,id',
            'paid_at' => 'required|date',
        ];

        $messages = [
            'amount.min' => $allowFlexible
                ? 'The amount must be at least ₦1.00.'
                : 'The amount must cover at least the principal and interest (₦'.number_format($minRequired, 2).').',
        ];

        $this->validate($rules, $messages);

        if ($allowFlexible) {
            // Distribute flexible amount: prioritize interest, then principal, then extra
            $remaining = $this->amount;

            // Interest share
            $intPart = min($remaining, $this->suggestedInterest);
            $this->interest_amount = $intPart;
            $remaining -= $intPart;

            // Principal share
            $priPart = min($remaining, $this->suggestedPrincipal);
            $this->principal_amount = $priPart;
            $remaining -= $priPart;

            // Extra
            $this->extra_amount = $remaining;
        } else {
            $this->extra_amount = $this->amount - $minRequired;
            $this->principal_amount = $this->suggestedPrincipal;
            $this->interest_amount = $this->suggestedInterest;
        }

        $this->loan->repayments()->create([
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'collected_by' => $this->collected_by,
            'paid_at' => $this->paid_at,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'extra_amount' => $this->extra_amount,
        ]);

        $this->loan->load(['repayments.collector', 'scheduledRepayments']);
        $this->calculateSuggestions();
        $this->resetRepaymentForm();
        $this->showAddForm = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Repayment added successfully.']);
    }

    public function editRepayment($id)
    {
        $this->editingRepaymentId = $id;
        $this->showAddForm = true;
        $repayment = Repayment::find($id);
        $this->amount = $repayment->amount;
        $this->payment_method = $repayment->payment_method;
        $this->collected_by = $repayment->collected_by;
        $this->paid_at = $repayment->paid_at->format('Y-m-d');
        $this->principal_amount = $repayment->principal_amount;
        $this->interest_amount = $repayment->interest_amount;
        $this->extra_amount = $repayment->extra_amount;
    }

    public function saveRepayment()
    {
        $allowFlexible = Auth::user()->organization->allow_flexible_repayments ?? false;
        $minRequired = $this->suggestedPrincipal + $this->suggestedInterest;

        $rules = [
            'amount' => 'required|numeric|min:'.($allowFlexible ? 1 : $minRequired),
            'payment_method' => 'required|string',
            'collected_by' => 'required|exists:users,id',
            'paid_at' => 'required|date',
        ];

        $this->validate($rules);

        $repayment = Repayment::find($this->editingRepaymentId);

        if ($allowFlexible) {
            $remaining = $this->amount;
            $intPart = min($remaining, $this->suggestedInterest);
            $this->interest_amount = $intPart;
            $remaining -= $intPart;
            $priPart = min($remaining, $this->suggestedPrincipal);
            $this->principal_amount = $priPart;
            $remaining -= $priPart;
            $this->extra_amount = $remaining;
        } else {
            $this->extra_amount = $this->amount - $minRequired;
            $this->principal_amount = $this->suggestedPrincipal;
            $this->interest_amount = $this->suggestedInterest;
        }

        $repayment->update([
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'collected_by' => $this->collected_by,
            'paid_at' => $this->paid_at,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'extra_amount' => $this->extra_amount,
        ]);

        $this->editingRepaymentId = null;
        $this->showAddForm = false;
        $this->loan->load(['repayments.collector', 'scheduledRepayments']);
        $this->calculateSuggestions();
        $this->resetRepaymentForm();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Repayment updated successfully.']);
    }

    public function deleteRepayment($id)
    {
        Repayment::destroy($id);
        $this->loan->load(['repayments.collector', 'scheduledRepayments']);
        $this->calculateSuggestions();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Repayment deleted.']);
    }

    private function resetRepaymentForm()
    {
        $this->amount = $this->suggestedPrincipal + $this->suggestedInterest;
        $this->payment_method = 'Cash';
        $this->collected_by = null;
        $this->paid_at = now()->format('Y-m-d');
        $this->principal_amount = $this->suggestedPrincipal;
        $this->interest_amount = $this->suggestedInterest;
        $this->extra_amount = 0;
    }

    public function approveLoan()
    {
        $this->loan->update(['status' => 'approved']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Loan approved successfully.']);
    }

    public function activateLoan()
    {
        try {
            $loanService = app(\App\Services\LoanService::class);
            $loanService->activateLoan($this->loan);
            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Loan activated and funds disbursed.']);
        } catch (\App\Exceptions\CollateralInsufficientException $e) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Failed to activate loan.']);
        }
    }

    public function declineLoan()
    {
        $this->loan->update(['status' => 'declined']);
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Loan has been declined.']);
    }

    public function deleteLoan()
    {
        $this->loan->delete();
        session()->flash('custom-alert', ['type' => 'warning', 'message' => 'Loan record deleted permanently.']);

        return redirect()->route('loan');
    }

    public function render()
    {
        return view('livewire.loan-details')->layout('layouts.app', ['title' => 'Loan Details #'.$this->loan->loan_number]);
    }
}
