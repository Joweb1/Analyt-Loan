<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UserProfile;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Staff']);
        Role::create(['name' => 'Borrower']);

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->user->assignRole('Staff');
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(UserProfile::class)
            ->assertStatus(200);
    }

    public function test_it_can_update_basic_profile()
    {
        Livewire::actingAs($this->user)
            ->test(UserProfile::class)
            ->set('name', 'New Name')
            ->set('email', 'newemail@example.com')
            ->call('updateProfile')
            ->assertDispatched('custom-alert');

        $this->user->refresh();
        $this->assertEquals('New Name', $this->user->name);
        $this->assertEquals('newemail@example.com', $this->user->email);
    }

    public function test_it_can_update_password()
    {
        Livewire::actingAs($this->user)
            ->test(UserProfile::class)
            ->set('current_password', 'password')
            ->set('new_password', 'new-password')
            ->set('new_password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertDispatched('custom-alert');

        $this->assertTrue(Hash::check('new-password', $this->user->fresh()->password));
    }

    public function test_borrower_can_complete_kyc()
    {
        $borrowerUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'phone' => '2348000000000',
        ]);
        $borrowerUser->assignRole('Borrower');

        Livewire::actingAs($borrowerUser)
            ->test(UserProfile::class)
            ->set('dob', '1990-01-01')
            ->set('gender', 'Male')
            ->set('address', '123 Test St')
            ->set('bvn', '12345678901')
            ->set('nin', '10987654321')
            ->set('marital_status', 'Single')
            ->set('bank_account_details', 'Bank ABC, 1234567890')
            ->call('completeKyc')
            ->assertDispatched('custom-alert')
            ->assertSet('kyc_status', 'pending');

        $borrower = Borrower::where('user_id', $borrowerUser->id)->first();
        $this->assertEquals('pending', $borrower->kyc_status);
        $this->assertEquals('12345678901', $borrower->bvn);
    }
}
