<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // We use MasterSeeder as the primary entry point for a "proper" seed
        // It handles truncation and ordered seeding of roles, orgs, loans, etc.
        $this->call(MasterSeeder::class);
    }
}
