<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class StatusBoard extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $riskFilter = '';
    public $dateFilter = '';

    public $counts = [];
    public $sums = [];
    public $totalPipelineValue = 0;

    // Board specific collections (non-paginated for Kanban)
    public $pending;
    public $active;
    public $repaid;
    public $overdue;
    public $declined;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingRiskFilter() { $this->resetPage(); }
    public function updatingDateFilter() { $this->resetPage(); }

    public function mount()
    {
        // Initial fetch handled by render
    }

    private function applyFilters($query)
    {
        // Search Logic
        if ($this->search) {
            $query->where(function($q) {
                $q->where('loan_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('borrower.user', function($bq) {
                      $bq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('borrower', function($bq) {
                      $bq->where('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('bvn', 'like', '%' . $this->search . '%')
                        ->orWhere('national_identity_number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Status Filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Date Filter
        if ($this->dateFilter) {
            match($this->dateFilter) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => null
            };
        }

        // Risk Filter logic (approximate based on credit score ranges used in getRiskLevel)
        if ($this->riskFilter) {
            $query->whereHas('borrower', function($q) {
                match($this->riskFilter) {
                    'low' => $q->where('credit_score', '>=', 750),
                    'medium' => $q->where('credit_score', '>=', 600)->where('credit_score', '<', 750),
                    'high' => $q->where('credit_score', '<', 600),
                    default => null
                };
            });
        }

        return $query;
    }

    public function fetchBoardData()
    {
        $baseQuery = Loan::with(['borrower.user', 'collateral', 'repayments']);
        $this->applyFilters($baseQuery);

        $this->pending = (clone $baseQuery)->whereIn('status', ['applied', 'verification_pending'])->latest()->get();
        $this->active = (clone $baseQuery)->whereIn('status', ['approved', 'active'])->latest()->get();
        $this->repaid = (clone $baseQuery)->where('status', 'repaid')->latest()->get();
        $this->overdue = (clone $baseQuery)->where('status', 'overdue')->latest()->get();
        $this->declined = (clone $baseQuery)->where('status', 'declined')->latest()->get();

        $this->calculateMetrics();
    }

    public function calculateMetrics()
    {
        $this->counts = [
            'pending' => $this->pending->count(),
            'active' => $this->active->count(),
            'repaid' => $this->repaid->count(),
            'overdue' => $this->overdue->count(),
            'declined' => $this->declined->count(),
        ];

        $this->sums = [
            'pending' => $this->pending->sum('amount'),
            'active' => $this->active->sum('amount'),
            'repaid' => $this->repaid->sum('amount'),
            'overdue' => $this->overdue->sum('amount'),
            'declined' => $this->declined->sum('amount'),
        ];

        $this->totalPipelineValue = 
            $this->sums['pending'] +
            $this->sums['active'];
    }

    public function getRiskLevel($score)
    {
        if ($score >= 750) return ['label' => 'Low Risk', 'color' => 'green'];
        if ($score >= 600) return ['label' => 'Medium Risk', 'color' => 'yellow'];
        return ['label' => 'High Risk', 'color' => 'red'];
    }

    public function render()
    {
        // Fetch Board Data (Card View) with filters
        $this->fetchBoardData();

        // Fetch List Data with filters
        $query = Loan::with(['borrower.user', 'collateral', 'repayments']);
        $this->applyFilters($query);

        return view('livewire.status-board', [
            'allLoans' => $query->latest()->paginate(15)
        ])->layout('layouts.app');
    }
}

