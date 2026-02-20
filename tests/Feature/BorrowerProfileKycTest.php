<?php

namespace Tests\Feature;

use App\Livewire\BorrowerProfile;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BorrowerProfileKycTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'manage_borrowers']);
        Role::firstOrCreate(['name' => 'Admin'])->givePermissionTo('manage_borrowers');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_approve_kyc()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348011111111']);
        $admin->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348022222222']);
        $borrower = Borrower::create([
            'user_id' => $borrowerUser->id,
            'organization_id' => $org->id,
            'phone' => $borrowerUser->phone,
            'kyc_status' => 'pending',
        ]);

        // Simulate a notification
        \App\Helpers\SystemLogger::action('KYC Submission', 'Message', 'link', 'kyc', $borrower);

        $this->actingAs($admin);

        Livewire::test(BorrowerProfile::class, ['borrower' => $borrower])
            ->call('approveKyc')
            ->assertRedirect(route('customer'));

        $this->assertEquals('approved', $borrower->fresh()->kyc_status);
        $this->assertDatabaseHas('system_notifications', [
            'title' => 'KYC Approved',
            'category' => 'kyc',
        ]);

        // Assert old notification is resolved
        $this->assertDatabaseMissing('system_notifications', [
            'subject_id' => $borrower->id,
            'subject_type' => Borrower::class,
            'category' => 'kyc',
            'read_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_decline_kyc_with_reason()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348011111111']);
        $admin->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348022222222']);
        $borrower = Borrower::create([
            'user_id' => $borrowerUser->id,
            'organization_id' => $org->id,
            'phone' => $borrowerUser->phone,
            'kyc_status' => 'pending',
        ]);

        // Simulate a notification
        \App\Helpers\SystemLogger::action('KYC Submission', 'Message', 'link', 'kyc', $borrower);

        $this->actingAs($admin);

        Livewire::test(BorrowerProfile::class, ['borrower' => $borrower])
            ->set('rejection_reason', 'Documents are blurry')
            ->call('declineKyc')
            ->assertSet('kyc_status', 'rejected');

        $this->assertEquals('rejected', $borrower->fresh()->kyc_status);
        $this->assertEquals('Documents are blurry', $borrower->fresh()->rejection_reason);
        $this->assertDatabaseHas('system_notifications', [
            'title' => 'KYC Rejected',
            'category' => 'kyc',
        ]);

        // Assert notification resolved
        $this->assertDatabaseMissing('system_notifications', [
            'subject_id' => $borrower->id,
            'subject_type' => Borrower::class,
            'category' => 'kyc',
            'read_at' => null,
        ]);
    }
}
