<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\SavingsWithdrawal;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SavingsWithdrawalRegister extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $selectedDate;

    protected $listeners = ['refreshRegister' => '$refresh'];

    public function mount()
    {
        $org = Organization::current();
        $this->selectedDate = $org->getSystemTime()->toDateString();
    }

    public function showAll()
    {
        $this->selectedDate = null;
    }

    public function updateNote($id, $note)
    {
        $withdrawal = SavingsWithdrawal::findOrFail($id);
        $withdrawal->update(['notes' => $note]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notes updated for '.($withdrawal->savingsAccount->user->name ?? 'Customer'),
        ]);
    }

    public function updateStatus($id, $newStatus)
    {
        $withdrawal = SavingsWithdrawal::findOrFail($id);

        // Authorization check - only Admins can approve/reject
        if (! Auth::user()->hasRole('Admin') && in_array($newStatus, ['approved', 'rejected', 'verified'])) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unauthorized action.']);

            return;
        }

        $oldStatus = $withdrawal->status;

        $withdrawal->update([
            'status' => $newStatus,
            'approved_by' => in_array($newStatus, ['approved', 'verified']) ? Auth::id() : $withdrawal->approved_by,
            'approved_at' => in_array($newStatus, ['approved', 'verified']) ? now() : $withdrawal->approved_at,
            'audit_trail' => array_merge($withdrawal->audit_trail ?? [], [[
                'from' => $oldStatus,
                'to' => $newStatus,
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
            ]]),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Status updated to '.ucfirst($newStatus),
        ]);
    }

    public function render()
    {
        $query = SavingsWithdrawal::with(['savingsAccount.user.borrower', 'staff', 'approver'])
            ->orderBy('transaction_date', 'desc');

        if ($this->selectedDate) {
            $query->whereDate('transaction_date', $this->selectedDate);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('savingsAccount.user', function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%');
                })->orWhere('reference', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $allRecords = $query->get();

        // Grouping by Month/Year
        $groupedRecords = $allRecords->groupBy(function ($record) {
            return $record->transaction_date->format('F Y');
        });

        // Org-wide Balances
        $orgSavings = \App\Models\SavingsAccount::get();
        $totalRegularMinor = $orgSavings->sum(fn ($a) => $a->balance->getMinorAmount());
        $totalThriftMinor = $orgSavings->sum(fn ($a) => $a->daily_savings_balance->getMinorAmount());

        // Summary Calculations
        $stats = [
            'total_withdrawals' => $this->formatMoney($allRecords->where('status', 'approved')->sum(fn ($r) => $r->amount_withdrawn->getMinorAmount())),
            'total_savings_balance' => $this->formatMoney($totalRegularMinor),
            'total_thrift_balance' => $this->formatMoney($totalThriftMinor),
            'total_loan_adjustments' => $this->formatMoney($allRecords->where('status', 'approved')->sum(fn ($r) => $r->loan_adjustment_amount->getMinorAmount())),
            'approved_count' => $allRecords->where('status', 'approved')->count(),
            'pending_count' => $allRecords->where('status', 'pending')->count(),
            'daily_flow' => $this->formatMoney($allRecords->where('transaction_date', '>=', $this->selectedDate ? Carbon::parse($this->selectedDate)->startOfDay() : now()->startOfMonth())->sum(fn ($r) => $r->amount_withdrawn->getMinorAmount())),
        ];

        return view('livewire.savings-withdrawal-register', [
            'groupedRecords' => $groupedRecords,
            'stats' => $stats,
        ])->layout('layouts.app', ['title' => 'Savings Withdrawal Ledger']);
    }

    private function formatMoney($minorAmount)
    {
        $org = Organization::current();
        $currency = $org->currency_code ?? 'NGN';

        return (new Money($minorAmount, $currency))->format();
    }
}
