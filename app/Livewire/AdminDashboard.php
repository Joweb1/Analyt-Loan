<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\ActionTaskService;
use App\Services\CashbookService;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AdminDashboard extends Component
{
    public ?Money $totalLoaned = null;

    public ?Money $totalCollected = null;

    public $totalCustomers = 0;

    public $borrowersCount = 0;

    public $saversCount = 0;

    public $guarantorsCount = 0;

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

    public ?Money $accountBalance = null;

    // Pulse Trend Data
    public $pulseData = [];

    public $actionItems = [];

    // Portfolio Selection
    public $portfolios = [];

    public $selectedPortfolioId = null;

    public static function clearCache(string $orgId, ?string $portfolioId = null): void
    {
        Cache::forget("admin_dashboard_stats_v2_{$orgId}_all");
        if ($portfolioId) {
            Cache::forget("admin_dashboard_stats_v2_{$orgId}_{$portfolioId}");
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
            if ($user->isAppOwner()) {
                $this->portfolios = Portfolio::withoutGlobalScopes()->get();
            } else {
                $this->portfolios = Portfolio::where('organization_id', $orgId)->get();
            }
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
            Cache::forget($cacheKey);
        }

        $stats = Cache::remember($cacheKey, now()->addHour(), function () use ($orgId, $isOwner) {
            // Robustly get organization context
            $org = $orgId ? Organization::find($orgId) : Organization::current();

            // Base queries
            $loanQuery = Loan::query();
            $repaymentQuery = Repayment::query();
            $customerQuery = User::where('type', 'customer');

            if ($isOwner) {
                $loanQuery->withoutGlobalScopes();
                $repaymentQuery->withoutGlobalScopes();
                $customerQuery->withoutGlobalScopes();
            } else {
                if ($orgId) {
                    $loanQuery->where('organization_id', $orgId);
                    $repaymentQuery->whereHas('loan', fn ($q) => $q->where('organization_id', $orgId));
                    $customerQuery->where('organization_id', $orgId);
                }
            }

            // Apply Portfolio Filter if selected
            $portfolioData = [
                'portfolioBalance' => new Money(0, $org->currency_code ?? 'NGN'),
                'savingsBalance' => new Money(0, $org->currency_code ?? 'NGN'),
                'portfolioAtRisk' => new Money(0, $org->currency_code ?? 'NGN'),
                'parPercentage' => 0,
                'profitLoss' => new Money(0, $org->currency_code ?? 'NGN'),
            ];

            if ($this->selectedPortfolioId) {
                $loanQuery->where('portfolio_id', $this->selectedPortfolioId);
                $repaymentQuery->whereHas('loan', fn ($q) => $q->where('portfolio_id', $this->selectedPortfolioId));
                $customerQuery->where(function ($q) {
                    $q->whereHas('borrower', fn ($bq) => $bq->where('portfolio_id', $this->selectedPortfolioId))
                        ->orWhereHas('saver', fn ($sq) => $sq->where('portfolio_id', $this->selectedPortfolioId))
                        ->orWhereHas('guarantor', fn ($gq) => $gq->where('portfolio_id', $this->selectedPortfolioId));
                });

                // Fetch specific portfolio metrics from model
                $portfolio = $isOwner
                    ? Portfolio::withoutGlobalScopes()->find($this->selectedPortfolioId)
                    : Portfolio::find($this->selectedPortfolioId);

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
                    $portfolios = Portfolio::where('organization_id', $org->id)->get();

                    $totalPARMinor = (int) $portfolios->sum(function ($p) {
                        return $p->portfolio_at_risk->getMinorAmount();
                    });
                    $totalPnLMinor = (int) $portfolios->sum(function ($p) {
                        return $p->profit_loss->getMinorAmount();
                    });

                    $portfolioData = [
                        'portfolioBalance' => $org->organization_balance,
                        'savingsBalance' => new Money((int) SavingsAccount::where('organization_id', $org->id)->sum('balance'), $currency),
                        'portfolioAtRisk' => new Money($totalPARMinor, $currency),
                        'parPercentage' => 0, // Calculated below
                        'profitLoss' => new Money($totalPnLMinor, $currency),
                    ];
                    if (! $portfolioData['portfolioBalance']->isZero()) {
                        $portfolioData['parPercentage'] = ($portfolioData['portfolioAtRisk']->getMajorAmount() / $portfolioData['portfolioBalance']->getMajorAmount()) * 100;
                    }
                }
            }

            // Fetch Last 7 Days Pulse
            $startDate = now()->subDays(6)->startOfDay();
            $pulseRepayments = (clone $repaymentQuery)
                ->where('paid_at', '>=', $startDate)
                ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
                ->groupBy('paid_date')
                ->get()
                ->pluck('total', 'paid_date');

            $currency = $org->currency_code ?? config('app.currency', 'NGN');
            $pulseData = collect(range(6, 0))->map(function ($daysAgo) use ($pulseRepayments, $currency) {
                $date = now()->subDays($daysAgo);
                $dateKey = $date->format('Y-m-d');
                $amountMinor = (int) $pulseRepayments->get($dateKey, 0);
                $money = new Money($amountMinor, $currency);

                return [
                    'day' => $date->format('D'),
                    'amount' => $money->getMajorAmount(),
                    'formatted' => $money->format(),
                ];
            })->toArray();

            $totalLoaned = $portfolioData['portfolioBalance'];
            $totalCollectedMinor = (int) (clone $repaymentQuery)->sum('amount');
            $totalCollected = new Money($totalCollectedMinor, $currency);

            $activeAmountMinor = (int) (clone $loanQuery)->whereIn('status', ['approved', 'active'])->sum('amount');
            $activeAmount = new Money($activeAmountMinor, $currency);

            $repaidAmountMinor = (int) (clone $loanQuery)->where('status', 'repaid')->sum('amount');
            $repaidAmount = new Money($repaidAmountMinor, $currency);

            $overdueAmountMinor = (int) (clone $loanQuery)->where('status', 'overdue')->sum('amount');
            $overdueAmount = new Money($overdueAmountMinor, $currency);

            // Detailed Customer Counts by Role
            $borrowerQuery = User::where('type', 'customer')->role('Borrower');
            $saverQuery = User::where('type', 'customer')->role('Saver');
            $guarantorQuery = User::where('type', 'customer')->role('Guarantor');

            if ($isOwner) {
                $borrowerQuery->withoutGlobalScopes();
                $saverQuery->withoutGlobalScopes();
                $guarantorQuery->withoutGlobalScopes();
            } elseif ($orgId) {
                $borrowerQuery->where('organization_id', $orgId);
                $saverQuery->where('organization_id', $orgId);
                $guarantorQuery->where('organization_id', $orgId);
            }

            $accountBalance = null;
            if ($org) {
                $accountBalance = app(CashbookService::class)->getLiveAccountBalance($org->getSystemTime(), $org);
            }

            $res = array_merge([
                'totalLoaned' => $totalLoaned,
                'totalCollected' => $totalCollected,
                'totalCustomers' => (clone $customerQuery)->count(),
                'borrowersCount' => $borrowerQuery->count(),
                'saversCount' => $saverQuery->count(),
                'guarantorsCount' => $guarantorQuery->count(),
                'activeLoansCount' => (clone $loanQuery)->whereIn('status', ['approved', 'active'])->count(),
                'pendingApplicationsCount' => (clone $loanQuery)->whereIn('status', ['applied', 'verification_pending'])->count(),
                'paidLoansCount' => (clone $loanQuery)->where('status', 'repaid')->count(),
                'defaultedLoansCount' => (clone $loanQuery)->where('status', 'overdue')->count(),
                'activeAmount' => $activeAmount,
                'repaidAmount' => $repaidAmount,
                'overdueAmount' => $overdueAmount,
                'accountBalance' => $accountBalance,
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
            $currentDateStr = now()->toDateString();
            Cache::remember("daily_tasks_run_{$orgId}_{$currentDateStr}", now()->addHour(), function () use ($orgId) {
                ActionTaskService::generateDailyTasks($orgId);

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
