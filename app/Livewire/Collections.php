<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Portfolio;
use App\Models\Repayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Collections extends Component
{
    use WithPagination;

    public $filter = 'today';

    public $showSummary = false;

    public $stats = [];

    public $portfolioId = null;

    public $portfolios = [];

    protected $updatesQueryString = ['filter', 'portfolioId'];

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }

        if ($user->hasRole('Collection Officer')) {
            $this->showSummary = false;
        }
        $this->calculateStats();
    }

    public function updatedFilter()
    {
        $this->calculateStats();
    }

    public function updatedPortfolioId()
    {
        $this->resetPage();
        $this->calculateStats();
    }

    public function calculateStats()
    {
        $dates = $this->getDateRange($this->filter);
        $prevDates = $this->getPreviousDateRange($this->filter);

        $loanQuery = Loan::query();
        $repaymentQuery = Repayment::query();

        if ($this->portfolioId) {
            $loanQuery->where('portfolio_id', $this->portfolioId);
            $repaymentQuery->whereHas('loan', fn ($q) => $q->where('portfolio_id', $this->portfolioId));
        }

        $currentOverdue = (clone $loanQuery)->where('status', 'overdue')->sum('amount');

        $collectedCurrent = (clone $repaymentQuery)->whereBetween('paid_at', [$dates['start'], $dates['end']])->sum('amount');
        $collectedPrev = (clone $repaymentQuery)->whereBetween('paid_at', [$prevDates['start'], $prevDates['end']])->sum('amount');

        $collectedChange = $collectedPrev > 0 ? (($collectedCurrent - $collectedPrev) / $collectedPrev) * 100 : 0;

        $totalAtRisk = $collectedCurrent + $currentOverdue;
        $recoveryRate = $totalAtRisk > 0 ? ($collectedCurrent / $totalAtRisk) * 100 : 0;

        $prevOverdue = $currentOverdue;
        $prevTotalAtRisk = $collectedPrev + $prevOverdue;
        $prevRecoveryRate = $prevTotalAtRisk > 0 ? ($collectedPrev / $prevTotalAtRisk) * 100 : 0;
        $recoveryChange = $prevRecoveryRate > 0 ? $recoveryRate - $prevRecoveryRate : 0;

        $this->stats = [
            'overdue' => [
                'value' => $currentOverdue,
                'change' => 0,
                'count' => (clone $loanQuery)->where('status', 'overdue')->count(),
            ],
            'collected' => [
                'value' => $collectedCurrent,
                'change' => $collectedChange,
                'count' => (clone $repaymentQuery)->whereBetween('paid_at', [$dates['start'], $dates['end']])->count(),
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
        $query = Loan::with(['borrower.user'])
            ->where('status', 'overdue');

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
        }

        $overdueLoans = $query->latest()->paginate(10);

        return view('livewire.collections', [
            'overdueLoans' => $overdueLoans,
        ])->layout('layouts.app', ['title' => 'Collections']);
    }
}
