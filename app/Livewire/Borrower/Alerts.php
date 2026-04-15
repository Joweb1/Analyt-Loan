<?php

namespace App\Livewire\Borrower;

use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Alerts extends Component
{
    use WithPagination;

    public function markAsRead($id)
    {
        $notification = SystemNotification::where('recipient_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->update(['read_at' => \App\Models\Organization::systemNow()]);
        }
    }

    public function markAllAsRead()
    {
        SystemNotification::where('recipient_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => \App\Models\Organization::systemNow()]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'All alerts marked as read.']);
    }

    public function render()
    {
        $notifications = SystemNotification::where('recipient_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('livewire.borrower.alerts', [
            'notifications' => $notifications,
        ])->layout('layouts.borrower', ['title' => 'Alerts & Notifications']);
    }
}
