<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class OverdueLoanSeeder extends Seeder
{
    public function run(): void
    {
        $borrowerRole = Role::firstOrCreate(['name' => 'Borrower']);

        // Create Overdue Borrower
        $user = User::factory()->create(['name' => 'Overdue Borrower '.uniqid()]);
        $user->assignRole($borrowerRole);
        $borrower = Borrower::factory()->create([
            'user_id' => $user->id,
            'phone' => '0801'.rand(1000000, 9999999),
        ]);

        Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 500000,
            'status' => 'overdue',
            'loan_number' => 'LN-OD-'.uniqid(),
            'updated_at' => now()->subDays(15), // 15 days overdue
        ]);

        // Another one
        $user2 = User::factory()->create(['name' => 'Late Payer '.uniqid()]);
        $user2->assignRole($borrowerRole);
        $borrower2 = Borrower::factory()->create([
            'user_id' => $user2->id,
            'phone' => '0908'.rand(1000000, 9999999),
        ]);

        Loan::factory()->create([
            'borrower_id' => $borrower2->id,
            'amount' => 120000,
            'status' => 'overdue',
            'loan_number' => 'LN-OD-'.uniqid(),
            'updated_at' => now()->subDays(45), // 45 days overdue (Red)
        ]);

        // Add 13 more overdue loans to trigger pagination (Total 15)
        for ($i = 3; $i <= 15; $i++) {
            $u = User::factory()->create();
            $u->assignRole($borrowerRole);
            $b = Borrower::factory()->create(['user_id' => $u->id, 'phone' => '070'.rand(10000000, 99999999)]);
            Loan::factory()->create([
                'borrower_id' => $b->id,
                'status' => 'overdue',
                'loan_number' => 'LN-OD-'.str_pad($i, 2, '0', STR_PAD_LEFT).'-'.rand(1000, 9999),
                'updated_at' => now()->subDays(rand(1, 60)),
            ]);
        }
    }
}
