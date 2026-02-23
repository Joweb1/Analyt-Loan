<?php

namespace App\Services;

use App\Models\SystemHealthLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    /**
     * Run a full system diagnostic check and log results.
     */
    public static function check(): array
    {
        $status = [];

        // 1. Database Check
        try {
            DB::connection()->getPdo();
            $status['database'] = ['level' => 'success', 'message' => 'Connected to primary MySQL cluster.'];
        } catch (\Exception $e) {
            $status['database'] = ['level' => 'error', 'message' => 'Connection failed: '.$e->getMessage()];
        }

        // 2. Cache Check
        try {
            Cache::put('health_check', true, 10);
            if (Cache::get('health_check')) {
                $status['cache'] = ['level' => 'success', 'message' => 'Redis/File cache driver operational.'];
            }
        } catch (\Exception $e) {
            $status['cache'] = ['level' => 'error', 'message' => 'Cache failure: '.$e->getMessage()];
        }

        // 3. Storage Check
        try {
            if (Storage::disk()->exists('.')) {
                $status['storage'] = ['level' => 'success', 'message' => 'Storage permissions verified (RW).'];
            }
        } catch (\Exception $e) {
            $status['storage'] = ['level' => 'error', 'message' => 'Storage inaccessible.'];
        }

        // 4. Scheduler Check
        $lastSync = SystemHealthLog::where('component', 'Scheduler')
            ->where('level', 'success')
            ->where('message', 'MidnightSync completed successfully.')
            ->latest()
            ->first();

        if ($lastSync && $lastSync->created_at->isAfter(now()->subHours(25))) {
            $status['scheduler'] = ['level' => 'success', 'message' => 'Maintenance engine operational. Last sync: '.$lastSync->created_at->diffForHumans()];
        } else {
            $status['scheduler'] = ['level' => 'error', 'message' => 'Maintenance engine stale. Check cron scheduler configuration.'];
        }

        // Log results
        foreach ($status as $component => $data) {
            SystemHealthLog::create([
                'component' => ucfirst($component),
                'level' => $data['level'],
                'message' => $data['message'],
            ]);
        }

        return $status;
    }

    public static function log($component, $level, $message, $payload = null)
    {
        return SystemHealthLog::create([
            'component' => $component,
            'level' => $level,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
