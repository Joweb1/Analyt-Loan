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
        $orgId = Auth::user()->organization_id;
        $cacheKey = "admin_dashboard_stats_{$orgId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () use ($orgId) {
            $totalLoaned = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['active', 'repaid', 'overdue'])
                ->sum('amount');
            $totalCollected = Repayment::whereHas('loan', function ($query) use ($orgId) {
                $query->where('organization_id', $orgId);
            })->sum('amount');
            $totalCustomers = Borrower::where('organization_id', $orgId)->count();

            $activeLoansCount = Loan::where('organization_id', $orgId)->where('status', 'active')->count();
            $paidLoansCount = Loan::where('organization_id', $orgId)->where('status', 'repaid')->count();
            $defaultedLoansCount = Loan::where('organization_id', $orgId)->where('status', 'overdue')->count();

            $activeAmount = Loan::where('organization_id', $orgId)->where('status', 'active')->sum('amount');
            $repaidAmount = Loan::where('organization_id', $orgId)->where('status', 'repaid')->sum('amount');
            $overdueAmount = Loan::where('organization_id', $orgId)->where('status', 'overdue')->sum('amount');

            // Fetch Last 7 Days Pulse in ONE query
            $startDate = now()->subDays(6)->startOfDay();
            $repayments = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('paid_at', '>=', $startDate)
                ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
                ->groupBy('paid_date')
                ->get()
                ->pluck('total', 'paid_date');

            $pulseData = collect(range(6, 0))->map(function ($daysAgo) use ($repayments) {
                $date = now()->subDays($daysAgo);
                $dateKey = $date->format('Y-m-d');
                $amount = $repayments->get($dateKey, 0);

                return [
                    'day' => $date->format('D'),
                    'amount' => (float) $amount,
                    'formatted' => number_format($amount, 0),
                ];
            })->toArray();

            return compact(
                'totalLoaned',
                'totalCollected',
                'totalCustomers',
                'activeLoansCount',
                'paidLoansCount',
                'defaultedLoansCount',
                'activeAmount',
                'repaidAmount',
                'overdueAmount',
                'pulseData'
            );
        });

        foreach ($stats as $key => $value) {
            $this->{$key} = $value;
        }

        // Cache task generation to run only once every hour per organization
        \Illuminate\Support\Facades\Cache::remember("daily_tasks_run_{$orgId}", now()->addHour(), function () use ($orgId) {
            \App\Services\ActionTaskService::generateDailyTasks($orgId);

            return true;
        });

        $this->loadActionItems($orgId);
    }

    public function loadActionItems($orgId)
    {
        $user = Auth::user();
        // Query real "Actions" from system_notifications table
        $this->actionItems = SystemNotification::where('organization_id', $orgId)
            ->where('is_actionable', true)
            ->whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->whereNull('recipient_id')
                    ->orWhere('recipient_id', $user->id);
            })
            ->latest()
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
