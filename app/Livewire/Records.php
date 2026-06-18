<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsTransaction;
use App\ValueObjects\Money;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Money $savingsBalance
 * @property-read Money $thriftBalance
 * @property-read Money $loanRepayments
 * @property-read Money $totalBalance
 */
class Records extends Component
{
    public $savingsPeriod = 'today';

    public $thriftPeriod = 'today';

    public $loanPeriod = 'today';

    public $customSavingsStart;

    public $customSavingsEnd;

    public $customThriftStart;

    public $customThriftEnd;

    public $customLoanStart;

    public $customLoanEnd;

    public function mount()
    {
        // Default values
    }

    #[Computed]
    public function savingsBalance()
    {
        return $this->calculateBalance('deposit', $this->savingsPeriod, $this->customSavingsStart, $this->customSavingsEnd);
    }

    #[Computed]
    public function thriftBalance()
    {
        return $this->calculateBalance('daily_thrift', $this->thriftPeriod, $this->customThriftStart, $this->customThriftEnd);
    }

    #[Computed]
    public function loanRepayments()
    {
        $org = Organization::current();
        $query = Repayment::where('organization_id', $org->id);

        $this->applyPeriodFilter($query, $this->loanPeriod, $this->customLoanStart, $this->customLoanEnd, 'paid_at');

        $amountMinor = (int) $query->sum('amount');

        return new Money($amountMinor, $org->currency_code ?? 'NGN');
    }

    #[Computed]
    public function totalBalance()
    {
        return Organization::current()->organization_balance;
    }

    protected function calculateBalance($type, $period, $start = null, $end = null)
    {
        $org = Organization::current();
        $query = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $org->id))
            ->where('type', $type);

        $this->applyPeriodFilter($query, $period, $start, $end);

        $amountMinor = (int) $query->sum('amount');

        return new Money($amountMinor, $org->currency_code ?? 'NGN');
    }

    protected function applyPeriodFilter($query, $period, $start, $end, $column = 'transaction_date')
    {
        $org = Organization::current();
        $today = $org->getSystemTime();

        switch ($period) {
            case 'today':
                $query->whereDate($column, $today->toDateString());
                break;
            case 'this_week':
                $query->whereBetween($column, [
                    $today->copy()->startOfWeek()->toDateString(),
                    $today->copy()->endOfWeek()->toDateString(),
                ]);
                break;
            case 'this_month':
                $query->whereMonth($column, $today->month)
                    ->whereYear($column, $today->year);
                break;
            case 'custom':
                if ($start && $end) {
                    $query->whereBetween($column, [$start, $end]);
                }
                break;
        }
    }

    public function render()
    {
        return view('livewire.records', [
            'savingsBalance' => $this->savingsBalance,
            'thriftBalance' => $this->thriftBalance,
            'loanRepayments' => $this->loanRepayments,
            'totalBalance' => $this->totalBalance,
        ])->layout('layouts.app', ['title' => 'Financial Records']);
    }
}
