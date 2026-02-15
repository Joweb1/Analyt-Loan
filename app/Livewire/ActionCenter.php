<?php

namespace App\Livewire;

use App\Models\SystemNotification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActionCenter extends Component
{
    public $tasks = [];

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $orgId = Auth::user()->organization_id;

        // Query real "Actions" from system_notifications table
        $this->tasks = SystemNotification::where('organization_id', $orgId)
            ->where('is_actionable', true)
            ->whereNull('read_at')
            ->latest()
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->category === 'kyc' ? 'kyc_review' : ($notif->category === 'loan' ? 'loan_approval' : 'overdue_loan'),
                    'title' => $notif->title,
                    'description' => $notif->message,
                    'date' => $notif->created_at,
                    'link' => $notif->action_link ?? '#',
                    'priority' => $notif->priority,
                ];
            });
    }

    public function markAsResolved($id)
    {
        $notif = SystemNotification::find($id);
        if ($notif && $notif->organization_id === Auth::user()->organization_id) {
            $notif->read_at = now();
            $notif->save();
            $this->loadTasks();
            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Action marked as resolved.']);
        }
    }

    public function render()
    {
        return view('livewire.action-center')->layout('layouts.app');
    }
}
