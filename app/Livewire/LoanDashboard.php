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
        $orgId = Auth::user()->organization_id;

        // Static Daily Stats for Cards
        $this->repaidToday = Repayment::whereHas('loan', function ($query) use ($orgId) {
            $query->where('organization_id', $orgId);
        })->whereDate('paid_at', today())->sum('amount');

        $this->overdueAmount = Loan::where('organization_id', $orgId)
            ->where('status', 'overdue')
            ->sum('amount');

        $this->totalLent = Loan::where('organization_id', $orgId)
            ->whereIn('status', ['active', 'repaid'])
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $this->activeCustomers = Borrower::where('organization_id', $orgId)->count();

        // Initial Pipeline Calc
        $this->calculatePipeline();

        // Action Box Items
        \App\Services\ActionTaskService::generateDailyTasks($orgId);
        $this->loadActionItems($orgId);

        // Chart Data
        $this->loadChartData($orgId);

        // Fetch Last 7 Days Pulse (Repayments Trend)
        $this->pulseData = collect(range(6, 0))->map(function ($daysAgo) use ($orgId) {
            $date = now()->subDays($daysAgo)->startOfDay();
            $amount = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })->whereDate('paid_at', $date)->sum('amount');

            return [
                'day' => $date->format('D'),
                'amount' => (float) $amount,
                'formatted' => number_format($amount, 0),
            ];
        })->toArray();

        $this->activeAmount = Loan::where('organization_id', $orgId)->where('status', 'active')->sum('amount');
        $this->repaidAmount = Loan::where('organization_id', $orgId)->where('status', 'repaid')->sum('amount');
        $this->overdueAmountTotal = Loan::where('organization_id', $orgId)->where('status', 'overdue')->sum('amount');
    }

    public function updatedFilter()
    {
        $this->calculatePipeline();
    }

    public function calculatePipeline()
    {
        $orgId = Auth::user()->organization_id;
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

        $this->pipelineApplied = Loan::where('organization_id', $orgId)
            ->where('status', 'applied')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $this->pipelineApproved = Loan::where('organization_id', $orgId)
            ->where('status', 'approved')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $this->pipelineDeclined = Loan::where('organization_id', $orgId)
            ->where('status', 'declined')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
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
                    'type' => $notif->priority === 'critical' || $notif->priority === 'high' ? 'overdue' : 'approval',
                    'message' => $notif->title.': '.$notif->message,
                    'link' => $notif->action_link ?? '#',
                    'date' => $notif->created_at,
                ];
            })->toArray();
    }

    public function loadChartData($orgId)
    {
        // Collection Pulse: Defaulted vs Active vs Refunded (Repaid)
        $active = Loan::where('organization_id', $orgId)->where('status', 'active')->sum('amount');
        $repaid = Loan::where('organization_id', $orgId)->where('status', 'repaid')->sum('amount');
        $overdue = Loan::where('organization_id', $orgId)->where('status', 'overdue')->sum('amount');

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
