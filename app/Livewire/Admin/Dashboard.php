<?php

namespace App\Livewire\Admin;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalOrganizations = 0;

    public $totalLent = 0;

    public $totalCollected = 0;

    public $totalUsers = 0;

    public $activeOrganizationsCount = 0;

    public $pendingKycCount = 0;

    // Charts and Trends
    public $orgGrowthData = [];

    public $platformActivity = [];

    public $recentOrganizations = [];

    public $latency = 0;

    public $dbStatus = 'online';

    public function mount()
    {
        if (! Auth::user()->isAppOwner()) {
            abort(403);
        }

        $this->loadStats();
        $this->loadTrends();
        $this->runHealthChecks();
        $this->recentOrganizations = Organization::latest()->take(5)->get();
    }

    public function runHealthChecks()
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            $this->dbStatus = 'online';
        } catch (\Exception $e) {
            $this->dbStatus = 'offline';
        }
        $end = microtime(true);
        $this->latency = round(($end - $start) * 1000, 2);
    }

    public function loadStats()
    {
        $this->totalOrganizations = Organization::count();
        $this->activeOrganizationsCount = Organization::where('status', 'active')->count();
        $this->pendingKycCount = Organization::where('kyc_status', 'pending')->count();

        $this->totalUsers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Borrower');
        })->count();

        // Summing across all organizations
        $this->totalLent = Loan::withoutGlobalScopes()->whereIn('status', ['active', 'repaid', 'overdue'])->sum('amount') / 100;
        $this->totalCollected = Repayment::withoutGlobalScopes()->sum('amount') / 100;
    }

    public function loadTrends()
    {
        // Organization Growth (Last 6 Months)
        $this->orgGrowthData = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = \App\Models\Organization::systemNow()->subMonths($monthsAgo);
            $count = Organization::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return [
                'month' => $date->format('M'),
                'count' => $count,
            ];
        })->toArray();

        // Platform Activity (Daily Repayments across all orgs, last 7 days)
        $this->platformActivity = collect(range(6, 0))->map(function ($daysAgo) {
            $date = \App\Models\Organization::systemNow()->subDays($daysAgo)->startOfDay();
            $amount = Repayment::withoutGlobalScopes()
                ->whereDate('paid_at', $date)
                ->sum('amount') / 100;

            return [
                'day' => $date->format('D'),
                'amount' => (float) $amount,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
