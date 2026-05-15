<?php

namespace App\Livewire;

use App\Models\Portfolio;
use App\Models\User;
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

    public $selectedCustomerId = null;

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

        $this->transaction_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPortfolioId()
    {
        $this->resetPage();
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomerId = $id;
        $this->amount = null;
        $this->payment_method = 'Cash';
        $this->transaction_date = now()->format('Y-m-d');
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

        $customer = User::findOrFail($this->selectedCustomerId);

        // Ensure they have a savings account
        /** @var \App\Models\SavingsAccount $account */
        $account = $customer->savingsAccount()->firstOrCreate([
            'organization_id' => $customer->organization_id,
        ], [
            'account_number' => 'SAV-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'balance' => 0,
            'interest_rate' => 0,
            'status' => 'active',
        ]);

        // Normalize payment method for system logic (Cash -> cash, Bank Transfer -> bank_transfer)
        $normalizedMethod = $this->payment_method === 'Bank Transfer' ? 'bank_transfer' : 'cash';

        // Create transaction
        $transaction = $account->transactions()->create([
            'amount' => $this->amount,
            'type' => 'deposit',
            'reference' => 'DEP-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'notes' => $this->notes.' ('.$this->payment_method.')',
            'staff_id' => Auth::id(),
            'payment_method' => $normalizedMethod,
            'transaction_date' => $this->transaction_date,
        ]);

        // Update balance
        $amountMoney = \App\ValueObjects\Money::fromMajor($this->amount, $customer->organization->currency_code ?? 'NGN');
        /** @var \App\ValueObjects\Money $balance */
        $balance = $account->balance;
        $account->update(['balance' => $balance->add($amountMoney)]);

        // Trigger Notification
        \App\Helpers\SystemLogger::success(
            'Savings Deposit',
            'Deposit of ₦'.number_format($this->amount, 2).' received from '.$customer->name,
            'savings',
            $customer->borrower ?? $customer->saver ?? $customer
        );

        // Refresh Cashbook for this date
        $cashbookService = app(\App\Services\CashbookService::class);
        $entry = $cashbookService->getEntryForDate(\Illuminate\Support\Carbon::parse($this->transaction_date), $customer->organization);
        $cashbookService->fetchSystemData($entry);

        \App\Events\DashboardUpdated::dispatch($customer->organization_id);
        \App\Livewire\Reports::clearCache($customer->organization_id);

        $this->showSavingsModal = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Savings deposit added successfully.']);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $query = User::where('type', 'customer')
            ->where('organization_id', $orgId)
            ->where(function ($q) {
                $q->has('borrower')->orHas('saver');
            })
            ->with(['savingsAccount', 'borrower', 'saver']);

        if (! empty($this->search)) {
            $search = strtolower(trim($this->search));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('borrower', fn ($bq) => $bq->where('custom_id', 'like', '%'.$search.'%'));
            });
        }

        if ($this->portfolioId) {
            $query->where(function ($q) {
                $q->whereHas('borrower', fn ($bq) => $bq->where('portfolio_id', $this->portfolioId))
                    ->orWhereHas('saver', fn ($sq) => $sq->where('portfolio_id', $this->portfolioId))
                    ->orWhereHas('guarantor', fn ($gq) => $gq->where('portfolio_id', $this->portfolioId));
            });
        }

        $customers = $query->latest()->paginate(15);

        return view('livewire.savings-entry', [
            'customers' => $customers,
        ])->layout('layouts.app', ['title' => 'Savings Entry']);
    }
}
