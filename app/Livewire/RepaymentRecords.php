<?php

namespace App\Livewire;

use App\Models\Portfolio;
use App\Models\Repayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class RepaymentRecords extends Component
{
    use WithPagination;

    public $dateRange = 'all';

    public $customStartDate;

    public $customEndDate;

    public $search = '';

    public $portfolioId = null;

    public $portfolios = [];

    protected $updatesQueryString = ['search', 'dateRange', 'portfolioId'];

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function updatingPortfolioId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $query = Repayment::query()
            ->with(['loan.borrower.user', 'collector'])
            ->whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
                if ($this->portfolioId) {
                    $q->where('portfolio_id', $this->portfolioId);
                }
            });

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('loan.borrower.user', function ($uq) {
                    $uq->where('name', 'like', '%'.$this->search.'%');
                })
                    ->orWhereHas('loan', function ($lq) {
                        $lq->where('loan_number', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Advanced Date Filtering
        $this->applyDateFilter($query);

        $repayments = $query->latest('paid_at')->paginate(15);

        // Stats
        $statsQuery = Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
            if ($this->portfolioId) {
                $q->where('portfolio_id', $this->portfolioId);
            }
        });
        $this->applyDateFilter($statsQuery);

        $totalAmount = $statsQuery->sum('amount') / 100;
        $totalCount = $statsQuery->count();

        return view('livewire.repayment-records', [
            'repayments' => $repayments,
            'totalAmount' => $totalAmount,
            'totalCount' => $totalCount,
        ])->layout('layouts.app', ['title' => 'Repayment Records']);
    }

    private function applyDateFilter($query)
    {
        $now = Carbon::now();

        match ($this->dateRange) {
            'today' => $query->whereDate('paid_at', today()),
            'yesterday' => $query->whereDate('paid_at', today()->subDay()),
            'this_week' => $query->whereBetween('paid_at', [$now->startOfWeek()->format('Y-m-d'), $now->endOfWeek()->format('Y-m-d')]),
            'last_week' => $query->whereBetween('paid_at', [
                $now->copy()->subWeek()->startOfWeek()->format('Y-m-d'),
                $now->copy()->subWeek()->endOfWeek()->format('Y-m-d'),
            ]),
            'this_month' => $query->whereMonth('paid_at', $now->month)->whereYear('paid_at', $now->year),
            'last_month' => $query->whereMonth('paid_at', $now->copy()->subMonth()->month)->whereYear('paid_at', $now->copy()->subMonth()->year),
            'this_year' => $query->whereYear('paid_at', $now->year),
            'last_year' => $query->whereYear('paid_at', $now->copy()->subYear()->year),
            'custom' => $this->applyCustomDates($query),
            default => null
        };
    }

    private function applyCustomDates($query)
    {
        if ($this->customStartDate && $this->customEndDate) {
            $query->whereBetween('paid_at', [$this->customStartDate, $this->customEndDate]);
        } elseif ($this->customStartDate) {
            $query->where('paid_at', '>=', $this->customStartDate);
        } elseif ($this->customEndDate) {
            $query->where('paid_at', '<=', $this->customEndDate);
        }
    }

    public function export()
    {
        if (! Auth::user()->hasPermissionTo('export_and_print')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to export data.']);

            return;
        }

        $orgId = Auth::user()->organization_id;
        $query = Repayment::query()
            ->with(['loan.borrower.user', 'collector'])
            ->whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
                if ($this->portfolioId) {
                    $q->where('portfolio_id', $this->portfolioId);
                }
            });

        // Apply same filters as render
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('loan.borrower.user', function ($uq) {
                    $uq->where('name', 'like', '%'.$this->search.'%');
                })
                    ->orWhereHas('loan', function ($lq) {
                        $lq->where('loan_number', 'like', '%'.$this->search.'%');
                    });
            });
        }
        $this->applyDateFilter($query);

        $repayments = $query->latest('paid_at')->get();

        $filename = 'repayments_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($repayments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Borrower', 'Loan ID', 'Amount', 'Method', 'Principal Split', 'Interest Split', 'Extra', 'Date', 'Recorded By']);

            foreach ($repayments as $row) {
                fputcsv($file, [
                    $row->loan->borrower->user->name,
                    $row->loan->loan_number,
                    $row->amount,
                    $row->payment_method,
                    $row->principal_amount,
                    $row->interest_amount,
                    $row->extra_amount,
                    $row->paid_at->format('Y-m-d'),
                    $row->collector->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
