<?php

namespace Database\Factories;

use App\Models\CashbookEntry;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashbookEntryFactory extends Factory
{
    protected $model = CashbookEntry::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'entry_date' => now()->toDateString(),
            'description' => $this->faker->sentence,
            'status' => 'pending',
        ];
    }
}
