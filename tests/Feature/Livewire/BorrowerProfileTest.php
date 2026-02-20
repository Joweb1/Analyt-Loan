<?php

namespace Tests\Feature\Livewire;

use App\Livewire\BorrowerProfile;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BorrowerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected Borrower $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin');
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
        $this->admin->givePermissionTo(Permission::findOrCreate('manage_borrowers'));
        $this->admin->givePermissionTo(Permission::findOrCreate('edit_borrowers'));

        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $user->id,
            'kyc_status' => 'pending',
        ]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->assertStatus(200);
    }

    public function test_it_can_approve_kyc()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->call('approveKyc')
            ->assertRedirect(route('customer'));

        $this->assertEquals('approved', $this->borrower->fresh()->kyc_status);
        $this->assertDatabaseHas('system_notifications', [
            'organization_id' => $this->organization->id,
            'title' => 'KYC Approved',
            'type' => 'success',
        ]);
    }

    public function test_it_can_decline_kyc()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->set('rejection_reason', 'Invalid documentation')
            ->call('declineKyc')
            ->assertSet('kyc_status', 'rejected');

        $this->assertEquals('rejected', $this->borrower->fresh()->kyc_status);
        $this->assertEquals('Invalid documentation', $this->borrower->fresh()->rejection_reason);
    }

    public function test_it_can_edit_profile()
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->create('photo.jpg', 100);

        Livewire::actingAs($this->admin)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->call('toggleEdit')
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('bvn', '12345678901')
            ->set('new_photo', $photo)
            ->call('save')
            ->assertDispatched('custom-alert');

        $this->borrower->refresh();
        $this->assertEquals('Updated Name', $this->borrower->user->name);
        $this->assertEquals('updated@example.com', $this->borrower->user->email);
        $this->assertEquals('12345678901', $this->borrower->bvn);
        $this->assertNotNull($this->borrower->photo_url);
    }

    public function test_it_aborts_without_permission()
    {
        $staffWithoutPermission = User::factory()->create(['organization_id' => $this->organization->id]);

        Livewire::actingAs($staffWithoutPermission)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->assertForbidden();
    }
}
