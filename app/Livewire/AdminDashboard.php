<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminDashboard extends Component
{
    public $totalLoaned = 0;

    public $totalCollected = 0;

    public $totalCustomers = 0;

    public $activeLoansCount = 0;

    public $pendingApplicationsCount = 0;

    public $paidLoansCount = 0;

    public $defaultedLoansCount = 0;

    // Chart Data
    public $activeAmount = 0;

    public $repaidAmount = 0;

    public $overdueAmount = 0;

    // Pulse Trend Data
    public $pulseData = [];

    public $actionItems = [];

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

        $this->totalLoaned = (clone $loanQuery)->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])->sum('amount');
        $this->totalCollected = (clone $repaymentQuery)->sum('amount');
        $this->totalCustomers = (clone $borrowerQuery)->count();

        $this->activeLoansCount = (clone $loanQuery)->whereIn('status', ['approved', 'active'])->count();
        $this->pendingApplicationsCount = (clone $loanQuery)->whereIn('status', ['applied', 'verification_pending'])->count();
        $this->paidLoansCount = (clone $loanQuery)->where('status', 'repaid')->count();
        $this->defaultedLoansCount = (clone $loanQuery)->where('status', 'overdue')->count();

        $this->activeAmount = (clone $loanQuery)->whereIn('status', ['approved', 'active'])->sum('amount');
        $this->repaidAmount = (clone $loanQuery)->where('status', 'repaid')->sum('amount');
        $this->overdueAmount = (clone $loanQuery)->where('status', 'overdue')->sum('amount');

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

        // Cache task generation to run only once every hour per organization
        if (! $isOwner) {
            \Illuminate\Support\Facades\Cache::remember("daily_tasks_run_{$orgId}", now()->addHour(), function () use ($orgId) {
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
