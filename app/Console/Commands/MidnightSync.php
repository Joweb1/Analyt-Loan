<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\SystemHealthService;
use App\Services\SystemMaintenanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MidnightSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:midnight-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily system maintenance: sync loan statuses, apply penalties, and generate tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Midnight Sync ['.now()->toDateTimeString().']...');
        SystemHealthService::log('Scheduler', 'info', 'MidnightSync started.');

        Organization::where('status', 'active')->chunk(50, function ($organizations) {
            foreach ($organizations as $org) {
                $this->info("Processing Organization: {$org->name}");

                // If using manual date, we increment it by 1 day at midnight real-time
                // or should we only run if real-time midnight matches?
                // Requirements say: "this will also affect the cron jobs".
                // If manual date is ON, the job should run for the operating_date.

                $dateToRun = $org->use_manual_date ? $org->operating_date : now();

                SystemMaintenanceService::runMaintenanceForDate($org->id, $dateToRun);

                // If it was manual, maybe we should auto-increment it to simulate passage of time?
                // User didn't explicitly ask for auto-increment, but usually, midnight sync implies a day passed.
                // However, they said "admin can set current date manually".
                // Let's stick to what they asked: use the operating date.
            }
        });

        // Reset time for the rest of the console process
        Carbon::setTestNow();

        SystemHealthService::log('Scheduler', 'success', 'MidnightSync completed successfully.');
        $this->info('Midnight Sync completed successfully.');
    }
}
