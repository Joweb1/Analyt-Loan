<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrower>
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
            'organization_id' => Organization::factory(),
            'user_id' => function (array $attributes) {
                return User::factory()->create([
                    'organization_id' => $attributes['organization_id'] ?? null,
                ])->id;
            },
            'phone' => $this->faker->phoneNumber,
            'custom_id' => 'CUS-'.strtoupper($this->faker->bothify('??####')),
            'bvn' => $this->faker->numerify('###########'),
            'trust_score' => $this->faker->numberBetween(0, 100),
            'portal_access' => $this->faker->boolean,
            'photo_url' => $this->faker->imageUrl(),
            'is_daily_saver' => false,
            'daily_target_amount' => 0,
        ];
    }
}
