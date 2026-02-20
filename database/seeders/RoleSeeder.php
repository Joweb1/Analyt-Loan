<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear Spatie's permission cache to ensure fresh state
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'manage_borrowers',
            'edit_borrowers',
            'manage_loans',
            'approve_loans',
            'manage_collections',
            'view_reports',
            'manage_vault',
            'manage_settings',
            'send_customer_messages',
            'access_org_notifications',
            'communicate_with_customers',
            'export_and_print',
            'access_minimal_staff_routes',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $borrower = Role::firstOrCreate(['name' => 'Borrower', 'guard_name' => 'web']);
        $borrower->syncPermissions(['view_dashboard']);

        $analyst = Role::firstOrCreate(['name' => 'Loan Analyst', 'guard_name' => 'web']);
        $analyst->syncPermissions(['view_dashboard', 'manage_loans']);

        Role::firstOrCreate(['name' => 'Vault Manager', 'guard_name' => 'web'])->syncPermissions(['view_dashboard', 'manage_vault']);
        Role::firstOrCreate(['name' => 'Credit Analyst', 'guard_name' => 'web'])->syncPermissions(['view_dashboard', 'approve_loans']);
        Role::firstOrCreate(['name' => 'Collection Specialist', 'guard_name' => 'web'])->syncPermissions(['view_dashboard', 'manage_collections']);

        $collectionOfficer = Role::firstOrCreate(['name' => 'Collection Officer', 'guard_name' => 'web']);
        $collectionOfficer->syncPermissions(['view_dashboard', 'manage_collections', 'access_minimal_staff_routes']);
    }
}
