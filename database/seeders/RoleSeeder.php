<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear Spatie's permission cache to ensure fresh state
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // App Management
            'manage_organizations',

            // Core Dashboard
            'view_dashboard',

            // Borrower & Saver Management
            'manage_borrowers',
            'edit_borrowers',
            'manage_savers',
            'edit_savers',
            'approve_kyc',

            // Loan Management
            'manage_loans',
            'approve_loans',
            'apply_for_loans',

            // Collections & Savings
            'manage_collections',
            'enter_collections',
            'enter_savings',
            'view_savings',
            'delete_savings',

            // Guarantors
            'manage_guarantors',

            // System & Settings
            'view_reports',
            'manage_vault',
            'manage_settings',
            'send_customer_messages',
            'access_org_notifications',
            'communicate_with_customers',
            'export_and_print',
            'access_minimal_staff_routes',
            'record_cashbook',
            'view_live_balance',
            'view_records_hub_stats',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. App Owner (Super Admin)
        $appOwner = Role::firstOrCreate(['name' => 'App Owner', 'guard_name' => 'web']);
        $appOwner->syncPermissions(Permission::all());

        // 2. Admin (Org Level)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(array_diff($permissions, ['manage_organizations']));

        // 3. General Staff (Base)
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'view_dashboard',
            'manage_borrowers',
            'manage_savers',
            'manage_loans',
            'manage_collections',
            'enter_collections',
            'enter_savings',
            'access_minimal_staff_routes',
            'record_cashbook',
            'view_live_balance',
        ]);

        // 4. Granular Staff Roles (Required by routes)

        $loanAnalyst = Role::firstOrCreate(['name' => 'Loan Analyst', 'guard_name' => 'web']);
        $loanAnalyst->syncPermissions(['view_dashboard', 'manage_loans', 'approve_loans', 'view_reports', 'access_minimal_staff_routes']);

        $vaultManager = Role::firstOrCreate(['name' => 'Vault Manager', 'guard_name' => 'web']);
        $vaultManager->syncPermissions(['view_dashboard', 'manage_vault', 'view_reports', 'access_minimal_staff_routes']);

        $creditAnalyst = Role::firstOrCreate(['name' => 'Credit Analyst', 'guard_name' => 'web']);
        $creditAnalyst->syncPermissions(['view_dashboard', 'manage_loans', 'approve_kyc', 'access_minimal_staff_routes']);

        $collectionSpecialist = Role::firstOrCreate(['name' => 'Collection Specialist', 'guard_name' => 'web']);
        $collectionSpecialist->syncPermissions(['view_dashboard', 'manage_collections', 'enter_collections', 'view_reports', 'access_minimal_staff_routes']);

        $collectionOfficer = Role::firstOrCreate(['name' => 'Collection Officer', 'guard_name' => 'web']);
        $collectionOfficer->syncPermissions(['view_dashboard', 'enter_collections', 'access_minimal_staff_routes']);

        // 5. Customer Roles

        $borrower = Role::firstOrCreate(['name' => 'Borrower', 'guard_name' => 'web']);
        $borrower->syncPermissions(['view_dashboard', 'apply_for_loans', 'view_savings']);

        $saver = Role::firstOrCreate(['name' => 'Saver', 'guard_name' => 'web']);
        $saver->syncPermissions(['view_dashboard', 'view_savings']);

        $guarantor = Role::firstOrCreate(['name' => 'Guarantor', 'guard_name' => 'web']);
        $guarantor->syncPermissions(['view_dashboard']);
    }
}
