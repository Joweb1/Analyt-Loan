<?php

namespace App\Livewire\Components;

use App\Services\SystemMaintenanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SidebarDateEditor extends Component
{
    public $system_date;
    public $showModal = false;

    protected $rules = [
        'system_date' => 'required|date',
    ];

    public function mount()
    {
        $org = Auth::user()->organization;
        $this->system_date = $org->system_date 
            ? Carbon::parse($org->system_date)->format('Y-m-d')
            : now()->format('Y-m-d');
    }

    public function updateDate()
    {
        if (!Auth::user()->can('change_system_date') && !Auth::user()->hasRole('Admin')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized.']);
            return;
        }

        $this->validate();

        $org = Auth::user()->organization;
        $oldSystemDate = $org->system_date ? $org->system_date->startOfDay() : null;
        $newSystemDate = Carbon::parse($this->system_date, $org->timezone ?? 'UTC')->startOfDay();

        $org->update(['system_date' => $newSystemDate]);

        // Run maintenance logic similar to GeneralSettings.php
        if ($oldSystemDate && $newSystemDate->isAfter($oldSystemDate)) {
            $days = (int) $oldSystemDate->diffInDays($newSystemDate);
            for ($i = 1; $i <= $days; $i++) {
                SystemMaintenanceService::runMaintenanceForDate($org->id, $oldSystemDate->copy()->addDays($i));
            }
        } elseif ($newSystemDate->isBefore($oldSystemDate)) {
            SystemMaintenanceService::runMaintenanceForDate($org->id, $newSystemDate);
        } else {
            SystemMaintenanceService::runMaintenanceForDate($org->id, $newSystemDate);
        }

        $this->showModal = false;
        $this->dispatch('close-modal');
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'System date updated.']);
        $this->dispatch('$refresh'); // Refresh the sidebar
    }

    public function render()
    {
        return view('livewire.components.sidebar-date-editor');
    }
}
