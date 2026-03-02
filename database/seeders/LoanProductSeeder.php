<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LoanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoOrg = \App\Models\Organization::where('slug', 'analyt-org-demo')->first();
        if (! $demoOrg) {
            return;
        }

        $products = [
            [
                'organization_id' => $demoOrg->id,
                'name' => 'Personal Loan',
                'description' => 'Fast personal loans for urgent needs.',
                'default_interest_rate' => 5.00,
                'default_duration' => 6,
                'duration_unit' => 'month',
                'repayment_cycle' => 'monthly',
            ],
            [
                'organization_id' => $demoOrg->id,
                'name' => 'Business Loan',
                'description' => 'Scale your business with competitive rates.',
                'default_interest_rate' => 4.50,
                'default_duration' => 12,
                'duration_unit' => 'month',
                'repayment_cycle' => 'monthly',
            ],
            [
                'organization_id' => $demoOrg->id,
                'name' => 'Student Loan',
                'description' => 'Educational support for registered students.',
                'default_interest_rate' => 2.00,
                'default_duration' => 24,
                'duration_unit' => 'month',
                'repayment_cycle' => 'monthly',
            ],
            [
                'organization_id' => $demoOrg->id,
                'name' => 'Agri Loan',
                'description' => 'Empowering farmers with seasonal repayment plans.',
                'default_interest_rate' => 3.00,
                'default_duration' => 1,
                'duration_unit' => 'year',
                'repayment_cycle' => 'yearly',
            ],
        ];

        foreach ($products as $p) {
            \App\Models\LoanProduct::create($p);
        }
    }
}
