<?php

namespace Tests\Feature\Livewire;

use App\Livewire\BorrowerProfile;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
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

        $this->seed(RoleSeeder::class);
        $this->organization = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($this->organization->id);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'admin',
        ]);
        $this->admin->assignRole('Admin');

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

    public function test_it_can_edit_profile_including_bank_and_documents()
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::fake('supabase');

        $photo = UploadedFile::fake()->create('new_photo.jpg', 100, 'image/jpeg');
        $passport = UploadedFile::fake()->create('passport.jpg', 100, 'image/jpeg');
        $id_doc = UploadedFile::fake()->create('id.pdf', 100);

        Livewire::actingAs($this->admin)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->call('toggleEdit')
            ->set('name', 'Updated Name')
            ->set('bank_account_details.bank_name', 'Updated Bank')
            ->set('bank_account_details.account_number', '1234567890')
            ->set('next_of_kin_details.name', 'NOK Name')
            ->set('new_photo', $photo)
            ->set('passport_photo', $passport)
            ->set('identity_doc', $id_doc)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('custom-alert');

        $this->borrower->refresh();
        $this->assertEquals('Updated Name', $this->borrower->user->name);
        $this->assertEquals('Updated Bank', $this->borrower->bank_account_details['bank_name']);
        $this->assertEquals('1234567890', $this->borrower->bank_account_details['account_number']);
        $this->assertEquals('NOK Name', $this->borrower->next_of_kin_details['name']);
        
        $this->assertNotNull($this->borrower->getRawOriginal('photo_url'));
        $this->assertNotNull($this->borrower->passport_photograph);
        $this->assertNotNull($this->borrower->identity_document);

        // Determine which disk it should have used
        $expectedDisk = (config('filesystems.disks.supabase.is_configured') && ! app()->environment('testing'))
            ? 'supabase'
            : (config('filesystems.default') === 'local' ? 'public' : config('filesystems.default'));

        Storage::disk($expectedDisk)->assertExists($this->borrower->getRawOriginal('photo_url'));
        Storage::disk($expectedDisk)->assertExists($this->borrower->passport_photograph);
        Storage::disk($expectedDisk)->assertExists($this->borrower->identity_document);
    }

    public function test_it_aborts_without_permission()
    {
        $staffWithoutPermission = User::factory()->create(['organization_id' => $this->organization->id]);

        Livewire::actingAs($staffWithoutPermission)
            ->test(BorrowerProfile::class, ['borrower' => $this->borrower])
            ->assertForbidden();
    }
}
