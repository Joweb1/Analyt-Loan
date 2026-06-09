<?php

namespace Tests\Feature;

use App\Livewire\CustomerRegistrationForm;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuarantorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_render_and_register_guarantor()
    {
        $organization = Organization::factory()->create(['kyc_status' => 'approved']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        Livewire::actingAs($user)
            ->test(CustomerRegistrationForm::class, ['registration_type' => 'guarantor'])
            ->set('name', 'John Doe Guarantor')
            ->set('phone', '08012345678')
            ->set('email', 'john@example.com')
            ->set('address', '123 Test St')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('guarantors', [
            'organization_id' => $organization->id,
            'name' => 'John Doe Guarantor',
            'phone' => '08012345678',
        ]);
    }
}
