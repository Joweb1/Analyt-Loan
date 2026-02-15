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
        SystemNotification::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'All activities marked as read.']);
    }

    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $query = SystemNotification::where('organization_id', $orgId)->with('user')->latest();

        if ($this->filter !== 'all') {
            $query->where('category', $this->filter);
        }

        return view('livewire.notifications', [
            'notifications' => $query->paginate(15)
        ])->layout('layouts.app');
    }
}
