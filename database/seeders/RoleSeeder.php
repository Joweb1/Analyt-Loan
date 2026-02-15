<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'manage_borrowers',
            'manage_loans',
            'approve_loans',
            'manage_collections',
            'view_reports',
            'manage_vault',
            'manage_settings',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'Borrower']);
        
        $analyst = Role::firstOrCreate(['name' => 'Loan Analyst']);
        $analyst->syncPermissions(['view_dashboard', 'manage_loans']);

        Role::firstOrCreate(['name' => 'Vault Manager'])->syncPermissions(['view_dashboard', 'manage_vault']);
        Role::firstOrCreate(['name' => 'Credit Analyst'])->syncPermissions(['view_dashboard', 'approve_loans']);
        Role::firstOrCreate(['name' => 'Collection Specialist'])->syncPermissions(['view_dashboard', 'manage_collections']);
    }
}
