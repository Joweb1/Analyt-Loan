<?php

namespace App\Livewire\Ledger;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsTransaction;
use App\Models\ScheduledRepayment;
use App\Services\CashbookService;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class GroupLedger extends Component
{
    public $group;

    #[Url]
    public $search = '';

    public $members = [];

    public $expanded = []; // [borrower_id => bool]

    public $editing = []; // [borrower_id => repayment_id|null]

    public $paymentData = []; // [borrower_id => ['repayment' => 0, 'repayment_method' => 'bank_transfer', 'savings' => 0, 'savings_method' => 'bank_transfer', 'notes' => '']]

    public function mount($group)
    {
        $this->group = $group;
        $this->loadMembers();
    }

    public function toggleExpand($borrowerId)
    {
        $this->expanded[$borrowerId] = ! ($this->expanded[$borrowerId] ?? false);
    }

    public function editPayment($borrowerId, $repaymentId = null)
    {
        $org = Organization::current();
        $systemTime = $org->getSystemTime();
        $today = $systemTime->toDateString();

        // Check if admin
        $isAdmin = auth()->user()->hasRole('Admin') || auth()->user()->type === 'owner';

        $borrower = Borrower::find($borrowerId);
        $repayment = null;

        if ($repaymentId) {
            $repayment = Repayment::find($repaymentId);
        }

        // If period is already paid and user is NOT admin, deny edit
        if ($repayment && ! $isAdmin) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Only administrators can edit verified ledger entries.']);

            return;
        }

        $savings = null;
        if ($borrower->savingsAccount) {
            $savings = SavingsTransaction::where('savings_account_id', $borrower->savingsAccount->id)
                ->where('type', 'deposit')
                ->whereDate('transaction_date', $today)
                ->first();
        }

        $this->paymentData[$borrowerId] = [
            'repayment' => $repayment ? $repayment->amount->getMajorAmount() : 0,
            'repayment_method' => $repayment ? $repayment->payment_method : 'bank_transfer',
            'savings' => $savings ? $savings->amount->getMajorAmount() : 0,
            'savings_method' => $savings ? $savings->payment_method : 'bank_transfer',
            'notes' => ($repayment ? $repayment->notes : '') ?: ($savings ? $savings->notes : ''),
        ];

        $this->editing[$borrowerId] = $repaymentId ?: 'new';
        $this->expanded[$borrowerId] = true;
    }

    public function loadMembers()
    {
        $org = Organization::current();
        $systemTime = $org->getSystemTime();
        $today = $systemTime->toDateString();
        $currency = $org->currency_code ?? 'NGN';

        $startOfWeek = $systemTime->copy()->startOfWeek();
        $endOfWeek = $systemTime->copy()->endOfWeek();

        if ($this->group === 'Monthly Collections') {
            $query = Borrower::whereHas('loans', fn ($q) => $q->where('repayment_cycle', 'monthly'));
        } else {
            // Group by LOAN collection group
            $query = Borrower::whereHas('loans', fn ($q) => $q->where('collection_group', $this->group));
        }

        if ($this->search) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhere('custom_id', 'like', $term);
            });
        }

        $borrowers = $query->with(['user', 'loans', 'savingsAccount'])->get();

        $this->members = [];
        foreach ($borrowers as $borrower) {
            // Get current active/overdue loan filtered by the group cycle AND group name
            $loanQuery = $borrower->loans()->whereIn('status', ['active', 'overdue']);

            if ($this->group === 'Monthly Collections') {
                $loanQuery->where('repayment_cycle', 'monthly');
            } else {
                $loanQuery->where('collection_group', $this->group)
                    ->where('repayment_cycle', '!=', 'monthly');
            }

            $activeLoan = $loanQuery->latest()->first();

            // Loan Index (Total count of loans taken by this borrower)
            $loanCount = $borrower->loans()->count();
            $loanIndexLabel = $this->getOrdinal($loanCount).' Loan';

            $nextDue = null;
            $paymentStatus = 'Upcoming';
            $dueAmountMinor = 0;
            $paidThisPeriod = false;
            $periodRepayments = collect();

            if ($activeLoan) {
                // Sum all overdue + today's pending installments
                $dueSchedules = ScheduledRepayment::where('loan_id', $activeLoan->id)
                    ->where('status', '!=', 'paid')
                    ->where('due_date', '<=', $today)
                    ->get();

                $dueAmountMinor = $dueSchedules->sum(fn ($s) => $s->principal_amount->getMinorAmount() + $s->interest_amount->getMinorAmount() + $s->penalty_amount->getMinorAmount() - $s->paid_amount->getMinorAmount());

                // Find the absolute next due date (even if future)
                $nextSchedule = ScheduledRepayment::where('loan_id', $activeLoan->id)
                    ->where('status', '!=', 'paid')
                    ->orderBy('due_date')
                    ->first();

                if ($nextSchedule) {
                    $nextDue = $nextSchedule->due_date;
                    if ($nextDue->isPast() && $dueAmountMinor > 0) {
                        $paymentStatus = 'Overdue';
                    } elseif ($nextDue->isToday()) {
                        $paymentStatus = 'Due Today';
                    }
                }

                // Check if already paid in the current period
                if ($this->group === 'Monthly Collections') {
                    $periodRepayments = Repayment::where('loan_id', $activeLoan->id)
                        ->whereMonth('paid_at', $systemTime->month)
                        ->whereYear('paid_at', $systemTime->year)
                        ->get();
                } else {
                    $periodRepayments = Repayment::where('loan_id', $activeLoan->id)
                        ->whereBetween('paid_at', [$startOfWeek->toDateString().' 00:00:00', $endOfWeek->toDateString().' 23:59:59'])
                        ->get();
                }

                $paidThisPeriod = $periodRepayments->isNotEmpty();

                if ($paidThisPeriod) {
                    $paymentStatus = 'Paid';
                }
            }

            $this->members[] = [
                'id' => $borrower->id,
                'name' => $borrower->user->name,
                'custom_id' => $borrower->custom_id,
                'active_loan' => $activeLoan,
                'loan_index' => $loanIndexLabel,
                'savings_account' => $borrower->savingsAccount,
                'next_due_date' => $nextDue ? $nextDue->format('d M Y') : 'N/A',
                'due_amount' => new Money($dueAmountMinor, $currency),
                'outstanding_balance' => $activeLoan ? $activeLoan->balance : new Money(0, $currency),
                'status' => $paymentStatus,
                'paid_this_period' => $paidThisPeriod,
                'period_repayments' => $periodRepayments,
                'last_payment_date' => $borrower->repayments()->latest('paid_at')->value('paid_at')?->format('d M Y') ?: 'None',
            ];

            if (! isset($this->paymentData[$borrower->id])) {
                $this->paymentData[$borrower->id] = [
                    'repayment' => 0,
                    'repayment_method' => 'bank_transfer',
                    'savings' => 0,
                    'savings_method' => 'bank_transfer',
                    'notes' => '',
                ];
            }
        }
    }

    private function getOrdinal($n)
    {
        $res = $n % 100;
        if ($res >= 11 && $res <= 13) {
            return $n.'th';
        }
        switch ($n % 10) {
            case 1:  return $n.'st';
            case 2:  return $n.'nd';
            case 3:  return $n.'rd';
            default: return $n.'th';
        }
    }

    public function toggleMethod($borrowerId, $type)
    {
        $field = $type === 'repayment' ? 'repayment_method' : 'savings_method';
        $this->paymentData[$borrowerId][$field] = $this->paymentData[$borrowerId][$field] === 'cash' ? 'bank_transfer' : 'cash';
    }

    public function updatedSearch()
    {
        $this->loadMembers();
    }

    public function recordPayment($borrowerId)
    {
        $data = $this->paymentData[$borrowerId];
        $repaymentAmount = (float) $data['repayment'];
        $repaymentMethod = $data['repayment_method'];
        $savingsAmount = (float) $data['savings'];
        $savingsMethod = $data['savings_method'];
        $notes = $data['notes'];

        if ($repaymentAmount <= 0 && $savingsAmount <= 0 && ! ($this->editing[$borrowerId] ?? false)) {
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Please enter a valid amount.']);

            return;
        }

        $borrower = Borrower::find($borrowerId);
        $org = Organization::current();
        $systemTime = $org->getSystemTime();
        $today = $systemTime->toDateString();
        $currency = $org->currency_code ?? config('app.currency', 'NGN');

        $startOfWeek = $systemTime->copy()->startOfWeek();
        $endOfWeek = $systemTime->copy()->endOfWeek();

        try {
            DB::beginTransaction();

            // 1. Record/Update Loan Repayment
            $loanQuery = $borrower->loans()->whereIn('status', ['active', 'overdue']);
            if ($this->group === 'Monthly Collections') {
                $loanQuery->where('repayment_cycle', 'monthly');
            } else {
                $loanQuery->where('collection_group', $this->group)
                    ->where('repayment_cycle', '!=', 'monthly');
            }
            $activeLoan = $loanQuery->latest()->first();

            if ($activeLoan) {
                $repaymentId = $this->editing[$borrowerId] ?? null;
                $repayment = null;

                if ($repaymentId && $repaymentId !== 'new') {
                    $repayment = Repayment::find($repaymentId);
                }

                if ($repaymentAmount > 0) {
                    $newAmountMoney = Money::fromMajor($repaymentAmount, $currency);

                    // Split logic remains the same...
                    // 1. Get total of ALL OTHER repayments (to know where this one starts)
                    $otherRepaymentsTotalMinor = (int) Repayment::where('loan_id', $activeLoan->id)
                        ->where('id', '!=', $repayment->id ?? 'non-existent-uuid')
                        ->sum('amount');

                    $poolMinor = $otherRepaymentsTotalMinor;
                    $remainingToSplitMinor = $newAmountMoney->getMinorAmount();

                    $schedules = $activeLoan->scheduledRepayments()->orderBy('due_date')->get();

                    $interestPartMinor = 0;
                    $feePartMinor = 0;
                    $principalPartMinor = 0;
                    $extraPartMinor = 0;

                    foreach ($schedules as $s) {
                        if ($remainingToSplitMinor <= 0) {
                            break;
                        }

                        $sTotalDueMinor = $s->principal_amount->getMinorAmount() + $s->interest_amount->getMinorAmount() + $s->penalty_amount->getMinorAmount();

                        // How much of THIS schedule is covered by previous repayments?
                        $coveredByPool = min($poolMinor, $sTotalDueMinor);
                        $poolMinor -= $coveredByPool;

                        $sRemainingDueMinor = $sTotalDueMinor - $coveredByPool;
                        if ($sRemainingDueMinor <= 0) {
                            continue;
                        }

                        // 1. Interest part of the remaining due
                        $sInterestTotalMinor = $s->interest_amount->getMinorAmount();
                        $sInterestAlreadyCovered = min($coveredByPool, $sInterestTotalMinor);
                        $sInterestRemainingMinor = $sInterestTotalMinor - $sInterestAlreadyCovered;

                        $toInterest = min($remainingToSplitMinor, $sInterestRemainingMinor);
                        $interestPartMinor += $toInterest;
                        $remainingToSplitMinor -= $toInterest;

                        if ($remainingToSplitMinor <= 0) {
                            break;
                        }

                        // 2. Fee part (stored in penalty_amount)
                        $sFeeTotalMinor = $s->penalty_amount->getMinorAmount();
                        $sFeeAlreadyCovered = min(max(0, $coveredByPool - $sInterestTotalMinor), $sFeeTotalMinor);
                        $sFeeRemainingMinor = $sFeeTotalMinor - $sFeeAlreadyCovered;

                        $toFee = min($remainingToSplitMinor, $sFeeRemainingMinor);
                        $feePartMinor += $toFee;
                        $remainingToSplitMinor -= $toFee;

                        if ($remainingToSplitMinor <= 0) {
                            break;
                        }

                        // 3. Principal part
                        $sPrincipalTotalMinor = $s->principal_amount->getMinorAmount();
                        $sPrincipalAlreadyCovered = min(max(0, $coveredByPool - $sInterestTotalMinor - $sFeeTotalMinor), $sPrincipalTotalMinor);
                        $sPrincipalRemainingMinor = $sPrincipalTotalMinor - $sPrincipalAlreadyCovered;

                        $toPrincipal = min($remainingToSplitMinor, $sPrincipalRemainingMinor);
                        $principalPartMinor += $toPrincipal;
                        $remainingToSplitMinor -= $toPrincipal;
                    }

                    if ($remainingToSplitMinor > 0) {
                        $extraPartMinor = $remainingToSplitMinor;
                    }

                    if (! $repayment) {
                        $repayment = new Repayment;
                        $repayment->loan_id = $activeLoan->id;
                        $repayment->borrower_id = $borrower->id;
                        $repayment->organization_id = $org->id;
                        $repayment->paid_at = $systemTime;
                        $repayment->recorded_by = auth()->id();
                        $repayment->collected_by = auth()->id();
                    }
                    $repayment->amount = $newAmountMoney;
                    $repayment->principal_amount = new Money($principalPartMinor, $currency);
                    $repayment->interest_amount = new Money($interestPartMinor, $currency);
                    $repayment->fee_amount = new Money($feePartMinor, $currency);
                    $repayment->extra_amount = new Money($extraPartMinor, $currency);
                    $repayment->payment_method = $repaymentMethod;
                    $repayment->notes = $notes;
                    $repayment->save();

                    $activeLoan->refreshRepaymentStatus();
                } elseif ($repayment) {
                    // If edited to 0, delete it
                    $repayment->delete();
                    $activeLoan->refreshRepaymentStatus();
                }
            }

            // 2. Record/Update Savings Deposit
            $savingsAccount = $borrower->savingsAccount;
            if ($savingsAccount) {
                $transaction = null;
                // If editing, we find the transaction for today to update it
                if ($this->editing[$borrowerId] ?? false) {
                    $transaction = SavingsTransaction::where('savings_account_id', $savingsAccount->id)
                        ->where('type', 'deposit')
                        ->whereDate('transaction_date', $today)
                        ->first();
                }

                $newSavingsMoney = Money::fromMajor($savingsAmount, $currency);

                if ($transaction) {
                    $oldAmount = $transaction->amount;
                    $difference = $newSavingsMoney->subtract($oldAmount);

                    if ($newSavingsMoney->isZero()) {
                        $transaction->delete();
                    } else {
                        $transaction->update([
                            'amount' => $newSavingsMoney,
                            'payment_method' => $savingsMethod,
                            'notes' => $notes,
                        ]);
                    }

                    $savingsAccount->balance = $savingsAccount->balance->add($difference);
                    $savingsAccount->save();
                } elseif ($savingsAmount > 0) {
                    $transaction = new SavingsTransaction;
                    $transaction->savings_account_id = $savingsAccount->id;
                    $transaction->amount = $newSavingsMoney;
                    $transaction->type = 'deposit';
                    $transaction->payment_method = $savingsMethod;
                    $transaction->notes = $notes;
                    $transaction->staff_id = auth()->id();
                    $transaction->transaction_date = $systemTime;
                    $transaction->save();

                    $savingsAccount->balance = $savingsAccount->balance->add($transaction->amount);
                    $savingsAccount->save();
                }
            }

            DB::commit();

            // 3. Trigger Cashbook Refresh for today
            $cashbookService = app(CashbookService::class);
            $entry = $cashbookService->getEntryForDate(Carbon::parse($today), $org);
            $cashbookService->fetchSystemData($entry);

            $this->paymentData[$borrowerId] = [
                'repayment' => 0, 'repayment_method' => 'bank_transfer',
                'savings' => 0, 'savings_method' => 'bank_transfer',
                'notes' => '',
            ];

            unset($this->editing[$borrowerId]);
            $this->loadMembers();
            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Ledger entries applied successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.ledger.group-ledger')
            ->layout('layouts.app', ['title' => $this->group.' Ledger']);
    }
}
