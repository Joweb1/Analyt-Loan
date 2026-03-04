<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        DB::table('users')->delete();
        DB::table('organizations')->delete();
        DB::table('borrowers')->delete();
        DB::table('loans')->delete();
        DB::table('repayments')->delete();
        DB::table('scheduled_repayments')->delete();
        DB::table('form_field_configs')->delete();
        DB::table('collaterals')->delete();
        DB::table('comments')->delete();
        DB::table('system_notifications')->delete();

        // Truncate Permission tables (Spatie)
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        Schema::enableForeignKeyConstraints();

        // 1. Run Core Seeders
        $this->call([
            RoleSeeder::class,
            OrganizationSeeder::class, // Creates App Owner and Demo Org
            LoanProductSeeder::class,
        ]);

        $demoOrg = Organization::where('slug', 'analyt-org-demo')->first();

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
