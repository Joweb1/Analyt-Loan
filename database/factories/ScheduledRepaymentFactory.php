<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledRepayment>
 */
class ScheduledRepaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'principal_amount' => $this->faker->randomFloat(2, 500, 10000),
            'interest_amount' => $this->faker->randomFloat(2, 50, 1000),
            'penalty_amount' => 0,
            'paid_amount' => 0,
            'status' => 'applied',
            'installment_number' => $this->faker->numberBetween(1, 12),
        ];
    }
}
