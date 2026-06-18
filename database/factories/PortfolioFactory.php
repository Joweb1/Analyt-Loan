<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->company().' Portfolio',
            'description' => $this->faker->sentence(),
        ];
    }
}
