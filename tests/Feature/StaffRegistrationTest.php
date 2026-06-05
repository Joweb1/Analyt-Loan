<?php

namespace Tests\Feature;

use App\Livewire\CustomerRegistrationForm;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_register_staff_member()
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create(['kyc_status' => 'approved']);
        $admin = User::factory()->create([
            'organization_id' => $org->id,
            'type' => 'admin',
        ]);
        $admin->assignRole('Admin');

        Livewire::actingAs($admin)
            ->test(CustomerRegistrationForm::class)
            ->set('registration_type', 'staff')
            ->set('name', 'Staff Member')
            ->set('email', 'staff@example.com')
            ->set('phone', '08012345678')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'type' => 'staff',
            'organization_id' => $org->id,
        ]);

        $staff = User::where('email', 'staff@example.com')->first();
        $this->assertTrue($staff->hasRole('Staff'));
    }

    #[Test]
    public function non_admin_cannot_register_staff_member()
    {
        $this->seed(RoleSeeder::class);
        $org = Organization::factory()->create(['kyc_status' => 'approved']);
        $staffUser = User::factory()->create([
            'organization_id' => $org->id,
            'type' => 'staff',
        ]);
        $staffUser->assignRole('Staff');

        Livewire::actingAs($staffUser)
            ->test(CustomerRegistrationForm::class)
            ->set('registration_type', 'staff')
            ->assertDispatched('custom-alert', function ($name, $params) {
                $data = $params[0];

                return $data['type'] === 'error' && str_contains($data['message'], 'Only administrators');
            })
            ->assertSet('registration_type', 'borrower');
    }
}
