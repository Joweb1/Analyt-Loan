<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\PushSystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KycNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        Permission::firstOrCreate(['name' => 'access_org_notifications']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Borrower']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_notifies_admin_when_kyc_status_changes_to_pending()
    {
        $org = Organization::factory()->create(['push_notifications_enabled' => true]);
        $admin = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348011111111']);
        $admin->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348022222222']);
        $borrowerUser->assignRole('Borrower');

        $borrower = Borrower::create([
            'user_id' => $borrowerUser->id,
            'organization_id' => $org->id,
            'phone' => $borrowerUser->phone,
            'kyc_status' => 'rejected',
        ]);

        Notification::fake();

        // Act: Change status to pending
        $borrower->update([
            'kyc_status' => 'pending',
        ]);

        // Assert
        Notification::assertSentTo($admin, PushSystemNotification::class);
        $this->assertDatabaseHas('system_notifications', [
            'title' => 'KYC Submitted for Review',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_notifies_staff_with_permission_when_kyc_is_submitted()
    {
        $org = Organization::factory()->create(['push_notifications_enabled' => true]);

        $staff = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348033333333']);
        $staff->givePermissionTo('access_org_notifications');

        $borrowerUser = User::factory()->create(['organization_id' => $org->id, 'phone' => '2348044444444']);
        $borrowerUser->assignRole('Borrower');

        $borrower = Borrower::create([
            'user_id' => $borrowerUser->id,
            'organization_id' => $org->id,
            'phone' => $borrowerUser->phone,
            'kyc_status' => 'pending',
        ]);

        Notification::fake();

        // Act: Update KYC fields
        $borrower->update([
            'address' => '123 Test St',
        ]);

        // Assert notification created in DB
        $this->assertDatabaseHas('system_notifications', [
            'organization_id' => $org->id,
            'title' => 'KYC Profile Updated',
        ]);

        // Assert notification sent to staff
        Notification::assertSentTo($staff, PushSystemNotification::class);
    }
}
