<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemNotification>
 */
class SystemNotificationFactory extends Factory
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
            'user_id' => User::factory(),
            'recipient_id' => User::factory(),
            'title' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'type' => 'info',
            'category' => 'system',
            'priority' => 'normal',
            'is_actionable' => false,
        ];
    }
}
