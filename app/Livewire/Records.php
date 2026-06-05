<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\SavingsTransaction;
use App\ValueObjects\Money;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Money $savingsBalance
 * @property-read Money $thriftBalance
 */
class Records extends Component
{
    public $savingsPeriod = 'this_month';

    public $thriftPeriod = 'this_month';

    public $customSavingsStart;

    public $customSavingsEnd;

    public $customThriftStart;

    public $customThriftEnd;

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

    protected function calculateBalance($type, $period, $start = null, $end = null)
    {
        $org = Organization::current();
        $query = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $org->id))
            ->where('type', $type);

        $this->applyPeriodFilter($query, $period, $start, $end);

        $amountMinor = (int) $query->sum('amount');

        return new Money($amountMinor, $org->currency_code ?? 'NGN');
    }

    protected function applyPeriodFilter($query, $period, $start, $end)
    {
        $org = Organization::current();
        $today = $org->getSystemTime();

        switch ($period) {
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
            case 'custom':
                if ($start && $end) {
                    $query->whereBetween('transaction_date', [$start, $end]);
                }
                break;
        }
    }

    public function render()
    {
        return view('livewire.records', [
            'savingsBalance' => $this->savingsBalance,
            'thriftBalance' => $this->thriftBalance,
        ])->layout('layouts.app', ['title' => 'Financial Records']);
    }
}
