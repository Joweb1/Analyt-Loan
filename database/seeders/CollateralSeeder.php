<?php

namespace Database\Seeders;

use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class CollateralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            return;
        }

        foreach ($organizations as $org) {
            // 1. Seed collaterals for some loans in this organization
            $loans = Loan::where('organization_id', $org->id)->inRandomOrder()->limit(5)->get();

            foreach ($loans as $loan) {
                Collateral::factory()->create([
                    'organization_id' => $org->id,
                    'loan_id' => $loan->id,
                    'status' => 'in_vault',
                ]);
            }

            // 2. Create some company-wide assets (not linked to loans)
            Collateral::factory()->count(3)->create([
                'organization_id' => $org->id,
                'loan_id' => null,
                'status' => 'in_vault',
                'type' => 'Real Estate',
                'name' => $org->name.' Asset '.rand(1, 100),
            ]);

            // 3. Create some returned assets
            $loansReturned = Loan::where('organization_id', $org->id)->inRandomOrder()->limit(2)->get();
            foreach ($loansReturned as $loan) {
                Collateral::factory()->create([
                    'organization_id' => $org->id,
                    'loan_id' => $loan->id,
                    'status' => 'returned',
                ]);
            }
        }
    }
}
