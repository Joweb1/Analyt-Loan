<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Borrower;
use App\Models\Repayment;
use App\Models\SystemNotification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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
    
    public $actionItems = [];

    public function mount()
    {
        $orgId = Auth::user()->organization_id;

        $this->totalLoaned = Loan::where('organization_id', $orgId)->sum('amount');
        
        $this->totalCollected = Repayment::whereHas('loan', function ($query) use ($orgId) {
            $query->where('organization_id', $orgId);
        })->sum('amount');

        $this->totalCustomers = Borrower::where('organization_id', $orgId)->count();
        
        $this->activeLoansCount = Loan::where('organization_id', $orgId)->where('status', 'active')->count();
        $this->paidLoansCount = Loan::where('organization_id', $orgId)->where('status', 'repaid')->count();
        $this->defaultedLoansCount = Loan::where('organization_id', $orgId)->where('status', 'overdue')->count();

        $this->activeAmount = Loan::where('organization_id', $orgId)->where('status', 'active')->sum('amount');
        $this->repaidAmount = Loan::where('organization_id', $orgId)->where('status', 'repaid')->sum('amount');
        $this->overdueAmount = Loan::where('organization_id', $orgId)->where('status', 'overdue')->sum('amount');

        \App\Services\ActionTaskService::generateDailyTasks($orgId);

        $this->loadActionItems($orgId);
    }

    public function loadActionItems($orgId)
    {
        // Query real "Actions" from system_notifications table
        $this->actionItems = SystemNotification::where('organization_id', $orgId)
            ->where('is_actionable', true)
            ->whereNull('read_at')
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
        return view('livewire.admin-dashboard')->layout('layouts.app');
    }
}
