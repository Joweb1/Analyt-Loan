<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create App Owner (Super Admin)
        $appOwnerEmail = 'nahjonah00@gmail.com';
        $appOwner = User::firstOrCreate(
            ['email' => $appOwnerEmail],
            [
                'name' => 'App Owner',
                'password' => Hash::make('password'), // Default password
                'organization_id' => null, // Super Admin doesn't belong to a specific org context usually, or belongs to a System Org
            ]
        );
        
        // Ensure App Owner has Admin role (or a specific Super Admin role if we had one)
        // For now, let's give them Admin role so they can access things, but the logic will rely on email
        $adminRole = Role::findByName('Admin');
        if ($adminRole) {
            $appOwner->assignRole($adminRole);
        }

        // 2. Create Default Organization
        $defaultOrg = Organization::firstOrCreate(
            ['slug' => 'analyt-demo'],
            [
                'name' => 'Analyt Demo Org',
                'email' => 'demo@analyt.com',
                'status' => 'active',
                'kyc_status' => 'approved',
                'owner_id' => $appOwner->id, // Just for testing, normally a specific Org Admin
            ]
        );

        // 3. Migrate existing data to this organization
        User::where('email', '!=', $appOwnerEmail)->update(['organization_id' => $defaultOrg->id]);
        Borrower::query()->update(['organization_id' => $defaultOrg->id]);
        Loan::query()->update(['organization_id' => $defaultOrg->id]);

        // 4. Create an Org Admin for this organization if not exists
        $orgAdmin = User::firstOrCreate(
            ['email' => 'admin@analyt.com'],
            [
                'name' => 'Org Admin',
                'password' => Hash::make('password'),
                'organization_id' => $defaultOrg->id,
            ]
        );
        $orgAdmin->assignRole($adminRole);
    }
}
