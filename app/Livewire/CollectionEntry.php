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

    public $showAllActive = false;

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
        $this->paid_at = \App\Models\Organization::systemNow()->format('Y-m-d');
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
        $this->amount = $loan->balance; // Default to full balance
        $this->showRepaymentModal = true;
    }

    public function addRepayment()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_at' => 'required|date',
            'collected_by' => 'required|exists:users,id',
        ]);

        $loan = Loan::findOrFail($this->selectedLoanId);

        // Simple Split Logic (Priority: Interest -> Principal -> Extra)
        $totalInterest = (float) $loan->amount * (($loan->interest_rate ?? 0) / 100);
        $interestPaid = (float) $loan->repayments()->sum('interest_amount');
        $interestDue = max(0, $totalInterest - $interestPaid);

        $principalPaid = (float) $loan->repayments()->sum('principal_amount');
        $principalDue = max(0, (float) $loan->amount - $principalPaid);

        $remainingAmount = (float) $this->amount;
        $interestToPay = min($remainingAmount, $interestDue);
        $remainingAmount -= $interestToPay;

        $principalToPay = min($remainingAmount, $principalDue);
        $remainingAmount -= $principalToPay;

        $extraAmount = $remainingAmount;

        $repayment = $loan->repayments()->create([
            'organization_id' => $loan->organization_id,
            'amount' => $this->amount,
            'principal_amount' => $principalToPay,
            'interest_amount' => $interestToPay,
            'extra_amount' => $extraAmount,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
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
                    ->whereDate('due_date', '<=', \App\Models\Organization::systemNow());
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
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['Borrower']);
            })->get();

        return view('livewire.collection-entry', [
            'loans' => $loans,
            'staffs' => $staffs,
        ])->layout('layouts.app', ['title' => 'Collection Entry']);
    }
}
