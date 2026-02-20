<?php

namespace Tests\Unit;

use App\Helpers\SystemLogger;
use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_logs_notification_with_correct_context()
    {
        $this->actingAs($this->user);

        $notification = SystemLogger::log(
            'Test Title',
            'Test Message',
            'info',
            'loan',
            null,
            true,
            '/test-link',
            'high'
        );

        $this->assertInstanceOf(SystemNotification::class, $notification);
        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test Message', $notification->message);
        $this->assertEquals('info', $notification->type);
        $this->assertEquals('loan', $notification->category);
        $this->assertTrue($notification->is_actionable);
        $this->assertEquals('/test-link', $notification->action_link);
        $this->assertEquals('high', $notification->priority);
        $this->assertEquals($this->user->id, $notification->user_id);
        $this->assertEquals($this->organization->id, $notification->organization_id);
    }

    public function test_it_extracts_organization_from_subject()
    {
        $otherOrg = Organization::factory()->create();
        $subject = new class
        {
            public $id = '123';

            public $organization_id = 'test-org-id';
        };
        $subject->organization_id = $otherOrg->id;

        $notification = SystemLogger::log('Title', 'Message', 'info', null, $subject);

        $this->assertEquals($otherOrg->id, $notification->organization_id);
    }

    public function test_helper_methods_work()
    {
        $this->actingAs($this->user);

        $success = SystemLogger::success('Success', 'Done');
        $this->assertEquals('success', $success->type);

        $warning = SystemLogger::warning('Warning', 'Careful');
        $this->assertEquals('warning', $warning->type);

        $danger = SystemLogger::danger('Danger', 'Stop');
        $this->assertEquals('danger', $danger->type);

        $action = SystemLogger::action('Action', 'Do this', '/link');
        $this->assertEquals('info', $action->type);
        $this->assertTrue($action->is_actionable);
        $this->assertEquals('/link', $action->action_link);
    }
}
