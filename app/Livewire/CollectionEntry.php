<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionEntry extends Component
{
    use WithPagination;

    public $search = '';

    public $portfolioId = null;

    public $portfolios = [];

    public $showAllActive = true;

    // Repayment Modal Fields
    public $showRepaymentModal = false;

    public $selectedLoanId = null;

    public $amount;

    public $payment_method = 'Cash';

    public $paid_at;

    public $collected_by;

    protected $updatesQueryString = ['search', 'portfolioId'];

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }
        $this->paid_at = now()->format('Y-m-d');
        $this->collected_by = Auth::id();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPortfolioId()
    {
        $this->resetPage();
    }

    public function toggleFilter()
    {
        $this->showAllActive = ! $this->showAllActive;
        $this->resetPage();
    }

    public function selectLoan($id)
    {
        $this->selectedLoanId = $id;
        $loan = Loan::findOrFail($id);

        // Calculate Due Amount (Overdue + Today's Installment)
        $today = now()->toDateString();
        $dueAmountMinor = (int) $loan->scheduledRepayments()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<=', $today)
            ->get()
            ->sum(fn (\App\Models\ScheduledRepayment $s) => $s->principal_amount->getMinorAmount() + $s->interest_amount->getMinorAmount() + $s->penalty_amount->getMinorAmount() - $s->paid_amount->getMinorAmount());

        $this->amount = $dueAmountMinor / 100;
        $this->showRepaymentModal = true;
    }

    public function addRepayment()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_at' => 'nullable|date',
            'collected_by' => 'required|exists:users,id',
        ]);

        $paidAt = $this->paid_at ?: now();

        $loan = Loan::findOrFail($this->selectedLoanId);

        // Simple Split Logic (Priority: Interest -> Principal -> Extra)
        $currency = $loan->organization->currency_code ?? 'NGN';
        $amountMinor = (int) ($this->amount * 100);
        $remainingMinor = $amountMinor;

        $schedules = $loan->scheduledRepayments()->where('status', '!=', 'paid')->orderBy('due_date')->get();

        $interestPaidMinor = 0;
        $feePaidMinor = 0;
        $principalPaidMinor = 0;
        $extraPaidMinor = 0;

        foreach ($schedules as $s) {
            if ($remainingMinor <= 0) {
                break;
            }

            $sTotalDue = $s->principal_amount->getMinorAmount() + $s->interest_amount->getMinorAmount() + $s->penalty_amount->getMinorAmount();
            $sPaid = $s->paid_amount->getMinorAmount();
            $sRemaining = max(0, $sTotalDue - $sPaid);

            if ($sRemaining <= 0) {
                continue;
            }

            // 1. Interest
            $sInterestDue = max(0, $s->interest_amount->getMinorAmount() - min($sPaid, $s->interest_amount->getMinorAmount()));
            $toInterest = min($remainingMinor, $sInterestDue);
            $interestPaidMinor += $toInterest;
            $remainingMinor -= $toInterest;

            if ($remainingMinor <= 0) {
                break;
            }

            // 2. Fee (Penalty)
            $sFeeDue = max(0, $s->penalty_amount->getMinorAmount() - min(max(0, $sPaid - $s->interest_amount->getMinorAmount()), $s->penalty_amount->getMinorAmount()));
            $toFee = min($remainingMinor, $sFeeDue);
            $feePaidMinor += $toFee;
            $remainingMinor -= $toFee;

            if ($remainingMinor <= 0) {
                break;
            }

            // 3. Principal
            $sPrincipalDue = max(0, $s->principal_amount->getMinorAmount() - min(max(0, $sPaid - $s->interest_amount->getMinorAmount() - $s->penalty_amount->getMinorAmount()), $s->principal_amount->getMinorAmount()));
            $toPrincipal = min($remainingMinor, $sPrincipalDue);
            $principalPaidMinor += $toPrincipal;
            $remainingMinor -= $toPrincipal;
        }

        if ($remainingMinor > 0) {
            $extraPaidMinor = $remainingMinor;
        }

        $repayment = $loan->repayments()->create([
            'organization_id' => $loan->organization_id,
            'amount' => $this->amount, // Cast will handle float to Money
            'principal_amount' => new \App\ValueObjects\Money($principalPaidMinor, $currency),
            'interest_amount' => new \App\ValueObjects\Money($interestPaidMinor, $currency),
            'fee_amount' => new \App\ValueObjects\Money($feePaidMinor, $currency),
            'extra_amount' => new \App\ValueObjects\Money($extraPaidMinor, $currency),
            'payment_method' => $this->normalizePaymentMethod($this->payment_method),
            'paid_at' => $paidAt,
            'collected_by' => $this->collected_by,
            'notes' => 'Bulk collection entry',
        ]);

        // Trigger observers/sync
        $loan->refreshRepaymentStatus();

        // Recalculate Trust Score
        \App\Models\Borrower::find($loan->borrower_id)->update([
            'trust_score' => \App\Services\TrustScoringService::calculate($loan->borrower),
        ]);

        $this->showRepaymentModal = false;
        $this->reset(['amount', 'selectedLoanId']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Repayment recorded successfully.']);
    }

    private function normalizePaymentMethod($method): string
    {
        return match (strtolower(trim($method))) {
            'bank transfer', 'transfer' => 'bank_transfer',
            default => 'cash',
        };
    }

    public function render()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        $query = Loan::with(['borrower.user', 'scheduledRepayments', 'loanOfficer'])
            ->where('organization_id', $orgId);

        if ($this->showAllActive) {
            $query->whereIn('status', ['active', 'overdue']);
        } else {
            // Default: Show those with installments due today or overdue
            $query->whereHas('scheduledRepayments', function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->whereDate('due_date', '<=', now());
            });
        }

        if (! empty($this->search)) {
            $search = strtolower(trim($this->search));
            if (str_starts_with($search, 'staff:')) {
                $staffName = substr($search, 6);
                $query->whereHas('loanOfficer', function ($q) use ($staffName) {
                    $q->where('name', 'like', '%'.$staffName.'%');
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('loan_number', 'like', '%'.$search.'%')
                        ->orWhereHas('borrower.user', function ($uq) use ($search) {
                            $uq->where('name', 'like', '%'.$search.'%');
                        });
                });
            }
        }

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
        }

        $loans = $query->latest()->paginate(15);

        $staffs = User::where('organization_id', $orgId)
            ->whereIn('type', ['admin', 'staff'])
            ->get();

        return view('livewire.collection-entry', [
            'loans' => $loans,
            'staffs' => $staffs,
        ])->layout('layouts.app', ['title' => 'Collection Entry']);
    }
}
