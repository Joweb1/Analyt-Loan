<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\SystemNotification;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanDashboard extends Component
{
    public ?Money $repaidToday = null;

    public ?Money $overdueAmount = null;

    public ?Money $totalLent = null;

    public $activeCustomers = 0;

    // Pipeline Stats (Dynamic)
    public $pipelineApplied = 0;

    public $pipelineApproved = 0;

    public $pipelineDeclined = 0;

    public $filter = 'today'; // today, week, month, year

    public $actionItems = [];

    // Chart Data
    public $collectionPulse = [];

    public $pulseData = [];

    public ?Money $activeAmount = null;

    public ?Money $repaidAmount = null;

    public ?Money $overdueAmountTotal = null;

    public static function clearCache(string $orgId): void
    {
        $filters = ['today', 'week', 'month', 'year'];
        foreach ($filters as $f) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_stats_v2_{$orgId}_filter_{$f}");
        }
    }

    public function getListeners()
    {
        $orgId = Auth::user()->organization_id;

        return [
            "echo:organization.{$orgId},.dashboard.updated" => 'refreshStatsAndForce',
            'echo:dashboard,.dashboard.updated' => 'refreshStatsAndForce',
        ];
    }

    public function refreshStatsAndForce()
    {
        $this->refreshStats(true);
    }

    public function mount()
    {
        $this->refreshStats();
    }

    public function updatedFilter()
    {
        $this->refreshStats();
    }

    public function refreshStats($force = false)
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $isOwner = $user->isAppOwner();

        $cacheKey = "dashboard_stats_v2_{$orgId}_filter_{$this->filter}";

        if ($force) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        // With real-time invalidation, we can cache for much longer (e.g., 1 hour)
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () use ($isOwner, $orgId) {
            $startDate = \App\Models\Organization::systemNow()->startOfDay();
            $endDate = \App\Models\Organization::systemNow()->endOfDay();

            switch ($this->filter) {
                case 'week':
                    $startDate = \App\Models\Organization::systemNow()->startOfWeek();
                    $endDate = \App\Models\Organization::systemNow()->endOfWeek();
                    break;
                case 'month':
                    $startDate = \App\Models\Organization::systemNow()->startOfMonth();
                    $endDate = \App\Models\Organization::systemNow()->endOfMonth();
                    break;
                case 'year':
                    $startDate = \App\Models\Organization::systemNow()->startOfYear();
                    $endDate = \App\Models\Organization::systemNow()->endOfYear();
                    break;
                case 'today':
                default:
                    $startDate = \App\Models\Organization::systemNow()->startOfDay();
                    $endDate = \App\Models\Organization::systemNow()->endOfDay();
                    break;
            }

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

            $res = [];
            $org = \App\Models\Organization::current();
            $currency = $org ? $org->currency_code : 'NGN';

            // Repaid in period
            $repaidTodayMinor = (int) ((clone $repaymentQuery)
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('amount'));
            /** @var \App\ValueObjects\Money $repaidToday */
            $repaidToday = new Money($repaidTodayMinor, $currency);
            $res['repaidToday'] = $repaidToday;

            // Overdue Amount
            $overdueAmountMinor = (int) ((clone $loanQuery)
                ->where('status', 'overdue')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->sum('amount'));

            if ($overdueAmountMinor == 0 && $this->filter === 'today') {
                $overdueAmountMinor = (int) ((clone $loanQuery)->where('status', 'overdue')->sum('amount'));
            }
            $res['overdueAmount'] = new Money($overdueAmountMinor, $currency);

            // Total Lent in period
            $totalLentMinor = (int) ((clone $loanQuery)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'));
            $res['totalLent'] = new Money($totalLentMinor, $currency);

            // Active Customers
            $res['activeCustomers'] = (clone $borrowerQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Pipeline Stats
            $res['pipelineApplied'] = (clone $loanQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $res['pipelineApproved'] = (clone $loanQuery)->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $res['pipelineDeclined'] = (clone $loanQuery)->where('status', 'declined')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            // Fetch Last 7 Days Pulse
            $pulseStartDate = \App\Models\Organization::systemNow()->subDays(6)->startOfDay();
            $pulseRepayments = (clone $repaymentQuery)
                ->where('paid_at', '>=', $pulseStartDate)
                ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
                ->groupBy('paid_date')
                ->get()
                ->pluck('total', 'paid_date');

            $res['pulseData'] = collect(range(6, 0))->map(function ($daysAgo) use ($pulseRepayments, $currency) {
                $date = \App\Models\Organization::systemNow()->subDays($daysAgo);
                $dateKey = $date->format('Y-m-d');
                $amountMinor = (int) $pulseRepayments->get($dateKey, 0);
                $money = new Money($amountMinor, $currency);

                return [
                    'day' => $date->format('D'),
                    'amount' => $money->getMajorAmount(),
                    'formatted' => $money->format(),
                ];
            })->toArray();

            $activeAmountMinor = (int) ((clone $loanQuery)->whereIn('status', ['approved', 'active'])->sum('amount'));
            $res['activeAmount'] = new Money($activeAmountMinor, $currency);

            $repaidAmountMinor = (int) ((clone $loanQuery)->where('status', 'repaid')->sum('amount'));
            $res['repaidAmount'] = new Money($repaidAmountMinor, $currency);

            $overdueAmountTotalMinor = (int) ((clone $loanQuery)->where('status', 'overdue')->sum('amount'));
            $res['overdueAmountTotal'] = new Money($overdueAmountTotalMinor, $currency);

            return $res;
        });

        // Hydrate public properties from cached stats
        foreach ($stats as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }

        // Action Box Items & Tasks
        if (! $isOwner) {
            $currentDateStr = \App\Models\Organization::systemNow()->toDateString();
            \Illuminate\Support\Facades\Cache::remember("daily_tasks_run_{$orgId}_{$currentDateStr}", now()->addHour(), function () use ($orgId) {
                \App\Services\ActionTaskService::generateDailyTasks($orgId);

                return true;
            });
        }
        $this->loadActionItems($isOwner ? null : $orgId);
        $this->loadChartData($isOwner ? null : $orgId);
    }

    public function calculatePipeline()
    {
        // Now handled by refreshStats
    }

    public function loadActionItems($orgId)
    {
        $user = Auth::user();
        // Query real "Actions" from system_notifications table
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
                    'type' => $notif->priority === 'critical' || $notif->priority === 'high' ? 'overdue' : 'approval',
                    'message' => $notif->title.': '.$notif->message,
                    'link' => $notif->action_link ?? '#',
                    'date' => $notif->created_at,
                ];
            })->toArray();
    }

    public function loadChartData($orgId = null)
    {
        $query = Loan::query();
        if (! $orgId) {
            $query->withoutGlobalScopes();
        } else {
            $query->where('organization_id', $orgId);
        }

        // Collection Pulse: Defaulted vs Active vs Refunded (Repaid)
        $active = (float) ((clone $query)->where('status', 'active')->sum('amount') / 100);
        $repaid = (float) ((clone $query)->where('status', 'repaid')->sum('amount') / 100);
        $overdue = (float) ((clone $query)->where('status', 'overdue')->sum('amount') / 100);

        $this->collectionPulse = [
            'series' => [$active, $repaid, $overdue],
            'labels' => ['Active', 'Repaid', 'Overdue'],
        ];
    }

    public function render()
    {
        return view('livewire.loan-dashboard')->layout('layouts.app', ['title' => 'Loan Dashboard']);
    }
}
