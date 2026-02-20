<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use Illuminate\Database\Seeder;

class ReportDataSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        if (! $org) {
            return;
        }

        $borrowers = Borrower::where('organization_id', $org->id)->get();
        if ($borrowers->isEmpty()) {
            $borrowers = Borrower::factory()->count(10)->create(['organization_id' => $org->id]);
        }

        // Generate data for the last 12 months
        for ($i = 0; $i < 365; $i++) {
            $date = now()->subDays($i);

            // Randomly create loans on some days
            if (rand(1, 10) > 6) {
                $count = rand(1, 3);
                for ($j = 0; $j < $count; $j++) {
                    Loan::factory()->create([
                        'organization_id' => $org->id,
                        'borrower_id' => $borrowers->random()->id,
                        'amount' => rand(50000, 500000),
                        'release_date' => $date,
                        'created_at' => $date,
                        'status' => 'active',
                    ]);
                }
            }

            // Randomly create repayments on some days
            if (rand(1, 10) > 4) {
                $count = rand(1, 5);
                for ($j = 0; $j < $count; $j++) {
                    $loan = Loan::where('organization_id', $org->id)->inRandomOrder()->first();
                    if ($loan) {
                        Repayment::factory()->create([
                            'loan_id' => $loan->id,
                            'amount' => rand(5000, 20000),
                            'paid_at' => $date,
                            'created_at' => $date,
                        ]);
                    }
                }
            }

            // Randomly create borrowers
            if (rand(1, 10) > 8) {
                Borrower::factory()->create([
                    'organization_id' => $org->id,
                    'created_at' => $date,
                ]);
            }
        }
    }
}
