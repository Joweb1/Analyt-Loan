<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\SystemNotification;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminDashboard extends Component
{
    public ?Money $totalLoaned = null;

    public ?Money $totalCollected = null;

    public $totalCustomers = 0;

    public $activeLoansCount = 0;

    public $pendingApplicationsCount = 0;

    public $paidLoansCount = 0;

    public $defaultedLoansCount = 0;

    // Portfolio Metrics
    public ?Money $portfolioBalance = null;

    public ?Money $savingsBalance = null;

    public ?Money $portfolioAtRisk = null;

    public $parPercentage = 0;

    public ?Money $profitLoss = null;

    // Chart Data
    public ?Money $activeAmount = null;

    public ?Money $repaidAmount = null;

    public ?Money $overdueAmount = null;

    // Pulse Trend Data
    public $pulseData = [];

    public $actionItems = [];

    // Portfolio Selection
    public $portfolios = [];

    public $selectedPortfolioId = null;

    public static function clearCache(string $orgId, ?string $portfolioId = null): void
    {
        \Illuminate\Support\Facades\Cache::forget("admin_dashboard_stats_v2_{$orgId}_all");
        if ($portfolioId) {
            \Illuminate\Support\Facades\Cache::forget("admin_dashboard_stats_v2_{$orgId}_{$portfolioId}");
        }
    }

    public function getListeners()
    {
        $orgId = Auth::user()->organization_id;

        return [
            "echo:organization.{$orgId},.dashboard.updated" => 'loadStatsAndForce',
            'echo:dashboard,.dashboard.updated' => 'loadStatsAndForce',
        ];
    }

    public function loadStatsAndForce()
    {
        $this->loadStats(true);
    }

    public function mount()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        // Load portfolios for the selector
        if ($user->hasRole('Admin') || $user->isOrgOwner() || $user->isAppOwner()) {
            $this->portfolios = Portfolio::all();
        } else {
            $this->portfolios = $user->portfolios;
        }

        $this->loadStats();
    }

    public function updatedSelectedPortfolioId()
    {
        $this->loadStats();
    }

    public function loadStats($force = false)
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $isOwner = $user->isAppOwner();

        $cacheKey = "admin_dashboard_stats_v2_{$orgId}_".($this->selectedPortfolioId ?? 'all');

        if ($force) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () use ($orgId, $isOwner) {
            $org = \App\Models\Organization::current();
            // Base queries
            $loanQuery = Loan::query();
            $repaymentQuery = Repayment::query();
            $borrowerQuery = Borrower::query();

            if ($isOwner) {
                $loanQuery->withoutGlobalScopes();
                $repaymentQuery->withoutGlobalScopes();
                $borrowerQuery->withoutGlobalScopes();
            } else {
                $loanQuery->where('organization_id', $orgId);
                $repaymentQuery->whereHas('loan', fn ($q) => $q->where('organization_id', $orgId));
                $borrowerQuery->where('organization_id', $orgId);
            }

            // Apply Portfolio Filter if selected
            $portfolioData = [
                'portfolioBalance' => 0,
                'savingsBalance' => 0,
                'portfolioAtRisk' => 0,
                'parPercentage' => 0,
                'profitLoss' => 0,
            ];

            if ($this->selectedPortfolioId) {
                $loanQuery->where('portfolio_id', $this->selectedPortfolioId);
                $repaymentQuery->whereHas('loan', fn ($q) => $q->where('portfolio_id', $this->selectedPortfolioId));
                $borrowerQuery->where('portfolio_id', $this->selectedPortfolioId);

                // Fetch specific portfolio metrics from model
                $portfolio = Portfolio::find($this->selectedPortfolioId);
                if ($portfolio) {
                    $portfolioData = [
                        'portfolioBalance' => $portfolio->portfolio_balance,
                        'savingsBalance' => $portfolio->savings_balance,
                        'portfolioAtRisk' => $portfolio->portfolio_at_risk,
                        'parPercentage' => (float) $portfolio->par_percentage,
                        'profitLoss' => $portfolio->profit_loss,
                    ];
                }
            } else {
                // Organization-wide metrics
                if ($org) {
                    $currency = $org->currency_code ?: config('app.currency', 'NGN');
                    $portfolios = Portfolio::where('organization_id', $orgId)->get();

                    $totalPARMinor = (int) $portfolios->sum(function ($p) {
                        return $p->portfolio_at_risk->getMinorAmount();
                    });
                    $totalPnLMinor = (int) $portfolios->sum(function ($p) {
                        return $p->profit_loss->getMinorAmount();
                    });

                    $portfolioData = [
                        'portfolioBalance' => $org->organization_balance,
                        'savingsBalance' => new \App\ValueObjects\Money((int) SavingsAccount::where('organization_id', $orgId)->sum('balance'), $currency),
                        'portfolioAtRisk' => new \App\ValueObjects\Money($totalPARMinor, $currency),
                        'parPercentage' => 0, // Calculated below
                        'profitLoss' => new \App\ValueObjects\Money($totalPnLMinor, $currency),
                    ];
                    if (! $portfolioData['portfolioBalance']->isZero()) {
                        $portfolioData['parPercentage'] = ($portfolioData['portfolioAtRisk']->getMajorAmount() / $portfolioData['portfolioBalance']->getMajorAmount()) * 100;
                    }
                }
            }

            // Fetch Last 7 Days Pulse
            $startDate = \App\Models\Organization::systemNow()->subDays(6)->startOfDay();
            $pulseRepayments = (clone $repaymentQuery)
                ->where('paid_at', '>=', $startDate)
                ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
                ->groupBy('paid_date')
                ->get()
                ->pluck('total', 'paid_date');

            $currency = $org->currency_code ?: config('app.currency', 'NGN');
            $pulseData = collect(range(6, 0))->map(function ($daysAgo) use ($pulseRepayments, $currency) {
                $date = \App\Models\Organization::systemNow()->subDays($daysAgo);
                $dateKey = $date->format('Y-m-d');
                $amountMinor = (int) $pulseRepayments->get($dateKey, 0);
                $money = new \App\ValueObjects\Money($amountMinor, $currency);

                return [
                    'day' => $date->format('D'),
                    'amount' => $money->getMajorAmount(),
                    'formatted' => $money->format(),
                ];
            })->toArray();

            $totalLoaned = $portfolioData['portfolioBalance'];
            $totalCollectedMinor = (int) (clone $repaymentQuery)->sum('amount');
            $totalCollected = new \App\ValueObjects\Money($totalCollectedMinor, $currency);

            $activeAmountMinor = (int) (clone $loanQuery)->whereIn('status', ['approved', 'active'])->sum('amount');
            $activeAmount = new \App\ValueObjects\Money($activeAmountMinor, $currency);

            $repaidAmountMinor = (int) (clone $loanQuery)->where('status', 'repaid')->sum('amount');
            $repaidAmount = new \App\ValueObjects\Money($repaidAmountMinor, $currency);

            $overdueAmountMinor = (int) (clone $loanQuery)->where('status', 'overdue')->sum('amount');
            $overdueAmount = new \App\ValueObjects\Money($overdueAmountMinor, $currency);

            $res = array_merge([
                'totalLoaned' => $totalLoaned,
                'totalCollected' => $totalCollected,
                'totalCustomers' => (clone $borrowerQuery)->count(),
                'activeLoansCount' => (clone $loanQuery)->whereIn('status', ['approved', 'active'])->count(),
                'pendingApplicationsCount' => (clone $loanQuery)->whereIn('status', ['applied', 'verification_pending'])->count(),
                'paidLoansCount' => (clone $loanQuery)->where('status', 'repaid')->count(),
                'defaultedLoansCount' => (clone $loanQuery)->where('status', 'overdue')->count(),
                'activeAmount' => $activeAmount,
                'repaidAmount' => $repaidAmount,
                'overdueAmount' => $overdueAmount,
                'pulseData' => $pulseData,
            ], $portfolioData);

            return $res;
        });

        foreach ($stats as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        // Action Box Items & Task Generation
        if (! $isOwner) {
            $currentDateStr = \App\Models\Organization::systemNow()->toDateString();
            \Illuminate\Support\Facades\Cache::remember("daily_tasks_run_{$orgId}_{$currentDateStr}", now()->addHour(), function () use ($orgId) {
                \App\Services\ActionTaskService::generateDailyTasks($orgId);

                return true;
            });
        }

        $this->loadActionItems($isOwner ? null : $orgId);
    }

    public function loadActionItems($orgId)
    {
        $user = Auth::user();
        $query = SystemNotification::where('is_actionable', true)
            ->whereNull('read_at');

        if ($orgId) {
            $query->where('organization_id', $orgId)
                ->where(function ($q) use ($user) {
                    $q->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                });
        }

        $this->actionItems = $query->latest()
            ->take(5)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->category,
                    'title' => $notif->title,
                    'subtitle' => $notif->message,
                    'link' => $notif->action_link ?? '#',
                    'priority' => $notif->priority === 'high' || $notif->priority === 'critical' ? 'urgent' : 'normal',
                    'time' => $notif->created_at->diffForHumans(),
                ];
            })->toArray();
    }

    public function render()
    {
        return view('livewire.admin-dashboard')->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
