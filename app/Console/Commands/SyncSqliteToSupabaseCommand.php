<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSqliteToSupabaseCommand extends Command
{
    protected $signature = 'app:sync-sqlite-to-supabase {--force : Skip confirmation}';

    protected $description = 'Selective sync of specific users and their organization data from local SQLite to Supabase';

    public function handle()
    {
        if (! $this->option('force') && ! app()->environment('local')) {
            if (! $this->confirm('This will overwrite data on your Supabase database. Are you sure?')) {
                return;
            }
        }

        $this->info('Starting selective sync from SQLite to Supabase...');

        // Force sqlite to use the local file
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $sqlite = DB::connection('sqlite');
        $pgsql = DB::connection('pgsql');

        // 1. Identify Target Users in SQLite
        $targetEmails = ['admin@analyt.com', 'nahjonah@gmail.com', 'nahjonah00@gmail.com'];
        $targetNames = ['Test user'];

        $users = $sqlite->table('users')
            ->whereIn('email', $targetEmails)
            ->orWhereIn('name', $targetNames)
            ->get();

        if ($users->isEmpty()) {
            $this->error('No matching users found in SQLite database.');

            return;
        }

        $userIds = $users->pluck('id')->toArray();
        $orgIds = $users->pluck('organization_id')->filter()->unique()->toArray();

        $this->info('Found '.count($userIds).' users and '.count($orgIds).' organizations to sync.');

        // Disable foreign key checks on Postgres
        $pgsql->statement("SET session_replication_role = 'replica';");

        // Helper to sync filtered data
        $syncFiltered = function ($tableName, $query) use ($pgsql) {
            $this->info("Syncing table: {$tableName}...");
            $data = $query->get();

            if ($data->isEmpty()) {
                $this->line("  - No data found for {$tableName}, skipping.");

                return;
            }

            // Truncate first (Careful: this clears the whole table on Supabase)
            $pgsql->table($tableName)->truncate();

            $insertData = $data->map(function ($item) use ($tableName) {
                $row = (array) $item;

                // Mock required fields if missing
                if ($tableName === 'users' && empty($row['phone'])) {
                    $row['phone'] = '080'.rand(10000000, 99999999);
                }

                return $row;
            })->toArray();

            foreach (array_chunk($insertData, 50) as $chunk) {
                $pgsql->table($tableName)->insert($chunk);
            }

            $this->info('  - Successfully synced '.count($data).' rows.');
        };

        // --- EXECUTION ORDER ---

        // 1. Core Config (Sync all)
        $syncFiltered('roles', $sqlite->table('roles'));
        $syncFiltered('permissions', $sqlite->table('permissions'));
        $syncFiltered('role_has_permissions', $sqlite->table('role_has_permissions'));
        $syncFiltered('platform_settings', $sqlite->table('platform_settings'));

        // 2. Organizations
        $syncFiltered('organizations', $sqlite->table('organizations')->whereIn('id', $orgIds));

        // 3. Users
        $syncFiltered('users', $sqlite->table('users')->whereIn('id', $userIds));

        // 4. User RBAC Pivots
        $syncFiltered('model_has_roles', $sqlite->table('model_has_roles')->whereIn('model_id', $userIds));
        $syncFiltered('model_has_permissions', $sqlite->table('model_has_permissions')->whereIn('model_id', $userIds));

        // 5. Organization Level Data
        $syncFiltered('loan_products', $sqlite->table('loan_products')->whereIn('organization_id', $orgIds));
        $syncFiltered('form_field_configs', $sqlite->table('form_field_configs')->whereIn('organization_id', $orgIds));

        // 6. Borrowers & Savings
        $borrowerQuery = $sqlite->table('borrowers')->whereIn('organization_id', $orgIds);
        $borrowerIds = $borrowerQuery->pluck('id')->toArray();
        $syncFiltered('borrowers', $borrowerQuery);

        $savingsAccountQuery = $sqlite->table('savings_accounts')->whereIn('borrower_id', $borrowerIds);
        $savingsIds = $savingsAccountQuery->pluck('id')->toArray();
        $syncFiltered('savings_accounts', $savingsAccountQuery);
        $syncFiltered('savings_transactions', $sqlite->table('savings_transactions')->whereIn('savings_account_id', $savingsIds));

        // 7. Loans & Related
        $loanQuery = $sqlite->table('loans')->whereIn('organization_id', $orgIds);
        $loanIds = $loanQuery->pluck('id')->toArray();
        $syncFiltered('loans', $loanQuery);

        $syncFiltered('repayments', $sqlite->table('repayments')->whereIn('loan_id', $loanIds));
        $syncFiltered('scheduled_repayments', $sqlite->table('scheduled_repayments')->whereIn('loan_id', $loanIds));
        $syncFiltered('collaterals', $sqlite->table('collaterals')->whereIn('organization_id', $orgIds));
        $syncFiltered('payment_proofs', $sqlite->table('payment_proofs')->whereIn('organization_id', $orgIds));

        // 8. System Logs/Notifications
        $syncFiltered('system_notifications', $sqlite->table('system_notifications')->whereIn('organization_id', $orgIds));
        $syncFiltered('comments', $sqlite->table('comments')->whereIn('user_id', $userIds));

        // Re-enable foreign key checks
        $pgsql->statement("SET session_replication_role = 'origin';");

        $this->info('Selective synchronization complete!');
    }
}
