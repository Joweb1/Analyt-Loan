<?php

namespace Tests\Feature\Livewire;

use App\Livewire\BorrowerRegistrationForm;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BorrowerRegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Borrower']);
        Role::create(['name' => 'Admin']); // Just in case

        $this->organization = Organization::factory()->create([
            'kyc_status' => 'approved',
            'status' => 'active',
        ]);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerRegistrationForm::class)
            ->assertStatus(200);
    }

    public function test_it_validates_required_fields()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerRegistrationForm::class)
            ->set('organization_id', $this->organization->id)
            ->call('save')
            ->assertHasErrors(['name', 'phone', 'dob', 'bvn', 'nin']);
    }

    public function test_it_creates_borrower_with_valid_data()
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->create('passport.jpg', 100, 'image/jpeg');
        $doc = UploadedFile::fake()->create('id_card.pdf');

        Livewire::actingAs($this->admin)
            ->test(BorrowerRegistrationForm::class)
            ->set('organization_id', $this->organization->id)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('phone', '08012345678')
            ->set('dob', '1990-01-01')
            ->set('gender', 'male')
            ->set('address', '123 Main St')
            ->set('bvn', '12345678901')
            ->set('nin', '12345678901')
            ->set('marital_status', 'Single')
            ->set('dependents', 0)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            // Financials
            ->set('bank_name', 'Test Bank')
            ->set('account_number', '0000000000')
            ->set('bank_account_name', 'John Doe')
            // NOK
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_relationship', 'Sister')
            ->set('next_of_kin_phone', '08098765432')
            // Files
            ->set('passport_photo', $photo)
            ->set('identity_document', $doc)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('custom-alert', [
                'type' => 'success',
                'message' => 'Borrower registered successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'organization_id' => $this->organization->id,
        ]);

        $this->assertDatabaseHas('borrowers', [
            'bvn' => '12345678901',
            'organization_id' => $this->organization->id,
        ]);

        // Verify User has Role
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('Borrower'));
    }

    public function test_it_prevents_registration_if_org_kyc_not_approved()
    {
        $this->organization->update(['kyc_status' => 'pending']);

        Livewire::actingAs($this->admin)
            ->test(BorrowerRegistrationForm::class)
            ->set('organization_id', $this->organization->id)
            ->set('name', 'John Doe')
            ->set('phone', '08012345678')
            ->call('save')
            ->assertDispatched('custom-alert', [
                'type' => 'error',
                'message' => 'Registration is currently disabled for this organization (KYC Pending/Rejected).',
            ]);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }
}
