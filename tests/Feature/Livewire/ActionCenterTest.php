<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ActionCenter;
use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActionCenterTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(ActionCenter::class)
            ->assertStatus(200);
    }

    public function test_it_displays_actionable_notifications()
    {
        // One actionable notification for this organization
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Pending Loan Approval',
            'message' => 'Please approve this loan',
            'is_actionable' => true,
            'category' => 'loan',
            'priority' => 'high',
        ]);

        // One non-actionable notification
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'General Info',
            'message' => 'System update',
            'is_actionable' => false,
        ]);

        // One actionable for another organization
        $otherOrg = Organization::factory()->create();
        SystemNotification::create([
            'organization_id' => $otherOrg->id,
            'title' => 'Another Pending Action',
            'message' => 'Other org task',
            'is_actionable' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ActionCenter::class)
            ->assertSet('tasks', function ($tasks) {
                return $tasks->count() === 1 && $tasks->first()['title'] === 'Pending Loan Approval';
            });
    }

    public function test_it_can_mark_action_as_resolved()
    {
        $notif = SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Overdue Task',
            'message' => 'Your task is overdue',
            'is_actionable' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ActionCenter::class)
            ->call('markAsResolved', $notif->id)
            ->assertDispatched('custom-alert')
            ->assertSet('tasks', fn ($tasks) => $tasks->isEmpty());

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_it_respects_recipient_restriction()
    {
        // Notification targeted specifically to the admin
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'recipient_id' => $this->admin->id,
            'title' => 'Admin Only Task',
            'message' => 'Task for admin',
            'is_actionable' => true,
        ]);

        // Notification targeted to another user
        $otherUser = User::factory()->create(['organization_id' => $this->organization->id]);
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'recipient_id' => $otherUser->id,
            'title' => 'Other User Task',
            'message' => 'Task for other user',
            'is_actionable' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ActionCenter::class)
            ->assertSet('tasks', function ($tasks) {
                return $tasks->count() === 1 && $tasks->first()['title'] === 'Admin Only Task';
            });
    }
}
