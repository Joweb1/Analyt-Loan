<?php

namespace Database\Factories;

use App\Models\Borrower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
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
            'borrower_id' => Borrower::factory(),
            'loan_number' => 'LN-' . $this->faker->unique()->numerify('#####'),
            'amount' => $this->faker->randomFloat(2, 50000, 5000000),
            'loan_product' => $this->faker->randomElement(['Personal Loan', 'Business Loan', 'Mortgage']),
            'interest_rate' => $this->faker->randomFloat(2, 5, 20),
            'duration' => $this->faker->numberBetween(1, 12),
            'duration_unit' => 'month',
            'status' => 'applied',
        ];
    }
}
