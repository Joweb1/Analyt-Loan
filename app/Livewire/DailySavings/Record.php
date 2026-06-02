<?php

namespace App\Livewire\DailySavings;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\Saver;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read Collection $customers
 */
class Record extends Component
{
    #[Url]
    public $search = '';

    #[Url]
    public $selectedDate;

    public $weekDays = [];

    public $thriftCycle = 6;

    public $gridData = []; // [user_id => [date => amount]]

    public $paymentMethods = []; // [user_id => [date => 'cash'|'bank_transfer']]

    public $unlockedDays = []; // [user_id => [date => bool]]

    public $today;

    public function mount()
    {
        $org = Organization::current();
        $this->thriftCycle = $org->thrift_cycle_days ?? 6;
        $this->today = $org->getSystemTime()->toDateString();
        $this->selectedDate = $this->today;
        $this->calculateWeek();
    }

    public function updatedSelectedDate()
    {
        $this->calculateWeek();
    }

    public function calculateWeek()
    {
        $org = Organization::current();
        $selected = Carbon::parse($this->selectedDate);
        $startOfWeek = $selected->copy()->startOfWeek();

        $this->weekDays = [];
        for ($i = 0; $i < $this->thriftCycle; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->toDateString();
            $this->weekDays[] = [
                'date' => $dateString,
                'label' => $date->format('D, d M'),
                'is_today' => $dateString === $this->today,
                'is_editable' => $dateString === $this->today, // Strict requirement: only system today is editable
            ];
        }
    }

