<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionEntry extends Component
{
    use WithPagination;

    public $search = '';

    public $showRepaymentModal = false;

    public $selectedLoanId = null;

    // Repayment Form Fields
    public $amount;

    public $payment_method = 'Cash';

    public $collected_by;

    public $paid_at;

    public $principal_amount = 0;

    public $interest_amount = 0;

    public $extra_amount = 0;

    public $suggestedPrincipal = 0;

    public $suggestedInterest = 0;

    public $staffs;

    public $showAllActive = false;

    public function mount()
    {
        $orgId = Auth::user()->organization_id;
        $this->staffs = User::where('organization_id', $orgId)
            ->role(['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist', 'Collection Officer'])
            ->get();
        $this->paid_at = now()->format('Y-m-d');
        $this->collected_by = Auth::id();
    }

    public function toggleFilter()
    {
        $this->showAllActive = ! $this->showAllActive;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectLoan($id)
    {
        $this->selectedLoanId = $id;
        $loan = Loan::with('scheduledRepayments')->findOrFail($id);

        $this->calculateSuggestions($loan);

        $this->amount = null; // Should be empty initially
        $this->payment_method = 'Cash';
        $this->collected_by = Auth::id();
        $this->paid_at = now()->format('Y-m-d');

        $this->showRepaymentModal = true;
    }

    private function calculateSuggestions(Loan $loan)
    {
        $nextSchedule = $loan->scheduledRepayments->sortBy('due_date')->first(function ($schedule) {
            return in_array($schedule->status, ['applied', 'overdue', 'partial']);
        });

        if ($nextSchedule) {
            $this->suggestedPrincipal = $nextSchedule->principal_amount;
            $totalDueForSchedule = $nextSchedule->principal_amount + $nextSchedule->interest_amount + $nextSchedule->penalty_amount;
            $remainingDue = max(0, $totalDueForSchedule - $nextSchedule->paid_amount);
            $this->suggestedInterest = max(0, $remainingDue - $this->suggestedPrincipal);
        } else {
            $numRepayments = max(1, $loan->num_repayments ?? 1);
            $this->suggestedPrincipal = $loan->amount / $numRepayments;
            $totalInterestNaira = $loan->amount * (($loan->interest_rate ?? 0) / 100);
            $this->suggestedInterest = $totalInterestNaira / $numRepayments;
        }

        $this->principal_amount = round($this->suggestedPrincipal, 2);
        $this->interest_amount = round($this->suggestedInterest, 2);
    }

    public function addRepayment()
    {
        $loan = Loan::findOrFail($this->selectedLoanId);
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

        $repayment = $loan->repayments()->create([
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'collected_by' => $this->collected_by,
            'paid_at' => $this->paid_at,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'extra_amount' => $this->extra_amount,
        ]);

        $loan->refreshRepaymentStatus();

        // Trigger Push Notification
        \App\Helpers\SystemLogger::success(
            'Repayment Added',
            'Repayment of ₦'.number_format($this->amount, 2).' added to Loan #'.$loan->loan_number,
            'collection',
            $loan
        );

        $this->showRepaymentModal = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Repayment added successfully.']);

        return redirect()->route('repayments.records');
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $query = Loan::with(['borrower.user', 'loanOfficer'])
            ->where('organization_id', $orgId);

        if (! empty($this->search)) {
            $search = strtolower(trim($this->search));
            $prefix = null;

            if (str_contains($search, ':')) {
                $parts = explode(':', $search, 2);
                $prefix = trim($parts[0]);
                $search = trim($parts[1]);
            }

            $query->where(function ($q) use ($search, $prefix) {
                if ($prefix === 'staff') {
                    $q->whereHas('loanOfficer', function ($lq) use ($search) {
                        $lq->where('name', 'like', '%'.$search.'%');
                    });
                } else {
                    $q->where('loan_number', 'like', '%'.$search.'%')
                        ->orWhereHas('borrower', function ($bq) use ($search) {
                            $bq->where('phone', 'like', '%'.$search.'%')
                                ->orWhere('bvn', 'like', '%'.$search.'%')
                                ->orWhere('national_identity_number', 'like', '%'.$search.'%')
                                ->orWhere('custom_id', 'like', '%'.$search.'%')
                                ->orWhereHas('user', function ($uq) use ($search) {
                                    $uq->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('email', 'like', '%'.$search.'%');
                                });
                        });
                }
            });
            $query->whereIn('status', ['approved', 'active', 'overdue']);
        } else {
            if ($this->showAllActive) {
                $query->whereIn('status', ['approved', 'active', 'overdue']);
            } else {
                // Default view: Overdue loans
                $query->where('status', 'overdue');
            }
        }

        $loans = $query->latest()->paginate(15);

        return view('livewire.collection-entry', [
            'loans' => $loans,
        ])->layout('layouts.app', ['title' => 'Collection Entry']);
    }
}
