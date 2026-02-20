<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Trigger the Laravel Scheduler.
     */
    public function runSchedule(Request $request)
    {
        $this->validateToken($request);

        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();
            Log::info('Web Cron: schedule:run executed.', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduler executed successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Web Cron Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Trigger the Queue Worker to process pending jobs.
     */
    public function runQueue(Request $request)
    {
        $this->validateToken($request);

        try {
            // --stop-when-empty ensures the request terminates after processing pending jobs
            Artisan::call('queue:work', ['--stop-when-empty' => true]);
            $output = Artisan::output();
            Log::info('Web Cron: queue:work executed.', ['output' => $output]);

            return response()->json([
                'success' => true,
                'message' => 'Queue worker executed successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Web Cron Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate the security token.
     */
    protected function validateToken(Request $request)
    {
        $token = config('app.cron_token');

        if (! $token || $request->query('token') !== $token) {
            Log::warning('Web Cron: Unauthorized access attempt.', ['ip' => $request->ip()]);
            abort(403, 'Unauthorized.');
        }
    }
}
