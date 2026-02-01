<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $adminRole = Role::findByName('Admin');
        $adminUser->assignRole($adminRole);

        $borrowerRole = Role::findByName('Borrower');
        User::factory(10)->create()->each(function ($user) use ($borrowerRole) {
            $user->assignRole($borrowerRole);
        });
    }
}
