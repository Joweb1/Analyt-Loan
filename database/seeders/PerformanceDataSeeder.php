<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PerformanceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a specific Performance Test Organization
        $org = Organization::firstOrCreate(
            ['slug' => 'perf-test-org'],
            [
                'name' => 'Performance Test Org',
                'description' => 'Organization for high-volume performance testing',
                'active' => true
            ]
        );

        // 2. Create Users for the Org
        User::factory()->count(10)->create([
            'organization_id' => $org->id
        ]);

        // 3. Create Borrowers
        $this->command->info('Creating 500 Borrowers...');
        $borrowers = Borrower::factory()->count(500)->create([
            'organization_id' => $org->id
        ]);

        // 4. Create Loans
        $this->command->info('Creating 2000 Loans...');
        $loans = Loan::factory()->count(2000)->create([
            'organization_id' => $org->id,
            'borrower_id' => fn() => $borrowers->random()->id,
            'status' => 'disbursed'
        ]);

        // 5. Create Scheduled Repayments for each Loan
        $this->command->info('Creating 24000 Scheduled Repayments (12 per loan)...');
        $loans->each(function ($loan) {
            ScheduledRepayment::factory()->count(12)->create([
                'loan_id' => $loan->id,
                'organization_id' => $loan->organization_id
            ]);
        });

        // 6. Create actual Repayments for some loans
        $this->command->info('Creating 5000 Repayments...');
        Repayment::factory()->count(5000)->create([
            'loan_id' => fn() => $loans->random()->id,
            'organization_id' => $org->id
        ]);

        // 7. Create Collateral
        $this->command->info('Creating 1000 Collateral records...');
        Collateral::factory()->count(1000)->create([
            'loan_id' => fn() => $loans->random()->id,
            'organization_id' => $org->id
        ]);
    }
}
