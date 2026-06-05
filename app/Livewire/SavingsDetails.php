<?php

namespace App\Livewire;

use App\Events\DashboardUpdated;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\SavingsWithdrawal;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\CashbookService;
use App\Services\TransactionService;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SavingsDetails extends Component
{
    use WithPagination;

    public User $user;

    public $savingsAccount;

    public $staffs;

    // Transaction Modal State
    public $showTransactionModal = false;

    public $transactionType = 'deposit'; // deposit or withdrawal

    public $amount;

    public $notes;

    public $reference;

    public $transactionDate;

    public $paymentMethod = 'cash';

    public $sourceAccount = 'regular'; // regular or daily_thrift

    public function mount(User $user)
    {
        $this->user = $user->load(['organization']);

        $orgId = Auth::user()->organization_id;
        $this->staffs = User::where('organization_id', $orgId)
            ->role(['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist'])
            ->get();

        $this->savingsAccount = SavingsAccount::firstOrCreate(
            ['user_id' => $this->user->id],
            [
                'organization_id' => $this->user->organization_id,
                'account_number' => 'SAV-'.strtoupper(Str::random(8)),
                'balance' => 0,
                'daily_savings_balance' => 0,
                'interest_rate' => 0,
                'status' => 'active',
            ]
        );

        $this->transactionDate = now()->format('Y-m-d');
    }

    public function openTransactionModal($type)
    {
        $this->transactionType = $type;
        $this->sourceAccount = 'regular';
        $this->resetValidation();
        $this->amount = null;
        $this->notes = null;
        $this->paymentMethod = 'cash';
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
            'paymentMethod' => 'required|in:cash,bank_transfer',
            'sourceAccount' => 'required|in:regular,daily_thrift',
        ]);

        $amountMoney = Money::fromMajor($this->amount, $this->user->organization->currency_code ?? 'NGN');

        // Check balance based on source
        if ($this->transactionType === 'withdrawal') {
            $currentBalance = $this->sourceAccount === 'regular'
                ? $this->savingsAccount->balance
                : $this->savingsAccount->daily_savings_balance;

            if ($currentBalance->getMinorAmount() < $amountMoney->getMinorAmount()) {
                $this->addError('amount', 'Insufficient balance in '.($this->sourceAccount === 'regular' ? 'Regular' : 'Daily Savings').' for this withdrawal.');

                return;
            }
        }

        DB::transaction(function () use ($amountMoney) {
            // Determine transaction type for database
            // Withdrawals are always 'withdrawal' for cashbook consistency
            // Deposits are 'daily_thrift' or 'deposit'
            $dbType = $this->transactionType === 'withdrawal' ? 'withdrawal' :
                     ($this->sourceAccount === 'daily_thrift' ? 'daily_thrift' : 'deposit');

            // Create transaction record
            $transaction = SavingsTransaction::create([
                'savings_account_id' => $this->savingsAccount->id,
                'amount' => $amountMoney,
                'type' => $dbType,
                'reference' => $this->reference,
                'notes' => ($this->notes ? $this->notes.' ' : '').($this->sourceAccount === 'daily_thrift' ? '[Daily Savings]' : ''),
                'staff_id' => Auth::id(),
                'transaction_date' => $this->transactionDate,
                'payment_method' => $this->paymentMethod,
            ]);

            // Master Transaction Log
            TransactionService::record(
                type: $dbType,
                amount: $amountMoney,
                user: $this->user,
                related: $transaction,
                paymentMethod: $this->paymentMethod,
                notes: $this->notes
            );

            // If it's a withdrawal, also create a record in the formal Withdrawal Ledger
            if ($this->transactionType === 'withdrawal') {
                SavingsWithdrawal::create([
                    'organization_id' => $this->user->organization_id,
                    'reference' => $this->reference,
                    'savings_account_id' => $this->savingsAccount->id,
                    'transaction_date' => $this->transactionDate,
                    'snapshot_balance' => $this->sourceAccount === 'daily_thrift'
                        ? $this->savingsAccount->daily_savings_balance
                        : $this->savingsAccount->balance,
                    'amount_withdrawn' => $amountMoney,
                    'status' => 'approved', // Auto-approved as it's recorded directly by staff
                    'staff_id' => Auth::id(),
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'notes' => $transaction->notes,
                ]);
            }

            // Update account balance
            if ($this->transactionType === 'deposit') {
                if ($this->sourceAccount === 'daily_thrift') {
                    $this->savingsAccount->daily_savings_balance = $this->savingsAccount->daily_savings_balance->add($amountMoney);
                } else {
                    $this->savingsAccount->balance = $this->savingsAccount->balance->add($amountMoney);
                }
            } else {
                if ($this->sourceAccount === 'daily_thrift') {
                    $this->savingsAccount->daily_savings_balance = $this->savingsAccount->daily_savings_balance->subtract($amountMoney);
                } else {
                    $this->savingsAccount->balance = $this->savingsAccount->balance->subtract($amountMoney);
                }
            }
            $this->savingsAccount->save();

            // Refresh Cashbook for this date
            $cashbookService = app(CashbookService::class);
            $entry = $cashbookService->getEntryForDate(Carbon::parse($this->transactionDate), $this->user->organization);
            $cashbookService->fetchSystemData($entry);

            // Create notification for the user
            SystemNotification::create([
                'organization_id' => $this->user->organization_id,
                'user_id' => Auth::id(),
                'recipient_id' => $this->user->id,
                'title' => ucfirst($this->transactionType).' Successful',
                'message' => 'A '.$this->transactionType.' of ₦'.number_format($amountMoney->getMajorAmount(), 2).' has been recorded in your savings account.',
                'type' => 'info',
                'category' => 'savings',
                'is_actionable' => false,
                'priority' => 'medium',
            ]);

            // Create notification for Admin/Staff
            SystemNotification::create([
                'organization_id' => $this->user->organization_id,
                'user_id' => Auth::id(),
                'title' => 'Savings Transaction Recorded',
                'message' => ucfirst($this->transactionType).' of ₦'.number_format($amountMoney->getMajorAmount(), 2).' for '.$this->user->name.' has been recorded.',
                'type' => 'success',
                'category' => 'savings',
                'is_actionable' => false,
                'priority' => 'low',
            ]);
        });

        DashboardUpdated::dispatch($this->user->organization_id);
        Reports::clearCache($this->user->organization_id);

        $this->savingsAccount->refresh();
        $this->showTransactionModal = false; // Close the modal
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => ucfirst($this->transactionType).' recorded successfully.']);
    }

    public function deleteTransaction($transactionId)
    {
        $this->authorize('delete_savings');

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
            if ($transaction->type === 'daily_thrift') {
                $this->savingsAccount->daily_savings_balance = $this->savingsAccount->daily_savings_balance->subtract($transaction->amount);
            } elseif ($transaction->type === 'deposit') {
                $this->savingsAccount->balance = $this->savingsAccount->balance->subtract($transaction->amount);
            } else {
                // It's a withdrawal. Check notes tag to see if it was from Daily Thrift.
                if (str_contains($transaction->notes ?? '', '[Daily Savings]')) {
                    $this->savingsAccount->daily_savings_balance = $this->savingsAccount->daily_savings_balance->add($transaction->amount);
                } else {
                    $this->savingsAccount->balance = $this->savingsAccount->balance->add($transaction->amount);
                }
            }
            $this->savingsAccount->save();

            // Delete associated SavingsWithdrawal record if it exists
            SavingsWithdrawal::where('reference', $transaction->reference)->delete();

            $transaction->delete();
        });

        DashboardUpdated::dispatch($this->user->organization_id);
        Reports::clearCache($this->user->organization_id);

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
