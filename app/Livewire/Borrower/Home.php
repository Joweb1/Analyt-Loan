<?php

namespace App\Livewire\Borrower;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Home extends Component
{
    public $activeLoan;

    public $creditLimit;

    public $greeting;

    public $organization;

    public $unreadAlertsCount = 0;

    public $recentAlerts = [];

    public function mount()
    {
        $user = Auth::user();
        if (! $user->borrower) {
            return redirect()->route('borrower.onboarding.identity');
        }

        // Redirect to onboarding if KYC is not complete
        if ($user->borrower->kyc_status === 'pending' || $user->borrower->kyc_status === 'rejected') {
            // We can check the step here. For now, let's assume if it's pending, they go to identity
            if ($user->borrower->onboarding_step < 4) {
                return redirect()->route('borrower.onboarding.identity');
            }
        }

        $this->organization = $user->organization;
        $this->activeLoan = $user->borrower->loans()
            ->whereIn('status', ['active', 'overdue', 'approved'])
            ->latest()
            ->first();

        $this->unreadAlertsCount = \App\Models\SystemNotification::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $this->recentAlerts = \App\Models\SystemNotification::where('recipient_id', $user->id)
            ->latest()
            ->limit(3)
            ->get();

        // Simple credit limit logic based on Trust Score
        // Base limit: 50,000. Each trust score point adds 1,000.
        // Max limit: 500,000
        $score = $user->borrower->trust_score ?? 0;
        $this->creditLimit = min(500000, 50000 + ($score * 2000));

        $this->setGreeting();
    }

    private function setGreeting()
    {
        $hour = \App\Models\Organization::systemNow()->hour;
        if ($hour < 12) {
            $this->greeting = 'Good Morning';
        } elseif ($hour < 18) {
            $this->greeting = 'Good Afternoon';
        } else {
            $this->greeting = 'Good Evening';
        }
    }

    public function render()
    {
        return view('livewire.borrower.home')->layout('layouts.borrower', ['title' => 'Home']);
    }
}
