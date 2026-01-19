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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'value' => $this->faker->randomFloat(2, 100, 1000),
            'image_path' => $this->faker->imageUrl(),
            'loan_id' => Loan::factory(),
            'status' => $this->faker->randomElement(['in_vault', 'returned']),
        ];
    }
}
