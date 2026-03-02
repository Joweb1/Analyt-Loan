<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoanDashboard extends Component
{
    public $repaidToday = 0;

    public $overdueAmount = 0;

    public $totalLent = 0;

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

    public $activeAmount = 0;

    public $repaidAmount = 0;

    public $overdueAmountTotal = 0;

    public function mount()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $isOwner = $user->isAppOwner();

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

        $this->repaidToday = (clone $repaymentQuery)->whereDate('paid_at', today())->sum('amount');
        $this->overdueAmount = (clone $loanQuery)->where('status', 'overdue')->sum('amount');
        $this->totalLent = (clone $loanQuery)->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])->whereMonth('created_at', now()->month)->sum('amount');
        $this->activeCustomers = (clone $borrowerQuery)->count();

        // Fetch Last 7 Days Pulse
        $startDate = now()->subDays(6)->startOfDay();
        $pulseRepayments = (clone $repaymentQuery)
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
            ->groupBy('paid_date')
            ->get()
            ->pluck('total', 'paid_date');

        $this->pulseData = collect(range(6, 0))->map(function ($daysAgo) use ($pulseRepayments) {
            $date = now()->subDays($daysAgo);
            $dateKey = $date->format('Y-m-d');
            $amount = $pulseRepayments->get($dateKey, 0);

            return [
                'day' => $date->format('D'),
                'amount' => (float) $amount,
                'formatted' => number_format($amount, 0),
            ];
        })->toArray();

        $this->activeAmount = (clone $loanQuery)->whereIn('status', ['approved', 'active'])->sum('amount');
        $this->repaidAmount = (clone $loanQuery)->where('status', 'repaid')->sum('amount');
        $this->overdueAmountTotal = (clone $loanQuery)->where('status', 'overdue')->sum('amount');

        // Initial Pipeline Calc
        $this->calculatePipeline();

        // Action Box Items
        if (! $isOwner) {
            \Illuminate\Support\Facades\Cache::remember("daily_tasks_run_{$orgId}", now()->addHour(), function () use ($orgId) {
                \App\Services\ActionTaskService::generateDailyTasks($orgId);

                return true;
            });
        }
        $this->loadActionItems($isOwner ? null : $orgId);

        // Chart Data
        $this->loadChartData($isOwner ? null : $orgId);
    }

    public function updatedFilter()
    {
        $this->calculatePipeline();
    }

    public function calculatePipeline()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $isOwner = $user->isAppOwner();

        $startDate = today();
        $endDate = today()->endOfDay();

        switch ($this->filter) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'today':
            default:
                $startDate = today();
                $endDate = today()->endOfDay();
                break;
        }

        $query = Loan::query();
        if ($isOwner) {
            $query->withoutGlobalScopes();
        } else {
            $query->where('organization_id', $orgId);
        }

        $this->pipelineApplied = (clone $query)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $this->pipelineApproved = (clone $query)->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $this->pipelineDeclined = (clone $query)->where('status', 'declined')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
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
        $active = (clone $query)->where('status', 'active')->sum('amount');
        $repaid = (clone $query)->where('status', 'repaid')->sum('amount');
        $overdue = (clone $query)->where('status', 'overdue')->sum('amount');

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
