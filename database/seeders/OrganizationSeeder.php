<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Create App Owner (nahjonah00)
        $appOwnerEmail = 'nahjonah00@gmail.com';
        $appOwner = User::firstOrCreate(
            ['email' => $appOwnerEmail],
            [
                'name' => 'App Owner',
                'phone' => '2348000000000',
                'password' => $password,
                'organization_id' => null,
            ]
        );

        $adminRole = Role::findByName('Admin');
        $staffRole = Role::findByName('Loan Analyst');
        $borrowerRole = Role::findByName('Borrower');

        if ($adminRole) {
            $appOwner->assignRole($adminRole);
        }

        // 2. Create "Analyt Org Demo"
        $demoOrg = Organization::firstOrCreate(
            ['slug' => 'analyt-org-demo'],
            [
                'name' => 'Analyt Org Demo',
                'email' => 'demo@analyt.com',
                'status' => 'active',
                'kyc_status' => 'approved',
                'owner_id' => $appOwner->id,
            ]
        );

        // Update App Owner to belong to this org for context
        $appOwner->update(['organization_id' => $demoOrg->id]);

        // 3. Create "Analyt admin" (admin@analyt.com)
        $orgAdmin = User::firstOrCreate(
            ['email' => 'admin@analyt.com'],
            [
                'name' => 'Analyt admin',
                'phone' => '2348011111111',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        if ($adminRole) {
            $orgAdmin->assignRole($adminRole);
        }

        // 4. Create "nahjonah@gmail.com"
        $extraUser = User::firstOrCreate(
            ['email' => 'nahjonah@gmail.com'],
            [
                'name' => 'Jonah Extra',
                'phone' => '2348044444444',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        if ($adminRole) {
            $extraUser->assignRole($adminRole);
        }

        // 5. Create "Test user"
        $testUser = User::firstOrCreate(
            ['name' => 'Test user'],
            [
                'email' => 'testuser@analyt.com',
                'phone' => '2348055555555',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        if ($staffRole) {
            $testUser->assignRole($staffRole);
        }

        // 6. Create Staff Members
        $staffData = [
            ['name' => 'Test Staff A', 'email' => 'testa@analyt.com', 'phone' => '2348022222222'],
            ['name' => 'Test Staff B', 'email' => 'testb@analyt.com', 'phone' => '2348033333333'],
        ];

        foreach ($staffData as $data) {
            $staff = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $password,
                    'organization_id' => $demoOrg->id,
                ]
            );
            if ($staffRole) {
                $staff->assignRole($staffRole);
            }
        }

        // 7. Create 4 Random Customers (Borrowers)
        User::factory(4)->create([
            'organization_id' => $demoOrg->id,
            'password' => $password,
        ])->each(function ($user) use ($demoOrg, $borrowerRole) {
            $user->assignRole($borrowerRole);
            Borrower::factory()->create([
                'user_id' => $user->id,
                'organization_id' => $demoOrg->id,
                'phone' => $user->phone,
            ]);
        });
    }
}
