<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collateral>
 */
class CollateralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => \App\Models\Organization::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'value' => $this->faker->randomFloat(2, 50000, 500000), // More realistic Naira values
            'image_path' => null,
            'loan_id' => null,
            'status' => $this->faker->randomElement(['in_vault', 'returned']),
            'type' => $this->faker->randomElement(['Vehicle', 'Real Estate', 'Jewelry', 'Electronics', 'Other']),
            'condition' => $this->faker->randomElement(['New', 'Used', 'Good', 'Poor']),
            'registered_date' => $this->faker->date(),
        ];
    }
}
