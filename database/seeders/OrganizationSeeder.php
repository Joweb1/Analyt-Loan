<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Guarantor;
use App\Models\Organization;
use App\Models\Saver;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Create App Owner (owner@analytloan.com)
        $appOwner = User::firstOrCreate(
            ['email' => 'owner@analytloan.com'],
            [
                'name' => 'App Owner',
                'type' => 'owner',
                'phone' => '2348000000000',
                'password' => $password,
                'organization_id' => null,
            ]
        );
        $appOwner->assignRole('App Owner');

        // 2. Create "Analyt Demo Org"
        $demoOrg = Organization::firstOrCreate(
            ['slug' => 'analyt-demo-org'],
            [
                'name' => 'Analyt Demo Org',
                'email' => 'demo@analytloan.com',
                'status' => 'active',
                'kyc_status' => 'approved',
                'owner_id' => $appOwner->id,
            ]
        );

        // 3. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@analytloan.com'],
            [
                'name' => 'Demo Admin',
                'type' => 'admin',
                'phone' => '2348011111111',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        $admin->assignRole('Admin');

        // 4. Create Staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@analytloan.com'],
            [
                'name' => 'Demo Staff',
                'type' => 'staff',
                'phone' => '2348022222222',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        $staff->assignRole('Staff');

        // 5. Create Borrower
        $borrower = User::firstOrCreate(
            ['email' => 'borrower@analytloan.com'],
            [
                'name' => 'Demo Borrower',
                'type' => 'customer',
                'phone' => '2348033333333',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        $borrower->assignRole('Borrower');

        // Ensure Borrower Profile exists
        Borrower::firstOrCreate(
            ['user_id' => $borrower->id],
            [
                'organization_id' => $demoOrg->id,
                'phone' => $borrower->phone,
                'credit_score' => 500,
            ]
        );

        // 6. Create Saver
        $saver = User::firstOrCreate(
            ['email' => 'saver@analytloan.com'],
            [
                'name' => 'Demo Saver',
                'type' => 'customer',
                'phone' => '2348044444444',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        $saver->assignRole('Saver');

        Saver::firstOrCreate(
            ['user_id' => $saver->id],
            [
                'organization_id' => $demoOrg->id,
                'phone' => $saver->phone,
                'kyc_status' => 'approved',
            ]
        );

        // 7. Create Guarantor
        $guarantor = User::firstOrCreate(
            ['email' => 'guarantor@analytloan.com'],
            [
                'name' => 'Demo Guarantor',
                'type' => 'customer',
                'phone' => '2348055555555',
                'password' => $password,
                'organization_id' => $demoOrg->id,
            ]
        );
        $guarantor->assignRole('Guarantor');

        Guarantor::firstOrCreate(
            ['user_id' => $guarantor->id],
            [
                'organization_id' => $demoOrg->id,
                'name' => $guarantor->name,
                'phone' => $guarantor->phone,
                'email' => $guarantor->email,
            ]
        );
    }
}
