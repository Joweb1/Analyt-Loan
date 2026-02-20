<?php

namespace App\Livewire\Admin;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public $platformName;

    public $supportEmail;

    public $allowNewRegistrations;

    public $maintenanceMode;

    public function mount()
    {
        if (! Auth::user()->isAppOwner()) {
            abort(403);
        }

        $this->platformName = PlatformSetting::get('platform_name', 'Analyt');
        $this->supportEmail = PlatformSetting::get('support_email', 'support@analyt.com');
        $this->allowNewRegistrations = (bool) PlatformSetting::get('allow_new_registrations', true);
        $this->maintenanceMode = (bool) PlatformSetting::get('maintenance_mode', false);
    }

    public function toggleRegistration()
    {
        $this->allowNewRegistrations = ! $this->allowNewRegistrations;
        PlatformSetting::set('allow_new_registrations', $this->allowNewRegistrations, 'boolean');
        $status = $this->allowNewRegistrations ? 'enabled' : 'disabled';
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "Self-service registration {$status}."]);
    }

    public function toggleMaintenance()
    {
        $this->maintenanceMode = ! $this->maintenanceMode;
        PlatformSetting::set('maintenance_mode', $this->maintenanceMode, 'boolean');
        $status = $this->maintenanceMode ? 'enabled' : 'disabled';
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => "Maintenance mode {$status}."]);
    }

    public function saveSettings()
    {
        PlatformSetting::set('platform_name', $this->platformName);
        PlatformSetting::set('support_email', $this->supportEmail);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Platform settings updated successfully.']);
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('layouts.app', ['title' => 'Platform Settings']);
    }
}
