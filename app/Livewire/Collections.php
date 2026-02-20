<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Collections extends Component
{
    use WithPagination;

    public $filter = 'today';

    public $showSummary = false;

    public $stats = [];

    public function mount()
    {
        if (auth()->user()->hasRole('Collection Officer')) {
            $this->showSummary = false;
        }
        $this->calculateStats();
    }

    public function updatedFilter()
    {
        $this->calculateStats();
    }

    public function calculateStats()
    {
        $dates = $this->getDateRange($this->filter);
        $prevDates = $this->getPreviousDateRange($this->filter);

        // 1. Total Overdue (Snapshot - Current)
        // Since we don't track historical overdue snapshots, we use the current live state for the value.
        // For comparison, we'll just leave it static or 0 for now as requested by user logic limitations.
        $currentOverdue = Loan::where('status', 'overdue')->sum('amount'); // Or status 'repayment'? User said "Overdue".

        // 2. Collected (Flow - Dynamic based on date)
        $collectedCurrent = Repayment::whereBetween('paid_at', [$dates['start'], $dates['end']])->sum('amount');
        $collectedPrev = Repayment::whereBetween('paid_at', [$prevDates['start'], $prevDates['end']])->sum('amount');

        $collectedChange = $collectedPrev > 0 ? (($collectedCurrent - $collectedPrev) / $collectedPrev) * 100 : 0;

        // 3. Recovery Rate
        // Logic: Collected / (Collected + Remaining Overdue) roughly represents what % of the "at risk" pot was recovered.
        $totalAtRisk = $collectedCurrent + $currentOverdue;
        $recoveryRate = $totalAtRisk > 0 ? ($collectedCurrent / $totalAtRisk) * 100 : 0;

        // Prev Recovery Rate (Approximate)
        $prevOverdue = $currentOverdue; // Assuming static overdue for prev calc to avoid complexity
        $prevTotalAtRisk = $collectedPrev + $prevOverdue;
        $prevRecoveryRate = $prevTotalAtRisk > 0 ? ($collectedPrev / $prevTotalAtRisk) * 100 : 0;
        $recoveryChange = $prevRecoveryRate > 0 ? $recoveryRate - $prevRecoveryRate : 0; // Point difference

        $this->stats = [
            'overdue' => [
                'value' => $currentOverdue,
                'change' => 0, // Static
                'count' => Loan::where('status', 'overdue')->count(),
            ],
            'collected' => [
                'value' => $collectedCurrent,
                'change' => $collectedChange,
                'count' => Repayment::whereBetween('paid_at', [$dates['start'], $dates['end']])->count(),
            ],
            'recovery' => [
                'value' => $recoveryRate,
                'change' => $recoveryChange,
            ],
        ];
    }

    private function getDateRange($filter)
    {
        $now = Carbon::now();

        return match ($filter) {
            'today' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'yesterday' => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            'this_week' => ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()],
            'last_week' => ['start' => $now->copy()->subWeek()->startOfWeek(), 'end' => $now->copy()->subWeek()->endOfWeek()],
            'this_month' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'last_month' => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            'this_year' => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            'last_year' => ['start' => $now->copy()->subYear()->startOfYear(), 'end' => $now->copy()->subYear()->endOfYear()],
            default => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
        };
    }

    private function getPreviousDateRange($filter)
    {
        $dates = $this->getDateRange($filter);
        $diff = $dates['start']->diffInDays($dates['end']) + 1;

        return [
            'start' => $dates['start']->copy()->subDays($diff),
            'end' => $dates['end']->copy()->subDays($diff),
        ];
    }

    public function render()
    {
        $overdueLoans = Loan::with(['borrower.user'])
            ->where('status', 'overdue')
            ->latest()
            ->paginate(10);

        return view('livewire.collections', [
            'overdueLoans' => $overdueLoans,
        ])->layout('layouts.app', ['title' => 'Collections']);
    }
}
