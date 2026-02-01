<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Borrower']);
        Role::create(['name' => 'Loan Analyst']);
        Role::create(['name' => 'Vault Manager']);
        Role::create(['name' => 'Credit Analyst']);
        Role::create(['name' => 'Collection Specialist']);
    }
}