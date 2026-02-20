<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSee('Create an Account');
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $org = \App\Models\Organization::factory()->create(['status' => 'active', 'kyc_status' => 'approved']);

        $component = Volt::test('pages.auth.register')
            ->set('organization_id', $org->id)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '08012345678')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
