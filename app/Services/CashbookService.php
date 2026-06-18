<?php

namespace App\Services;

use App\Models\AccountBalance;
use App\Models\Borrower;
use App\Models\CashbookEntry;
use App\Models\ExpenseBudget;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsTransaction;
use App\Models\Transaction;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;

class CashbookService
{
    /**
     * Get or create a cashbook entry for a specific date.
     */
    public function getEntryForDate(Carbon $date, Organization $organization): CashbookEntry
    {
        return CashbookEntry::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'entry_date' => $date->toDateString(),
            ],
            [
                'status' => 'pending',
            ]
        );
    }

    /**
     * Auto-fetch financial data from the system for a specific date.
     */
    public function fetchSystemData(CashbookEntry $entry): void
    {
        $date = $entry->entry_date;
        $orgId = $entry->organization_id;
        $currency = $entry->organization->currency_code;

        // 1. Loan Repayments (Split into Principal and Interest)
        $repaymentsQuery = Repayment::whereDate('paid_at', $date)
            ->whereHas('loan', fn ($q) => $q->where('organization_id', $orgId));

        // Principal + Extras (Penalties, etc.)
        $principalMinor = (int) $repaymentsQuery->sum('principal_amount') + (int) $repaymentsQuery->sum('extra_amount');
        $interestMinor = (int) $repaymentsQuery->sum('interest_amount');

        $entry->loan_repayments = new Money($principalMinor, $currency);
        $entry->loan_interest = new Money($interestMinor, $currency);

        // 2. Savings Deposits (Total)
        $savingsQuery = SavingsTransaction::whereDate('transaction_date', $date)
            ->whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $orgId));

        $entry->savings_deposits = new Money(
            (int) (clone $savingsQuery)->where('type', 'deposit')->sum('amount'),
            $currency
        );

        $entry->daily_savings = new Money(
            (int) (clone $savingsQuery)->where('type', 'daily_thrift')->sum('amount'),
            $currency
        );

        // 3. Savings Withdrawals
        $entry->savings_withdrawals = new Money(
            (int) (clone $savingsQuery)->where('type', 'withdrawal')->sum('amount'),
            $currency
        );

        // 4. Loan Disbursements
        $entry->loan_disbursements = new Money(
            (int) Loan::whereDate('release_date', $date)
                ->where('organization_id', $orgId)
                ->whereIn('status', ['active', 'closed', 'restructured'])
                ->sum('amount'),
            $currency
        );

        // 5. Registration Fees (1000 per borrower registered that day)
        $borrowerCount = Borrower::whereDate('created_at', $date)
            ->where('organization_id', $orgId)
            ->count();
        $entry->registration_fees = new Money($borrowerCount * 100000, $currency);

        // 6. Fees (Processing & Insurance) - NOW FROM TRANSACTIONS TABLE
        $entry->loan_processing_fees = new Money(
            (int) Transaction::where('organization_id', $orgId)
                ->where('type', 'processing_fee')
                ->whereDate('transaction_date', $date)
                ->sum('amount'),
            $currency
        );

        $entry->insurance_fees = new Money(
            (int) Transaction::where('organization_id', $orgId)
                ->where('type', 'insurance_fee')
                ->whereDate('transaction_date', $date)
                ->sum('amount'),
            $currency
        );

        // 7. Calculate Expected Bank Transfers
        $bankMethods = ['bank_transfer', 'transfer', 'Bank Transfer', 'Bank transfer', 'Transfer'];
        $bankRepayments = (int) (clone $repaymentsQuery)->whereIn('payment_method', $bankMethods)->sum('amount');
        $bankSavings = (int) (clone $savingsQuery)->whereIn('type', ['deposit', 'daily_thrift'])->whereIn('payment_method', $bankMethods)->sum('amount');

        // Include upfront fees paid via bank transfer
        $bankFees = (int) Transaction::where('organization_id', $orgId)
            ->whereIn('type', ['processing_fee', 'insurance_fee'])
            ->whereIn('payment_method', $bankMethods)
            ->whereDate('transaction_date', $date)
            ->sum('amount');

        $entry->expected_bank_transfers = new Money($bankRepayments + $bankSavings + $bankFees, $currency);

        $this->recalculateExpectedCash($entry);
        $entry->save();
    }

    /**
     * Recalculate context.
     */
    public function recalculateExpectedCash(CashbookEntry $entry): void
    {
        $entry->expected_cash_at_hand = $entry->total_inflow->subtract($entry->expected_bank_transfers);
        $this->syncMonthlyBudget($entry);
    }

    /**
     * Get the live account balance as of a specific date.
     */
    public function getLiveAccountBalance(Carbon $date, Organization $organization): Money
    {
        $orgId = $organization->id;
        $currency = $organization->currency_code;

        $opening = AccountBalance::where('organization_id', $orgId)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->first();

        $openingBalance = $opening ? $opening->opening_balance : new Money(0, $currency);

        // 1. Total Bank Deposits (Manually entered in Cashbook)
        $totalDepositedMinor = CashbookEntry::where('organization_id', $orgId)
            ->whereMonth('entry_date', $date->month)
            ->whereYear('entry_date', $date->year)
            ->where('entry_date', '<=', $date->toDateString())
            ->sum('bank_deposit_amount');

        // 2. Total System Outflows (Directly from source tables to be truly live)
        // Loan Disbursements
        $totalDisbursementsMinor = Loan::where('organization_id', $orgId)
            ->whereMonth('release_date', $date->month)
            ->whereYear('release_date', $date->year)
            ->whereDate('release_date', '<=', $date->toDateString())
            ->whereIn('status', ['active', 'closed', 'restructured'])
            ->sum('amount');

        // Savings Withdrawals
        $totalSavingsWithdrawalsMinor = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $orgId))
            ->whereMonth('transaction_date', $date->month)
            ->whereYear('transaction_date', $date->year)
            ->whereDate('transaction_date', '<=', $date->toDateString())
            ->where('type', 'withdrawal')
            ->sum('amount');

        // Bank Withdrawals & Charges (from CashbookEntry since they are manual/entry-based)
        $totalEntryOutflowsMinor = CashbookEntry::where('organization_id', $orgId)
            ->whereMonth('entry_date', $date->month)
            ->whereYear('entry_date', $date->year)
            ->where('entry_date', '<=', $date->toDateString())
            ->get()
            ->sum(fn ($e) => $e->bank_withdrawals->getMinorAmount() +
                $e->charges->getMinorAmount() +
                $e->bonuses->getMinorAmount()
            );

        return $openingBalance
            ->add(new Money($totalDepositedMinor, $currency))
            ->subtract(new Money($totalDisbursementsMinor, $currency))
            ->subtract(new Money($totalSavingsWithdrawalsMinor, $currency))
            ->subtract(new Money($totalEntryOutflowsMinor, $currency));
    }

    /**
     * Sync the daily expense with the monthly budget pool.
     */
    protected function syncMonthlyBudget(CashbookEntry $entry): void
    {
        $date = $entry->entry_date;
        $budget = ExpenseBudget::firstOrCreate([
            'organization_id' => $entry->organization_id,
            'month' => $date->month,
            'year' => $date->year,
        ], [
            'total_budget_amount' => 0,
            'spent_amount' => 0,
        ]);

        $totalSpentMinor = CashbookEntry::where('organization_id', $entry->organization_id)
            ->whereMonth('entry_date', $date->month)
            ->whereYear('entry_date', $date->year)
            ->sum('daily_expense_amount');

        $budget->spent_amount = new Money($totalSpentMinor, $entry->organization->currency_code);
        $budget->save();
    }

    /**
     * Get the remaining budget for a specific month.
     */
    public function getRemainingBudget(Carbon $date, Organization $organization): Money
    {
        $budget = ExpenseBudget::where('organization_id', $organization->id)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->first();

        return $budget ? $budget->remaining : new Money(0, $organization->currency_code);
    }

    /**
     * Get the total budget for a specific month.
     */
    public function getTotalBudget(Carbon $date, Organization $organization): Money
    {
        $budget = ExpenseBudget::where('organization_id', $organization->id)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->first();

        return $budget ? $budget->total_budget_amount : new Money(0, $organization->currency_code);
    }

    /**
     * Verify and lock a cashbook entry.
     */
    public function verifyEntry(CashbookEntry $entry): bool
    {
        // Target: Bank Deposit must at least equal Total Inflow
        if ($entry->bank_deposit_amount->getMinorAmount() >= $entry->total_inflow->getMinorAmount()) {
            $entry->status = 'verified';
            $entry->verified_at = now();
            $entry->audit_hash = hash('sha256', $entry->toJson());
            $entry->save();

            // Record Manual Outflows as System Transactions
            $this->recordManualOutflows($entry);

            return true;
        }

        $entry->status = 'discrepancy';
        $entry->save();

        return false;
    }

    protected function recordManualOutflows(CashbookEntry $entry): void
    {
        $org = $entry->organization;
        $date = $entry->entry_date->toDateString();

        // 1. Daily Expenses
        if ($entry->daily_expense_amount->getMinorAmount() > 0) {
            TransactionService::record(
                type: 'charge',
                amount: $entry->daily_expense_amount,
                notes: "Daily Operating Expenses for {$date}",
                date: $date,
                related: $entry
            );
        }

        // 2. Bank Withdrawals
        if ($entry->bank_withdrawals->getMinorAmount() > 0) {
            TransactionService::record(
                type: 'withdrawal',
                amount: $entry->bank_withdrawals,
                notes: "Manual Bank Withdrawal for {$date}",
                date: $date,
                related: $entry
            );
        }

        // 3. System Charges
        if ($entry->charges->getMinorAmount() > 0) {
            TransactionService::record(
                type: 'charge',
                amount: $entry->charges,
                notes: "Manual System Charges for {$date}",
                date: $date,
                related: $entry
            );
        }

        // 4. Bonuses
        if ($entry->bonuses->getMinorAmount() > 0) {
            TransactionService::record(
                type: 'bonus',
                amount: $entry->bonuses,
                notes: "System Bonuses/Payouts for {$date}",
                date: $date,
                related: $entry
            );
        }
    }
}
