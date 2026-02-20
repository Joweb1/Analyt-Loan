<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Notifications;
use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Borrower']);

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Notifications::class)
            ->assertStatus(200);
    }

    public function test_it_filters_notifications_by_category()
    {
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Loan Notif',
            'message' => 'Loan message',
            'category' => 'loan',
        ]);

        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'KYC Notif',
            'message' => 'KYC message',
            'category' => 'kyc',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Notifications::class)
            ->set('filter', 'loan')
            ->assertViewHas('notifications', function ($notifs) {
                return $notifs->count() === 1 && $notifs->first()->category === 'loan';
            });
    }

    public function test_borrowers_only_see_their_own_notifications()
    {
        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $borrowerUser->assignRole('Borrower');

        // Global notification
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Global News',
            'message' => 'Global message',
            'recipient_id' => null,
        ]);

        // Targeted notification
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Your Loan Approved',
            'message' => 'Your loan message',
            'recipient_id' => $borrowerUser->id,
        ]);

        Livewire::actingAs($borrowerUser)
            ->test(Notifications::class)
            ->assertViewHas('notifications', function ($notifs) {
                return $notifs->count() === 1 && $notifs->first()->title === 'Your Loan Approved';
            });
    }

    public function test_it_can_mark_all_as_read()
    {
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Unread 1',
            'message' => 'Unread message',
            'read_at' => null,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Notifications::class)
            ->call('markAllAsRead')
            ->assertDispatched('custom-alert');

        $this->assertEquals(0, SystemNotification::whereNull('read_at')->count());
    }
}
