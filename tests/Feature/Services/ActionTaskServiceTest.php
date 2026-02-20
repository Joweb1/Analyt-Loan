<?php

namespace Tests\Feature\Services;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\ActionTaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActionTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions/roles if needed (though Service might not check them, but the query uses roles)
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Borrower']);
        Role::create(['name' => 'Staff']);

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_generates_overdue_loan_tasks()
    {
        // Create an overdue loan > 3 days ago
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'overdue',
            'updated_at' => now()->subDays(4),
            'loan_number' => 'LN-OVERDUE-001',
        ]);

        ActionTaskService::generateDailyTasks($this->organization->id);

        $this->assertDatabaseHas('system_notifications', [
            'organization_id' => $this->organization->id,
            'category' => 'overdue',
            'subject_id' => $loan->id,
            'subject_type' => Loan::class,
            'is_actionable' => true,
        ]);
    }

    public function test_it_does_not_duplicate_overdue_tasks()
    {
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'overdue',
            'updated_at' => now()->subDays(4),
        ]);

        // Run twice
        ActionTaskService::generateDailyTasks($this->organization->id);
        ActionTaskService::generateDailyTasks($this->organization->id);

        // Expect 1 Overdue task (created by ActionTaskService, not duplicated)
        $this->assertEquals(1, SystemNotification::where('category', 'overdue')->count());

        // Expect 1 Report task
        $this->assertEquals(1, SystemNotification::where('category', 'report')->count());
    }

    public function test_it_generates_inactive_staff_tasks()
    {
        $staff = User::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(4),
            'last_login_at' => null,
        ]);
        $staff->assignRole('Staff');

        ActionTaskService::generateDailyTasks($this->organization->id);

        $this->assertDatabaseHas('system_notifications', [
            'organization_id' => $this->organization->id,
            'category' => 'staff_alert',
            'subject_id' => $staff->id,
            'subject_type' => User::class,
        ]);
    }

    public function test_it_ignores_borrowers_for_inactive_staff_tasks()
    {
        $borrowerUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(4),
            'last_login_at' => null,
        ]);
        $borrowerUser->assignRole('Borrower');

        ActionTaskService::generateDailyTasks($this->organization->id);

        $this->assertDatabaseMissing('system_notifications', [
            'category' => 'staff_alert',
            'subject_id' => $borrowerUser->id,
        ]);
    }

    public function test_it_generates_daily_report_task()
    {
        ActionTaskService::generateDailyTasks($this->organization->id);

        $this->assertDatabaseHas('system_notifications', [
            'organization_id' => $this->organization->id,
            'category' => 'report',
            'subject_id' => $this->organization->id,
            'subject_type' => Organization::class,
            'is_actionable' => true,
        ]);
    }
}
