<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Portfolio;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StatusBoard extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $riskFilter = '';

    #[Url]
    public $dateFilter = '';

    #[Url]
    public $portfolioId = null;

    public $portfolios = [];

    public $counts = [];

    /** @var Money[] */
    public $sums = [];

    public ?Money $totalPipelineValue = null;

    // Board specific collections
    public $pending;

    public $active;

    public $repaid;

    public $overdue;

    public $declined;

    public function mount()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }
    }

    public function updating($property)
    {
        if (in_array($property, ['search', 'statusFilter', 'riskFilter', 'dateFilter', 'portfolioId'])) {
            $this->resetPage();
        }
    }

    private function applyFilters($query)
    {
        if ($this->search) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->where('loan_number', 'like', $term)
                    ->orWhereHas('borrower.user', function ($uq) use ($term) {
                        $uq->whereRaw('LOWER(name) LIKE ?', [$term]);
                    })
                    ->orWhereHas('borrower', function ($bq) use ($term) {
                        $bq->where('phone', 'like', $term)
                            ->orWhere('bvn', 'like', $term)
                            ->orWhere('national_identity_number', 'like', $term)
                            ->orWhere('custom_id', 'like', $term);
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
        }

        if ($this->dateFilter) {
            match ($this->dateFilter) {
                'today' => $query->whereDate('created_at', now()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => null
            };
        }

        if ($this->riskFilter) {
            $query->whereHas('borrower', function ($q) {
                match ($this->riskFilter) {
                    'low' => $q->where('trust_score', '>=', 80),
                    'medium' => $q->where('trust_score', '>=', 50)->where('trust_score', '<', 80),
                    'high' => $q->where('trust_score', '<', 50),
                    default => null
                };
            });
        }

        return $query;
    }

    public function fetchBoardData()
    {
        $user = Auth::user();
        $isOwner = $user->isAppOwner();
        $orgId = $user->organization_id;

        $baseQuery = Loan::with(['borrower.user', 'collateral', 'repayments']);

        if ($isOwner) {
            $baseQuery->withoutGlobalScopes();
        } else {
            $baseQuery->where('organization_id', $orgId);
        }

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
        $currency = Auth::user()->organization->currency_code ?? config('app.currency', 'NGN');

        $this->counts = [
            'pending' => $this->pending->count(),
            'active' => $this->active->count(),
            'repaid' => $this->repaid->count(),
            'overdue' => $this->overdue->count(),
            'declined' => $this->declined->count(),
        ];

        $this->sums = [
            'pending' => new Money((int) $this->pending->sum(fn ($l) => $l->amount->getMinorAmount()), $currency),
            'active' => new Money((int) $this->active->sum(fn ($l) => $l->amount->getMinorAmount()), $currency),
            'repaid' => new Money((int) $this->repaid->sum(fn ($l) => $l->amount->getMinorAmount()), $currency),
            'overdue' => new Money((int) $this->overdue->sum(fn ($l) => $l->amount->getMinorAmount()), $currency),
            'declined' => new Money((int) $this->declined->sum(fn ($l) => $l->amount->getMinorAmount()), $currency),
        ];

        $this->totalPipelineValue = $this->sums['pending']->add($this->sums['active']);
    }

    public function getRiskLevel($score)
    {
        if ($score >= 750) {
            return ['label' => 'Low Risk', 'color' => 'green'];
        }
        if ($score >= 600) {
            return ['label' => 'Medium Risk', 'color' => 'yellow'];
        }

        return ['label' => 'High Risk', 'color' => 'red'];
    }

    public function render()
    {
        $this->fetchBoardData();

        $user = Auth::user();
        $isOwner = $user->isAppOwner();
        $orgId = $user->organization_id;

        $query = Loan::with(['borrower.user', 'collateral', 'repayments']);

        if ($isOwner) {
            $query->withoutGlobalScopes();
        } else {
            $query->where('organization_id', $orgId);
        }

        $this->applyFilters($query);

        return view('livewire.status-board', [
            'allLoans' => $query->latest()->paginate(15),
        ])->layout('layouts.app', ['title' => 'Loan Status Board']);
    }
}
