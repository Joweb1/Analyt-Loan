<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Organization;
use App\Models\User;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\SystemNotification;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for truncation
        Schema::disableForeignKeyConstraints();

        // Truncate all relevant tables
        DB::table('users')->truncate();
        DB::table('organizations')->truncate();
        DB::table('borrowers')->truncate();
        DB::table('loans')->truncate();
        DB::table('repayments')->truncate();
        DB::table('scheduled_repayments')->truncate();
        DB::table('form_field_configs')->truncate();
        DB::table('collaterals')->truncate();
        DB::table('comments')->truncate();
        DB::table('system_notifications')->truncate();
        
        // Truncate Permission tables (Spatie)
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        Schema::enableForeignKeyConstraints();

        // 1. Run Core Seeders
        $this->call([
            RoleSeeder::class,
            OrganizationSeeder::class, // Creates App Owner and Demo Org
        ]);

        $demoOrg = Organization::where('slug', 'analyt-demo')->first();

        // 2. Add some Demo Loans/Data linked to Demo Org
        // We ensure the observers catch these and create actionable tasks
        $this->call([
            LoanSeeder::class,
            CollateralSeeder::class,
            StatusBoardSeeder::class,
            ActionTaskSeeder::class, // Added this
        ]);

        // Fix missing organization_ids for everything created by standard seeders
        User::whereNull('organization_id')->where('email', '!=', 'nahjonah00@gmail.com')->update(['organization_id' => $demoOrg->id]);
        Borrower::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);
        Loan::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);
        
        // IMPORTANT: Also fix system_notifications organization_id
        SystemNotification::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);
    }
}
