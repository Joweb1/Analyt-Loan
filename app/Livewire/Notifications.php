<?php

namespace App\Livewire;

use App\Models\SystemNotification;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public $filter = 'all';

    public function markAllAsRead()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->isAppOwner()) {
            $query = SystemNotification::whereNull('organization_id')->whereNull('read_at');
        } elseif ($user->hasRole('Borrower')) {
            $query = SystemNotification::where('organization_id', $user->organization_id)
                ->where('recipient_id', $user->id)
                ->whereNull('read_at');
        } else {
            $query = SystemNotification::where('organization_id', $user->organization_id)
                ->whereNull('read_at')
                ->where(function ($q) use ($user) {
                    $q->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                });
        }

        $query->update(['read_at' => now()]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'All activities marked as read.']);
    }

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $orgId = $user->organization_id;

        // Security check: Staff can be restricted from seeing org-wide notifications
        if (! $user->hasRole('Admin') && ! $user->hasPermissionTo('access_org_notifications')) {
            if (! $user->hasRole('Borrower')) {
                $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have access to view organizational notifications.']);

                return view('livewire.notifications', [
                    'notifications' => SystemNotification::whereNull('id')->paginate(15),
                ])->layout('layouts.app', ['title' => 'Notifications']);
            }
        }

        // Also respect the push_enabled toggle for general viewing if that's what the user meant by "will not have access to see organisation's notification"
        if (! $user->hasRole('Admin') && ! $user->pushEnabled() && ! $user->hasRole('Borrower')) {
            $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Organization notifications are toggled off for your account.']);

            return view('livewire.notifications', [
                'notifications' => SystemNotification::whereNull('id')->paginate(15),
            ])->layout('layouts.app', ['title' => 'Notifications']);
        }

        if ($user->isAppOwner()) {
            // App Owner only sees platform-wide notifications (where organization_id is null)
            $query = SystemNotification::whereNull('organization_id')->with('user')->latest();
        } elseif ($user->hasRole('Borrower')) {
            // Borrowers ONLY see their targeted notifications within their organization
            $query = SystemNotification::where('organization_id', $orgId)
                ->where('recipient_id', $user->id)
                ->with('user')
                ->latest();
        } else {
            // Staff/Admins see global notifications within their organization OR those specifically targeted to them
            $query = SystemNotification::where('organization_id', $orgId)
                ->where(function ($q) use ($user) {
                    $q->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                })
                ->with('user')
                ->latest();
        }

        if ($this->filter !== 'all') {
            $query->where('category', $this->filter);
        }

        return view('livewire.notifications', [
            'notifications' => $query->paginate(15),
        ])->layout('layouts.app', ['title' => 'Notifications']);
    }
}
