<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have borrowers
        if (Borrower::count() < 5) {
            Borrower::factory(10)->create();
        }
        $borrowers = Borrower::all();

        // 1. Loans Applied (Total 10, 5 Today)
        foreach (range(1, 5) as $i) {
            Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'applied',
                'created_at' => now(), // Today
                'amount' => rand(10000, 500000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);
        }
        foreach (range(1, 5) as $i) {
            Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'applied',
                'created_at' => now()->subDays(rand(2, 10)), // Older
                'amount' => rand(10000, 500000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);
        }

        // 2. Loans Approved (Total 5, 2 Updated Today)
        foreach (range(1, 2) as $i) {
            $loan = Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'approved',
                'created_at' => now()->subDays(5),
                'updated_at' => now(), // Approved Today
                'amount' => rand(50000, 1000000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);

            // Create a Repayment for this loan (Today)
            Repayment::create([
                'loan_id' => $loan->id,
                'amount' => $loan->amount * 0.1, // 10% paid
                'paid_at' => now(),
            ]);
        }
        foreach (range(1, 3) as $i) {
            Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'approved',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(2),
                'amount' => rand(50000, 1000000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);
        }

        // 3. Loans Declined (Total 3, 1 Updated Today)
        foreach (range(1, 1) as $i) {
            Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'declined',
                'created_at' => now()->subDays(1),
                'updated_at' => now(), // Declined Today
                'amount' => rand(10000, 200000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);
        }
        foreach (range(1, 2) as $i) {
            Loan::factory()->create([
                'borrower_id' => $borrowers->random()->id,
                'status' => 'declined',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(3),
                'amount' => rand(10000, 200000),
                'loan_number' => 'LN-'.rand(10000, 99999),
            ]);
        }
    }
}
