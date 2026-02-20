<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationSettings extends Component
{
    public $email_reminders;

    public $loan_approval_alerts;

    public $push_enabled;

    public $repayment_alerts;

    public $overdue_alerts;

    public $new_borrower_alerts;

    public function mount()
    {
        $org = Auth::user()->organization;
        $this->email_reminders = $org->email_reminders_enabled;
        $this->loan_approval_alerts = $org->loan_approval_alerts_enabled;
        $this->push_enabled = $org->push_notifications_enabled;
        $this->repayment_alerts = $org->repayment_notifications_enabled;
        $this->overdue_alerts = $org->overdue_notifications_enabled;
        $this->new_borrower_alerts = $org->new_borrower_notifications_enabled;
    }

    public function updated($propertyName, $value)
    {
        $org = Auth::user()->organization;
        if (! $org) {
            return;
        }

        $map = [
            'email_reminders' => 'email_reminders_enabled',
            'loan_approval_alerts' => 'loan_approval_alerts_enabled',
            'push_enabled' => 'push_notifications_enabled',
            'repayment_alerts' => 'repayment_notifications_enabled',
            'overdue_alerts' => 'overdue_notifications_enabled',
            'new_borrower_alerts' => 'new_borrower_notifications_enabled',
        ];

        if (isset($map[$propertyName])) {
            $column = $map[$propertyName];

            // Use the value passed from Livewire and ensure it's saved to the database
            $org->forceFill([
                $column => (bool) $value,
            ])->save();

            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Notification preference updated.']);
        }
    }

    public function render()
    {
        return view('livewire.settings.notification-settings')->layout('layouts.app', ['title' => 'Notification Settings']);
    }
}
