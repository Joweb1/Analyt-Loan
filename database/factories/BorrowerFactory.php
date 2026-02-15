<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrower>
 */
class BorrowerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => $this->faker->phoneNumber,
            'bvn' => $this->faker->numerify('###########'),
            'trust_score' => $this->faker->numberBetween(0, 100),
            'portal_access' => $this->faker->boolean,
            'photo_url' => $this->faker->imageUrl(),
        ];
    }
}
