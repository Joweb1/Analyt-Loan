<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsAccount>
 */
class SavingsAccountFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'account_number' => 'SAV-'.strtoupper(Str::random(8)),
            'balance' => $this->faker->numberBetween(0, 10000000),
            'daily_savings_balance' => 0,
            'interest_rate' => 0,
            'status' => 'active',
        ];
    }
}
