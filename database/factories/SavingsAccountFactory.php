<?php

namespace Database\Factories;

use App\Models\Borrower;
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
            'borrower_id' => Borrower::factory(),
            'account_number' => 'SAV-'.strtoupper(Str::random(8)),
            'balance' => $this->faker->randomFloat(2, 0, 100000),
            'interest_rate' => 0,
            'status' => 'active',
        ];
    }
}
