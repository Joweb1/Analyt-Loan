<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SavingsDetails extends Component
{
    use WithPagination;

    public Borrower $borrower;

    public $savingsAccount;

    public $staffs;

    // Transaction Modal State
    public $showTransactionModal = false;

    public $transactionType = 'deposit'; // deposit or withdrawal

    public $amount;

    public $notes;

    public $reference;

    public $transactionDate;

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower->load(['user', 'organization']);

        $orgId = Auth::user()->organization_id;
        $this->staffs = User::where('organization_id', $orgId)
            ->role(['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist'])
            ->get();

        $this->savingsAccount = SavingsAccount::firstOrCreate(
            ['borrower_id' => $this->borrower->id],
            [
                'organization_id' => $this->borrower->organization_id,
                'account_number' => 'SAV-'.strtoupper(Str::random(8)),
                'balance' => 0,
                'interest_rate' => 0,
                'status' => 'active',
            ]
        );

        $this->transactionDate = \App\Models\Organization::systemNow()->format('Y-m-d');
    }

    public function openTransactionModal($type)
    {
        $this->transactionType = $type;
        $this->resetValidation();
        $this->amount = null;
        $this->notes = null;
        $this->reference = 'REF-'.strtoupper(Str::random(10));
        $this->showTransactionModal = true;
    }

    public function submitTransaction()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'transactionDate' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:50',
        ]);

        $amountMoney = \App\ValueObjects\Money::fromMajor($this->amount, $this->borrower->organization->currency_code ?? 'NGN');

        if ($this->transactionType === 'withdrawal' && $this->savingsAccount->balance->getMinorAmount() < $amountMoney->getMinorAmount()) {
            $this->addError('amount', 'Insufficient balance for this withdrawal.');

            return;
        }

        DB::transaction(function () use ($amountMoney) {
            // Create transaction record
            SavingsTransaction::create([
                'savings_account_id' => $this->savingsAccount->id,
                'amount' => $amountMoney,
                'type' => $this->transactionType,
                'reference' => $this->reference,
                'notes' => $this->notes,
                'staff_id' => Auth::id(),
                'transaction_date' => $this->transactionDate,
            ]);

            // Update account balance
            if ($this->transactionType === 'deposit') {
                $this->savingsAccount->balance = $this->savingsAccount->balance->add($amountMoney);
            } else {
                $this->savingsAccount->balance = $this->savingsAccount->balance->subtract($amountMoney);
            }
            $this->savingsAccount->save();

            // Create notification for the borrower (if they have a user account)
            if ($this->borrower->user_id) {
                SystemNotification::create([
                    'organization_id' => $this->borrower->organization_id,
                    'user_id' => Auth::id(),
                    'recipient_id' => $this->borrower->user_id,
                    'title' => ucfirst($this->transactionType).' Successful',
                    'message' => 'A '.$this->transactionType.' of ₦'.number_format($amountMoney->getMajorAmount(), 2).' has been recorded in your savings account.',
                    'type' => 'info',
                    'category' => 'savings',
                    'is_actionable' => false,
                    'priority' => 'medium',
                ]);
            }

            // Create notification for Admin/Staff
            SystemNotification::create([
                'organization_id' => $this->borrower->organization_id,
                'user_id' => Auth::id(),
                'title' => 'Savings Transaction Recorded',
                'message' => ucfirst($this->transactionType).' of ₦'.number_format($amountMoney->getMajorAmount(), 2).' for '.$this->borrower->user->name.' has been recorded.',
                'type' => 'success',
                'category' => 'savings',
                'is_actionable' => false,
                'priority' => 'low',
            ]);
        });

        \App\Events\DashboardUpdated::dispatch($this->borrower->organization_id);
        \App\Livewire\Reports::clearCache($this->borrower->organization_id);

        $this->savingsAccount->refresh();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => ucfirst($this->transactionType).' recorded successfully.']);
    }

    public function deleteTransaction($transactionId)
    {
        if (! Auth::user()->can('delete_savings')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to delete savings records.']);

            return;
        }

        $transaction = SavingsTransaction::findOrFail($transactionId);

        // Check if it belongs to this account
        if ($transaction->savings_account_id !== $this->savingsAccount->id) {
            return;
        }

        // IMPORTANT: Prevent deletion if linked to a loan repayment
        if ($transaction->repayment_id) {
            $this->dispatch('custom-alert', [
                'type' => 'error',
                'message' => 'This record is linked to a loan repayment and cannot be deleted from here.',
            ]);

            return;
        }

        DB::transaction(function () use ($transaction) {
            // Revert balance
            if ($transaction->type === 'deposit') {
                $this->savingsAccount->balance = $this->savingsAccount->balance->subtract($transaction->amount);
            } else {
                $this->savingsAccount->balance = $this->savingsAccount->balance->add($transaction->amount);
            }
            $this->savingsAccount->save();

            $transaction->delete();
        });

        \App\Events\DashboardUpdated::dispatch($this->borrower->organization_id);
        \App\Livewire\Reports::clearCache($this->borrower->organization_id);

        $this->savingsAccount->refresh();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Transaction deleted and balance adjusted.']);
    }

    public function render()
    {
        $transactions = SavingsTransaction::where('savings_account_id', $this->savingsAccount->id)
            ->with('staff')
            ->latest('transaction_date')
            ->paginate(10);

        return view('livewire.savings-details', [
            'transactions' => $transactions,
        ])->layout('layouts.app', ['title' => 'Savings Account']);
    }
}
