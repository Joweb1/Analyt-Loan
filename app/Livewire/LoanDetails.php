<?php

namespace App\Livewire;

use App\Exceptions\CollateralInsufficientException;
use App\Models\Loan;
use App\Models\PaymentProof;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use App\Services\LoanService;
use App\ValueObjects\Money;
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

    public $payment_method = 'Bank Transfer';

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
            ->whereIn('type', ['admin', 'staff'])
            ->get();

        $this->paid_at = now()->format('Y-m-d');

        $this->calculateSuggestions();
        $this->loadPendingProofs();
    }

    public function loadPendingProofs()
    {
        $this->pendingProofs = PaymentProof::where('loan_id', $this->loan->id)
            ->where('status', 'applied')
            ->latest()
            ->get();
    }

    public function approveProof($id)
    {
        $proof = PaymentProof::findOrFail($id);

        if ($proof->status !== 'applied') {
            return;
        }

        // Distribution Logic (Match with PaymentVerifications logic)
        /** @var Money $amount */
        $amount = $proof->amount;
        $currency = $amount->getCurrency();

        $nextSchedule = $this->loan->scheduledRepayments()
            ->whereIn('status', ['applied', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->first();

        /** @var Money $interestPart */
        $interestPart = new Money(0, $currency);
        /** @var Money $principalPart */
        $principalPart = new Money(0, $currency);
        /** @var Money $extraPart */
        $extraPart = new Money(0, $currency);

        if ($nextSchedule) {
            /** @var ScheduledRepayment $nextSchedule */
            $interestPart = new Money(min($amount->getMinorAmount(), $nextSchedule->interest_amount->getMinorAmount()), $currency);
            $remaining = $amount->subtract($interestPart);

            $feePart = new Money(min($remaining->getMinorAmount(), $nextSchedule->penalty_amount->getMinorAmount()), $currency);
            $remaining = $remaining->subtract($feePart);

            $principalPart = new Money(min($remaining->getMinorAmount(), $nextSchedule->principal_amount->getMinorAmount()), $currency);
            $extraPart = $remaining->subtract($principalPart);
        } else {
            $principalPart = $amount;
            $feePart = new Money(0, $currency);
        }

        // Create Repayment
        $this->loan->repayments()->create([
            'amount' => $amount,
            'payment_method' => $this->normalizePaymentMethod($proof->payment_method ?? 'Bank Transfer'),
            'collected_by' => Auth::id(),
            'paid_at' => $proof->paid_at ?? now(),
            'principal_amount' => $principalPart,
            'interest_amount' => $interestPart,
            'fee_amount' => $feePart,
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
        $proof = PaymentProof::findOrFail($id);
        $proof->update([
            'status' => 'rejected',
            'admin_notes' => 'Rejected by '.Auth::user()->name.' via Loan Details',
        ]);

        $this->loadPendingProofs();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Payment proof declined.']);
    }

    private function calculateSuggestions()
    {
        $currency = $this->loan->amount->getCurrency();

        // Find the earliest schedule that isn't fully paid
        $nextSchedule = $this->loan->scheduledRepayments->sortBy('due_date')->first(function ($schedule) {
            return in_array($schedule->status, ['applied', 'overdue', 'partial']);
        });

        if ($nextSchedule) {
            $this->suggestedPrincipal = $nextSchedule->principal_amount->getMajorAmount();
            // Calculate remaining interest + penalty needed
            $totalDueForSchedule = $nextSchedule->principal_amount->add($nextSchedule->interest_amount)->add($nextSchedule->penalty_amount);
            $remainingDue = new Money(max(0, $totalDueForSchedule->getMinorAmount() - $nextSchedule->paid_amount->getMinorAmount()), $currency);

            // We attribute the 'suggested interest' to be the remaining amount needed after principal
            // This is a simplification for the UI suggestion
            $this->suggestedInterest = max(0, $remainingDue->getMajorAmount() - $this->suggestedPrincipal);
        } else {
            $numRepayments = max(1, $this->loan->num_repayments ?? 1);
            $this->suggestedPrincipal = $this->loan->amount->divide($numRepayments)->getMajorAmount();

            $totalInterest = $this->loan->getTotalExpectedInterest();
            $this->suggestedInterest = $totalInterest->divide($numRepayments)->getMajorAmount();
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
        app(LoanService::class)->generateRepaymentSchedule($this->loan);
        $this->loan->load('scheduledRepayments');
    }

    public function editSchedule($id)
    {
        $schedule = ScheduledRepayment::find($id);
        $this->editingScheduleId = $id;
        $this->schedulePrincipal = $schedule->principal_amount->getMajorAmount();
        $this->scheduleInterest = $schedule->interest_amount->getMajorAmount();
        $this->schedulePenalty = $schedule->penalty_amount->getMajorAmount();
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
        $this->feeProcessing = $this->loan->processing_fee ? $this->loan->processing_fee->getMajorAmount() : 0;
        $this->feeInsurance = $this->loan->insurance_fee ? $this->loan->insurance_fee->getMajorAmount() : 0;
        $this->feePenaltyValue = $this->loan->penalty_value->getMajorAmount();
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
        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin') || $user->type === 'owner';

        // Period Locking Check
        $now = now();
        $hasRepaymentThisPeriod = false;
        if ($this->loan->repayment_cycle === 'monthly') {
            $hasRepaymentThisPeriod = $this->loan->repayments()
                ->whereMonth('paid_at', $now->month)
                ->whereYear('paid_at', $now->year)
                ->exists();
        } else {
            $hasRepaymentThisPeriod = $this->loan->repayments()
                ->whereBetween('paid_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->exists();
        }

        if ($hasRepaymentThisPeriod && ! $isAdmin) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'The current period is locked. Only administrators can add multiple repayments per period.']);

            return;
        }

        $allowFlexible = Auth::user()->organization->allow_flexible_repayments ?? false;
        $minRequired = $this->suggestedPrincipal + $this->suggestedInterest;

        $rules = [
            'amount' => 'required|numeric|min:'.($allowFlexible ? 1 : $minRequired),
            'payment_method' => 'required|in:Cash,Bank Transfer',
            'collected_by' => 'required|exists:users,id',
            'paid_at' => 'nullable|date',
        ];

        $messages = [
            'amount.min' => $allowFlexible
                ? 'The amount must be at least ₦1.00.'
                : 'The amount must cover at least the principal and interest (₦'.number_format($minRequired, 2).').',
        ];

        $this->validate($rules, $messages);

        $paidAt = $this->paid_at ?: now();

        $currency = $this->loan->amount->getCurrency();
        $amountMoney = Money::fromMajor($this->amount, $currency);

        if ($allowFlexible) {
            // Distribute flexible amount: prioritize interest, then principal, then extra
            $remaining = $amountMoney;

            // Interest share
            $suggestedIntMoney = Money::fromMajor($this->suggestedInterest, $currency);
            $intPart = new Money(min($remaining->getMinorAmount(), $suggestedIntMoney->getMinorAmount()), $currency);
            $this->interest_amount = $intPart->getMajorAmount();
            $remaining = $remaining->subtract($intPart);

            // Principal share
            $suggestedPriMoney = Money::fromMajor($this->suggestedPrincipal, $currency);
            $priPart = new Money(min($remaining->getMinorAmount(), $suggestedPriMoney->getMinorAmount()), $currency);
            $this->principal_amount = $priPart->getMajorAmount();
            $remaining = $remaining->subtract($priPart);

            // Extra
            $this->extra_amount = $remaining->getMajorAmount();
        } else {
            $this->extra_amount = $this->amount - $minRequired;
            $this->principal_amount = $this->suggestedPrincipal;
            $this->interest_amount = $this->suggestedInterest;
        }

        $this->loan->repayments()->create([
            'amount' => $this->amount,
            'payment_method' => $this->normalizePaymentMethod($this->payment_method),
            'collected_by' => $this->collected_by,
            'paid_at' => $paidAt,
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
        $repayment = Repayment::find($id);
        if ($repayment->isLocked()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'This repayment is locked. Only administrators can edit verified period entries.']);

            return;
        }

        $this->editingRepaymentId = $id;
        $this->showAddForm = true;
        $this->amount = $repayment->amount->getMajorAmount();
        $this->payment_method = $repayment->payment_method;
        $this->collected_by = $repayment->collected_by;
        $this->paid_at = $repayment->paid_at->format('Y-m-d');
        $this->principal_amount = $repayment->principal_amount->getMajorAmount();
        $this->interest_amount = $repayment->interest_amount->getMajorAmount();
        $this->extra_amount = $repayment->extra_amount->getMajorAmount();
    }

    public function saveRepayment()
    {
        $allowFlexible = Auth::user()->organization->allow_flexible_repayments ?? false;
        $minRequired = $this->suggestedPrincipal + $this->suggestedInterest;

        $rules = [
            'amount' => 'required|numeric|min:'.($allowFlexible ? 1 : $minRequired),
            'payment_method' => 'required|in:Cash,Bank Transfer',
            'collected_by' => 'required|exists:users,id',
            'paid_at' => 'nullable|date',
        ];

        $this->validate($rules);

        $paidAt = $this->paid_at ?: now();

        $repayment = Repayment::find($this->editingRepaymentId);
        $currency = $this->loan->amount->getCurrency();
        $amountMoney = Money::fromMajor($this->amount, $currency);

        if ($allowFlexible) {
            $remaining = $amountMoney;
            $suggestedIntMoney = Money::fromMajor($this->suggestedInterest, $currency);
            $intPart = new Money(min($remaining->getMinorAmount(), $suggestedIntMoney->getMinorAmount()), $currency);
            $this->interest_amount = $intPart->getMajorAmount();
            $remaining = $remaining->subtract($intPart);

            $suggestedPriMoney = Money::fromMajor($this->suggestedPrincipal, $currency);
            $priPart = new Money(min($remaining->getMinorAmount(), $suggestedPriMoney->getMinorAmount()), $currency);
            $this->principal_amount = $priPart->getMajorAmount();
            $remaining = $remaining->subtract($priPart);

            $this->extra_amount = $remaining->getMajorAmount();
        } else {
            $this->extra_amount = $this->amount - $minRequired;
            $this->principal_amount = $this->suggestedPrincipal;
            $this->interest_amount = $this->suggestedInterest;
        }

        $repayment->update([
            'amount' => $this->amount,
            'payment_method' => $this->normalizePaymentMethod($this->payment_method),
            'collected_by' => $this->collected_by,
            'paid_at' => $paidAt,
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
        $repayment = Repayment::find($id);
        if ($repayment->isLocked()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'This repayment is locked. Only administrators can delete verified period entries.']);

            return;
        }

        Repayment::destroy($id);
        $this->loan->load(['repayments.collector', 'scheduledRepayments']);
        $this->calculateSuggestions();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Repayment deleted.']);
    }

    private function normalizePaymentMethod($method): string
    {
        return match (strtolower(trim($method))) {
            'bank transfer', 'transfer' => 'bank_transfer',
            default => 'cash',
        };
    }

    private function resetRepaymentForm()
    {
        $this->amount = $this->suggestedPrincipal + $this->suggestedInterest;
        $this->payment_method = 'Bank Transfer';
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
            $loanService = app(LoanService::class);
            $loanService->activateLoan($this->loan);
            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Loan activated and funds disbursed.']);
        } catch (CollateralInsufficientException $e) {
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
