<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'borrower_id' => function (array $attributes) {
                return Borrower::factory()->create([
                    'organization_id' => $attributes['organization_id'] ?? null,
                ])->id;
            },
            'loan_number' => 'LN-'.$this->faker->unique()->numerify('#####'),
            'amount' => $this->faker->randomFloat(2, 50000, 5000000),
            'loan_product' => $this->faker->randomElement(['Personal Loan', 'Business Loan', 'Mortgage']),
            'interest_rate' => $this->faker->randomFloat(2, 5, 20),
            'interest_calculation_type' => 'percentage',
            'interest_type' => 'year',
            'duration' => $this->faker->numberBetween(1, 12),
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => $this->faker->numberBetween(1, 12),
            'status' => 'applied',
            'insurance_fee_type' => 'fixed',
            'processing_fee_type' => 'fixed',
        ];
    }
}
