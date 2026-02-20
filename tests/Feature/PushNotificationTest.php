<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use App\Notifications\PushSystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $appOwner;

    protected $org;

    protected $admin;

    protected $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        Permission::firstOrCreate(['name' => 'access_org_notifications']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Borrower']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Setup App Owner
        $ownerEmail = config('app.owner');
        $this->appOwner = User::factory()->create([
            'email' => $ownerEmail,
            'phone' => '2348000000000',
            'settings' => ['push_enabled' => true],
        ]);

        // Setup Org and Users
        $this->org = Organization::factory()->create(['push_notifications_enabled' => true]);

        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'phone' => '2348011111111',
            'settings' => ['push_enabled' => true],
        ]);
        $this->admin->assignRole('Admin');

        $this->borrower = User::factory()->create([
            'organization_id' => $this->org->id,
            'phone' => '2348022222222',
            'settings' => ['push_enabled' => true],
        ]);
        $this->borrower->assignRole('Borrower');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_push_notification_to_specific_recipient()
    {
        Notification::fake();

        // Create a notification for the borrower, linked to their org
        \App\Helpers\SystemLogger::log(
            'Test Notification',
            'Hello Borrower',
            'info',
            'test',
            $this->org, // subject to link to org
            false,
            null,
            'low',
            $this->borrower->id
        );

        Notification::assertSentTo($this->borrower, PushSystemNotification::class);
        Notification::assertNotSentTo($this->admin, PushSystemNotification::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_broadcasts_push_notification_to_org_admins()
    {
        Notification::fake();

        // Create a broadcast notification for the organization
        \App\Helpers\SystemLogger::log(
            'Admin Alert',
            'Something happened in your org',
            'warning',
            'org_event',
            $this->org
        );

        Notification::assertSentTo($this->admin, PushSystemNotification::class);
        Notification::assertNotSentTo($this->borrower, PushSystemNotification::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_respects_push_enabled_setting()
    {
        Notification::fake();

        $this->admin->update(['settings' => ['push_enabled' => false]]);

        \App\Helpers\SystemLogger::log(
            'Admin Alert',
            'Something happened',
            'warning',
            'org_event',
            $this->org
        );

        Notification::assertNotSentTo($this->admin, PushSystemNotification::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_notifies_app_owner_when_new_org_is_created()
    {
        Notification::fake();

        // Create a new organization
        $newOrg = Organization::factory()->create([
            'name' => 'New Lending Co',
        ]);

        // The OrganizationObserver should trigger a notification to the app owner
        Notification::assertSentTo($this->appOwner, PushSystemNotification::class);
        $this->assertDatabaseHas('system_notifications', [
            'recipient_id' => $this->appOwner->id,
            'title' => 'New Organization Onboarded',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_notifies_app_owner_for_platform_events()
    {
        Notification::fake();

        // Platform events usually don't have an organization_id
        // But SystemLogger tries to find one.
        // If we explicitly set recipient_id to app owner, it should work even without org

        SystemNotification::create([
            'recipient_id' => $this->appOwner->id,
            'title' => 'Platform Alert',
            'message' => 'New organization registered',
            'type' => 'info',
            'category' => 'platform',
        ]);

        // This might fail currently because SystemNotificationObserver returns if organization is null
        Notification::assertSentTo($this->appOwner, PushSystemNotification::class);
    }
}