    public function getSummaryProperty()
    {
        $org = Organization::current();
        $currency = $org->currency_code ?? 'NGN';
        $selectedDate = Carbon::parse($this->selectedDate);

        // 1. Today's Total (actually based on selected date for context)
        $todayTotalMinor = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $org->id))
            ->where('type', 'daily_thrift')
            ->whereDate('transaction_date', $this->selectedDate)
            ->sum('amount');

        // 2. Weekly Total (the week shown in the grid)
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->addDays($this->thriftCycle - 1);

        $weekTotalMinor = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $org->id))
            ->where('type', 'daily_thrift')
            ->whereBetween('transaction_date', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
            ->sum('amount');

        // 3. Monthly Total (based on selected date's month)
        $monthTotalMinor = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('organization_id', $org->id))
            ->where('type', 'daily_thrift')
            ->whereMonth('transaction_date', $selectedDate->month)
            ->whereYear('transaction_date', $selectedDate->year)
            ->sum('amount');

        return [
            'today' => new Money($todayTotalMinor, $currency),
            'week' => new Money($weekTotalMinor, $currency),
            'month' => new Money($monthTotalMinor, $currency),
        ];
    }

    public function togglePaymentMethod($userId, $date)
    {
        if (! isset($this->paymentMethods[$userId][$date]) || $this->paymentMethods[$userId][$date] === 'cash') {
            $this->paymentMethods[$userId][$date] = 'bank_transfer';
        } else {
            $this->paymentMethods[$userId][$date] = 'cash';
        }
    }

    public function toggleUnlock($userId, $date)
    {
        $isAdmin = auth()->user()->isAdmin() || auth()->user()->isAppOwner();
        $isToday = $date === $this->today;
        $isPast = $date < $this->today;
        $isFuture = $date > $this->today;

        if ($isFuture) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Future dates cannot be unlocked.']);

            return;
        }

        // Staff: Today only. Admin: Today and Past.
        $canUnlock = $isToday || ($isAdmin && $isPast);

        if (! $canUnlock) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to unlock past dates.']);

            return;
        }

        if (isset($this->unlockedDays[$userId][$date])) {
            unset($this->unlockedDays[$userId][$date]);
            unset($this->gridData[$userId][$date]);
            unset($this->paymentMethods[$userId][$date]);
        } else {
            $this->unlockedDays[$userId][$date] = true;

            // Pre-fill existing value for easier editing
            $org = Organization::current();
            $currency = $org->currency_code ?? 'NGN';

            $existing = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('user_id', $userId))
                ->where('type', 'daily_thrift')
                ->whereDate('transaction_date', $date)
                ->first();

            if ($existing) {
                $this->gridData[$userId][$date] = $existing->amount->getMajorAmount();
                $this->paymentMethods[$userId][$date] = $existing->payment_method ?? 'cash';
            }
        }
    }

    public function recordSavings($userId, $date)
    {
        $amountMajor = $this->gridData[$userId][$date] ?? null;
        $paymentMethod = $this->paymentMethods[$userId][$date] ?? 'cash';

        $isAdmin = auth()->user()->isAdmin() || auth()->user()->isAppOwner();
        $isToday = $date === $this->today;
        $isPast = $date < $this->today;

        // Validation for permission (repeat for safety)
        if (! ($isToday || ($isAdmin && $isPast))) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to record for this date.']);

            return;
        }

        $amountMajor = (float) $amountMajor;
        if ($amountMajor < 0) {
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Please enter a valid amount.']);

            return;
        }

        try {
            DB::beginTransaction();

            $org = Organization::current();
            $currency = $org->currency_code ?? config('app.currency', 'NGN');
            $newAmount = Money::fromMajor($amountMajor, $currency);

            // Find or create savings account
            $account = SavingsAccount::firstOrCreate(
                ['user_id' => $userId, 'organization_id' => $org->id],
                [
                    'account_number' => 'SAV-'.strtoupper(Str::random(8)),
                    'balance' => new Money(0, $currency),
                    'daily_savings_balance' => new Money(0, $currency),
                    'status' => 'active',
                ]
            );

            // Check for existing transaction for this date to perform an update (replacement)
            $transaction = SavingsTransaction::where('savings_account_id', $account->id)
                ->where('type', 'daily_thrift')
                ->whereDate('transaction_date', $date)
                ->first();

            if ($transaction) {
                // Adjustment logic
                $oldAmount = $transaction->amount;
                $difference = $newAmount->subtract($oldAmount);

                $transaction->amount = $newAmount;
                $transaction->payment_method = $paymentMethod;
                $transaction->notes = 'Daily Savings for '.$date.' (Updated)';
                if ($isPast) {
                    $transaction->notes .= ' [Admin Override]';
                }
                $transaction->save();

                // Update sub-balance by the difference
                $account->daily_savings_balance = $account->daily_savings_balance->add($difference);
                $account->save();
            } else {
                // New transaction
                if ($newAmount->isZero()) {
                    DB::rollBack();
                    unset($this->unlockedDays[$userId][$date]);
                    unset($this->paymentMethods[$userId][$date]);

                    return; // Don't save zero if it didn't exist
                }

                $transaction = new SavingsTransaction;
                $transaction->savings_account_id = $account->id;
                $transaction->amount = $newAmount;
                $transaction->type = 'daily_thrift';
                $transaction->transaction_date = Carbon::parse($date)->setTimeFrom($org->getSystemTime());
                $transaction->staff_id = auth()->id();
                $transaction->payment_method = $paymentMethod;
                $transaction->notes = 'Daily Savings for '.$date;
                if ($isPast) {
                    $transaction->notes .= ' [Admin Override]';
                }
                $transaction->save();

                // Update sub-balance
                $account->daily_savings_balance = $account->daily_savings_balance->add($transaction->amount);
                $account->save();
            }

            DB::commit();

            // Clear input and lock back
            unset($this->gridData[$userId][$date]);
            unset($this->paymentMethods[$userId][$date]);
            unset($this->unlockedDays[$userId][$date]);

            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Savings recorded successfully for '.$date]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCustomersProperty()
    {
        $org = Organization::current();
        $selected = Carbon::parse($this->selectedDate);
        $startOfWeek = $selected->copy()->startOfWeek()->toDateString();
        $endOfWeek = $selected->copy()->startOfWeek()->addDays($this->thriftCycle - 1)->toDateString();

        // 1. Fetch Borrowers enrolled in Daily Savings
        $borrowerQuery = Borrower::where('is_daily_saver', true)
            ->with(['user', 'user.savingsAccount']);

        if ($this->search) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $borrowerQuery->where(function ($sq) use ($term) {
                $sq->whereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhere('custom_id', 'like', $term);
            });
        }

        $borrowers = $borrowerQuery->get()->map(function ($b) use ($startOfWeek, $endOfWeek) {
            return $this->formatCustomerData($b, 'borrower', $startOfWeek, $endOfWeek);
        });

        // 2. Fetch Savers enrolled in Daily Savings
        $saverQuery = Saver::where('is_daily_saver', true)
            ->with(['user', 'user.savingsAccount']);

        if ($this->search) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $saverQuery->where(function ($sq) use ($term) {
                $sq->whereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhere('custom_id', 'like', $term);
            });
        }

        $savers = $saverQuery->get()->map(function ($s) use ($startOfWeek, $endOfWeek) {
            return $this->formatCustomerData($s, 'saver', $startOfWeek, $endOfWeek);
        });

        return $borrowers->concat($savers)->unique('user_id');
    }

    protected function formatCustomerData($customer, $type, $start, $end)
    {
        $userId = $customer->user_id;
        $account = $customer->user->savingsAccount;

        // Fetch actual transactions for this week to populate the grid
        $weeklyTransactions = SavingsTransaction::whereHas('savingsAccount', fn ($q) => $q->where('user_id', $userId))
            ->where('type', 'daily_thrift')
            ->whereBetween('transaction_date', [$start.' 00:00:00', $end.' 23:59:59'])
            ->get();

        $weekTotalMinor = $weeklyTransactions->sum(fn ($t) => $t->amount->getMinorAmount());
        $currency = Organization::current()->currency_code ?? 'NGN';

        $weekData = [];
        foreach ($this->weekDays as $day) {
            $dayAmountMinor = $weeklyTransactions->filter(fn ($t) => Carbon::parse($t->transaction_date)->toDateString() === $day['date'])
                ->sum(fn ($t) => $t->amount->getMinorAmount());

            $weekData[$day['date']] = new Money($dayAmountMinor, $currency);
        }

        return [
            'user_id' => $userId,
            'name' => $customer->user->name,
            'id_label' => $customer->custom_id,
            'type' => $type,
            'daily_target' => $customer->daily_target_amount,
            'daily_savings_balance' => $account ? $account->daily_savings_balance : new Money(0, $currency),
            'week_total' => new Money($weekTotalMinor, $currency),
            'week_grid' => $weekData,
            'photo_url' => $type === 'borrower' ? $customer->photo_url : null,
        ];
    }

    public function render()
    {
        return view('livewire.daily-savings.record', [
            'customers' => $this->customers,
        ])->layout('layouts.app', ['title' => 'Daily Savings Record']);
    }
}
