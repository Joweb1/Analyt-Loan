<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Transaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
{
    use WithPagination;

    #[Url]
    public $period = 'this_month';

    #[Url]
    public $type = 'all';

    public $customStart;

    public $customEnd;

    public function updating($property)
    {
        if (in_array($property, ['period', 'type', 'customStart', 'customEnd'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $org = Organization::current();
        $query = Transaction::with(['user', 'performer', 'related'])
            ->where('organization_id', $org->id);

        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        $this->applyDateFilter($query);

        return view('livewire.transaction-history', [
            'transactions' => $query->latest('transaction_date')->latest('created_at')->paginate(20),
            'types' => $this->getTransactionTypes(),
        ])->layout('layouts.app', ['title' => 'Transaction History']);
    }

    protected function applyDateFilter($query)
    {
        $org = Organization::current();
        $today = $org->getSystemTime();

        switch ($this->period) {
            case 'today':
                $query->whereDate('transaction_date', $today->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('transaction_date', [
                    $today->copy()->startOfWeek()->toDateString(),
                    $today->copy()->endOfWeek()->toDateString(),
                ]);
                break;
            case 'this_month':
                $query->whereMonth('transaction_date', $today->month)
                    ->whereYear('transaction_date', $today->year);
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonth();
                $query->whereMonth('transaction_date', $lastMonth->month)
                    ->whereYear('transaction_date', $lastMonth->year);
                break;
            case 'this_year':
                $query->whereYear('transaction_date', $today->year);
                break;
            case 'last_year':
                $query->whereYear('transaction_date', $today->year - 1);
                break;
            case 'custom':
                if ($this->customStart && $this->customEnd) {
                    $query->whereBetween('transaction_date', [$this->customStart, $this->customEnd]);
                }
                break;
        }
    }

    protected function getTransactionTypes()
    {
        return [
            'all' => 'All Types',
            'registration_fee' => 'Registration Fee',
            'deposit' => 'Savings Deposit',
            'withdrawal' => 'Savings Withdrawal',
            'daily_thrift' => 'Daily Thrift',
            'loan_disbursement' => 'Loan Disbursement',
            'repayment' => 'Loan Repayment',
            'interest' => 'Interest Income',
            'penalty' => 'Penalty/Fee',
            'charge' => 'System Charge',
            'bonus' => 'System Bonus',
            'adjustment' => 'Adjustment',
            'balance_update' => 'Account Balance',
            'budget_update' => 'Budget Update',
        ];
    }
}
