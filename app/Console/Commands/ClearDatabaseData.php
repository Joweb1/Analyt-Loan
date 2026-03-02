<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDatabaseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-data {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all tables except migrations to clear data without remigrating.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('The application is in production. Do you really want to clear ALL data?')) {
                return;
            }
        }

        $this->info('Clearing database data...');

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Disable foreign key checks
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'pgsql') {
            DB::statement("SET session_replication_role = 'replica';");
        } else {
            Schema::disableForeignKeyConstraints();
        }

        $tables = [];

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'migrations';");
            $tables = array_map(fn ($t) => $t->name, $tables);
        } elseif ($driver === 'pgsql') {
            $tables = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename NOT LIKE 'migrations';");
            $tables = array_map(fn ($t) => $t->tablename, $tables);
        } else {
            // Fallback for MySQL/MariaDB
            $tables = Schema::getTableListing();
            $tables = array_diff($tables, ['migrations']);
        }

        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Re-enable foreign key checks
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } elseif ($driver === 'pgsql') {
            DB::statement("SET session_replication_role = 'origin';");
        } else {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('All data cleared successfully.');
    }
}
