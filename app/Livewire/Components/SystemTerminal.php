<?php

namespace App\Livewire\Components;

use App\Models\SystemHealthLog;
use App\Services\SystemHealthService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SystemTerminal extends Component
{
    public $logs = [];

    public $commandInput = '';

    public $systemStatus = 'operational';

    public function mount()
    {
        $this->refreshLogs();
    }

    public function refreshLogs()
    {
        // Org Admins only see their org's logs
        $this->logs = SystemHealthLog::where('organization_id', Auth::user()->organization_id)
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at->toDateTimeString(),
                    'level' => $log->level,
                    'message' => $log->message,
                    'component' => $log->component,
                ];
            })
            ->toArray();

        $this->determineStatus();
        $this->dispatch('refreshLogs');
    }

    public function determineStatus()
    {
        $latestLogs = SystemHealthLog::where('organization_id', Auth::user()->organization_id)
            ->latest()
            ->get()
            ->unique('component');

        if ($latestLogs->contains('level', 'error')) {
            $this->systemStatus = 'critical';
        } else {
            $this->systemStatus = 'operational';
        }
    }

    public function executeCommand()
    {
        if (empty($this->commandInput)) {
            return;
        }

        $cmd = strtolower(trim($this->commandInput));
        $this->log('input', $cmd, 'terminal');

        match ($cmd) {
            'help' => $this->log('info', 'Available: maintenance, diagnostics, clear', 'kernel'),
            'maintenance' => $this->runMaintenance(),
            'diagnostics' => $this->runDiagnostics(),
            'clear' => $this->clearLogs(),
            default => $this->log('error', "Command not found: $cmd. Try 'help'.", 'terminal')
        };

        $this->commandInput = '';
        $this->refreshLogs();
    }

    public function log($level, $message, $component = 'system')
    {
        SystemHealthLog::create([
            'organization_id' => Auth::user()->organization_id,
            'level' => $level,
            'message' => $message,
            'component' => $component,
        ]);
    }

    public function runMaintenance()
    {
        $this->log('info', 'Starting organizational maintenance...', 'kernel');
        try {
            Artisan::call('app:midnight-sync');
            $this->log('success', 'Maintenance cycle complete.', 'kernel');
        } catch (\Exception $e) {
            $this->log('error', 'Maintenance failed: '.$e->getMessage(), 'kernel');
        }
    }

    public function runDiagnostics()
    {
        $this->log('info', 'Running health diagnostics...', 'diagnostics');
        $results = SystemHealthService::check();
        $this->log('success', 'System checks completed. Database and Cache are operational.', 'diagnostics');
    }

    public function clearLogs()
    {
        SystemHealthLog::where('organization_id', Auth::user()->organization_id)->delete();
        $this->log('info', 'Terminal cleared.', 'kernel');
    }

    public function render()
    {
        return view('livewire.components.system-terminal');
    }
}
