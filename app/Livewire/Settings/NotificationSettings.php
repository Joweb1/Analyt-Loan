<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationSettings extends Component
{
    public $email_reminders;
    public $loan_approval_alerts;

    public function mount()
    {
        $org = Auth::user()->organization;
        $this->email_reminders = $org->email_reminders_enabled;
        $this->loan_approval_alerts = $org->loan_approval_alerts_enabled;
    }

    public function updated($propertyName)
    {
        $org = Auth::user()->organization;
        
        if ($propertyName === 'email_reminders') {
            $org->email_reminders_enabled = $this->email_reminders;
        } elseif ($propertyName === 'loan_approval_alerts') {
            $org->loan_approval_alerts_enabled = $this->loan_approval_alerts;
        }
        
        $org->save();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Notification preference updated.']);
    }

    public function render()
    {
        return view('livewire.settings.notification-settings')->layout('layouts.app');
    }
}
