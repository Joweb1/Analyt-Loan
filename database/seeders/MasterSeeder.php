<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Comment;
use App\Models\FormFieldConfig;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
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
        User::truncate();
        Organization::truncate();
        Borrower::truncate();
        Loan::truncate();
        Repayment::truncate();
        ScheduledRepayment::truncate();
        FormFieldConfig::truncate();
        Collateral::truncate();
        Comment::truncate();
        SystemNotification::truncate();
        Portfolio::truncate();
        DB::table('portfolio_user')->truncate();

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
            LoanProductSeeder::class,
        ]);

        $demoOrg = Organization::where('slug', 'analyt-org-demo')->first();

        // 2. Add some Demo Loans/Data linked to Demo Org
        // We ensure the observers catch these and create actionable tasks
        $this->call([
            LoanSeeder::class,
            PortfolioSeeder::class,
            CollateralSeeder::class,
            StatusBoardSeeder::class,
            ActionTaskSeeder::class,
        ]);

        // Fix missing organization_ids for everything created by standard seeders
        User::whereNull('organization_id')->where('email', '!=', 'nahjonah00@gmail.com')->update(['organization_id' => $demoOrg->id]);
        Borrower::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);
        Loan::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);

        // IMPORTANT: Also fix system_notifications organization_id
        SystemNotification::whereNull('organization_id')->update(['organization_id' => $demoOrg->id]);
    }
}
