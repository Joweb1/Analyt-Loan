<?php

namespace App\Livewire;

use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActionCenter extends Component
{
    public $tasks = [];

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $user = Auth::user();
        $isOwner = $user->isAppOwner();
        $orgId = $user->organization_id;

        // Query real "Actions" from system_notifications table
        $query = SystemNotification::where('is_actionable', true)
            ->whereNull('read_at');

        if (! $isOwner) {
            $query->where('organization_id', $orgId)
                ->where(function ($q) use ($user) {
                    $q->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                });
        }

        $this->tasks = $query->latest()
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
        $user = Auth::user();
        $isOwner = $user->isAppOwner();

        $query = SystemNotification::query();
        if (! $isOwner) {
            $query->where('organization_id', $user->organization_id)
                ->where(function ($q) use ($user) {
                    $q->whereNull('recipient_id')
                        ->orWhere('recipient_id', $user->id);
                });
        }

        $notif = $query->find($id);

        if ($notif) {
            $notif->read_at = now();
            $notif->save();
            $this->loadTasks();
            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Action marked as resolved.']);
        }
    }

    public function render()
    {
        return view('livewire.action-center')->layout('layouts.app', ['title' => 'Action Center']);
    }
}
