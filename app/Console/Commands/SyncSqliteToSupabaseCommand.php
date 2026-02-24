<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncSqliteToSupabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-sqlite-to-supabase';

    protected $description = 'Sync all data from local SQLite to Supabase PostgreSQL';

    public function handle()
    {
        $this->info('Starting sync from SQLite to Supabase...');

        // Force sqlite to use the local file regardless of DB_DATABASE env
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $sqlite = \Illuminate\Support\Facades\DB::connection('sqlite');
        $pgsql = \Illuminate\Support\Facades\DB::connection('pgsql');

        // Get all tables from SQLite
        $tables = $sqlite->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'migrations'");

        $pgsql->statement('SET session_replication_role = "replica";');

        foreach ($tables as $table) {
            $tableName = $table->name;
            $this->info("Syncing table: {$tableName}");

            $data = $sqlite->table($tableName)->get();

            if ($data->isEmpty()) {
                $this->line("  Table {$tableName} is empty, skipping.");

                continue;
            }

            // Clear destination table
            $pgsql->table($tableName)->truncate();

            // Insert in chunks
            $chunks = $data->chunk(100);
            foreach ($chunks as $chunk) {
                $insertData = array_map(function ($item) {
                    return (array) $item;
                }, $chunk->toArray());
                $pgsql->table($tableName)->insert($insertData);
            }

            $this->info('  Successfully synced '.count($data)." rows for {$tableName}.");
        }

        $pgsql->statement('SET session_replication_role = "origin";');

        $this->info('Data synchronization complete!');
    }
}
