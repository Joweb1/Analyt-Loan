<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SavingsEntry extends Component
{
    use WithPagination;

    public $search = '';

    public $portfolioId = null;

    public $portfolios = [];

    public $showSavingsModal = false;

    public $selectedBorrowerId = null;

    // Savings Form Fields
    public $amount;

    public $payment_method = 'Cash';

    public $transaction_date;

    public $notes = '';

    protected $updatesQueryString = ['search', 'portfolioId'];

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }

        $this->transaction_date = \App\Models\Organization::systemNow()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPortfolioId()
    {
        $this->resetPage();
    }

    public function selectBorrower($id)
    {
        $this->selectedBorrowerId = $id;
        $this->amount = null;
        $this->payment_method = 'Cash';
        $this->transaction_date = \App\Models\Organization::systemNow()->format('Y-m-d');
        $this->notes = '';
        $this->showSavingsModal = true;
    }

    public function addSavings()
    {
        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ];

        $this->validate($rules);

        $borrower = Borrower::findOrFail($this->selectedBorrowerId);

        // Ensure they have a savings account
        /** @var \App\Models\SavingsAccount $account */
        $account = $borrower->savingsAccount()->firstOrCreate([
            'organization_id' => $borrower->organization_id,
        ], [
            'account_number' => 'SAV-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'balance' => 0,
            'interest_rate' => 0,
            'status' => 'active',
        ]);

        // Create transaction
        $transaction = $account->transactions()->create([
            'amount' => $this->amount,
            'type' => 'deposit',
            'reference' => 'DEP-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'notes' => $this->notes.' ('.$this->payment_method.')',
            'staff_id' => Auth::id(),
            'transaction_date' => $this->transaction_date,
        ]);

        // Update balance
        $account->increment('balance', $this->amount);

        // Trigger Push Notification
        \App\Helpers\SystemLogger::success(
            'Savings Deposit',
            'Deposit of ₦'.number_format($this->amount, 2).' received from '.$borrower->user->name,
            'savings',
            $borrower
        );

        \App\Events\DashboardUpdated::dispatch($borrower->organization_id);
        \App\Livewire\Reports::clearCache($borrower->organization_id);

        $this->showSavingsModal = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Savings deposit added successfully.']);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $query = Borrower::with(['user', 'savingsAccount'])
            ->where('organization_id', $orgId);

        if (! empty($this->search)) {
            $search = strtolower(trim($this->search));
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('custom_id', 'like', '%'.$search.'%')
                    ->orWhere('bvn', 'like', '%'.$search.'%')
                    ->orWhere('national_identity_number', 'like', '%'.$search.'%');
            });
        }

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
        }

        $borrowers = $query->latest()->paginate(15);

        return view('livewire.savings-entry', [
            'borrowers' => $borrowers,
        ])->layout('layouts.app', ['title' => 'Savings Entry']);
    }
}
