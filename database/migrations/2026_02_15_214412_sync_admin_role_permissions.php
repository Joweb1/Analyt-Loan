<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
        ];

        foreach ($permissions as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
