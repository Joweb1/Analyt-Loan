<?php

namespace Tests\Feature;

use App\Livewire\Settings\GeneralSettings;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TimeControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    #[Test]
    public function setting_manual_date_overrides_global_time()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $manualDate = '2026-12-25';

        Livewire::actingAs($admin)
            ->test(GeneralSettings::class)
            ->set('use_manual_date', true)
            ->set('operating_date', $manualDate)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($org->fresh()->use_manual_date);
        $this->assertEquals($manualDate, \Carbon\Carbon::parse($org->fresh()->operating_date)->format('Y-m-d'));

        // Verify global time override
        Carbon::setTestNow($org->fresh()->operating_date);
        $this->assertEquals($manualDate, now()->format('Y-m-d'));
        Carbon::setTestNow();
    }

    #[Test]
    public function simulation_date_is_maintained_across_multiple_requests()
    {
        $org = Organization::factory()->create([
            'use_manual_date' => true,
            'operating_date' => '2028-10-10',
        ]);
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $this->actingAs($admin);

        // First request to dashboard
        $response1 = $this->get(route('dashboard'));
        $response1->assertStatus(200);
        $this->assertEquals('2028-10-10', now()->format('Y-m-d'));

        // Second request to reports
        $response2 = $this->get(route('reports'));
        $response2->assertStatus(200);
        $this->assertEquals('2028-10-10', now()->format('Y-m-d'));

        Carbon::setTestNow(); // Clean up
    }

    #[Test]
    public function advancing_time_triggers_overdue_sync_and_penalties()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);

        $startDate = Carbon::parse('2026-01-01');
        Carbon::setTestNow($startDate);

        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'loan_number' => 'LN-TIME-1',
            'status' => 'active',
            'penalty_value' => 100,
            'penalty_type' => 'fixed',
            'penalty_frequency' => 'daily',
        ]);

        $dueDate = $startDate->copy()->addDays(4); // 2026-01-05
        $schedule = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 10000,
            'principal_amount' => 10000,
            'interest_amount' => 0,
            'due_date' => $dueDate,
            'status' => 'applied',
        ]);

        $futureDate = '2026-01-07';

        Livewire::actingAs($admin)
            ->test(GeneralSettings::class)
            ->set('use_manual_date', true)
            ->set('operating_date', $futureDate)
            ->call('save');

        $this->assertEquals('overdue', $loan->fresh()->status);
        $this->assertEquals('overdue', $schedule->fresh()->status);
        $this->assertEquals(200, (float) $schedule->fresh()->penalty_amount);

        Carbon::setTestNow();
    }

    #[Test]
    public function backdating_time_reverts_overdue_status()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $now = Carbon::parse('2026-01-10');
        Carbon::setTestNow($now);

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'status' => 'overdue',
        ]);

        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000,
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'due_date' => '2026-01-05',
            'status' => 'overdue',
        ]);

        $pastDate = '2026-01-01';

        Livewire::actingAs($admin)
            ->test(GeneralSettings::class)
            ->set('use_manual_date', true)
            ->set('operating_date', $pastDate)
            ->call('save');

        $this->assertEquals('active', $loan->fresh()->status);

        Carbon::setTestNow();
    }

    #[Test]
    public function new_records_use_operating_date_as_timestamp()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $manualDate = Carbon::parse('2027-05-20');
        $org->update(['use_manual_date' => true, 'operating_date' => $manualDate]);

        $this->actingAs($admin);
        Carbon::setTestNow($manualDate);

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $loan = Loan::factory()->create(['organization_id' => $org->id]);
        $repayment = Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 500,
            'paid_at' => now(),
        ]);

        $this->assertEquals('2027-05-20', $repayment->created_at->format('Y-m-d'));
        $this->assertEquals('2027-05-20', $borrower->created_at->format('Y-m-d'));

        Carbon::setTestNow();
    }

    #[Test]
    public function switching_to_manual_for_today_triggers_maintenance()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $today = now()->startOfDay();

        // Ensure we have an overdue schedule that hasn't been processed yet
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'status' => 'active',
        ]);

        $pastDueDate = $today->copy()->subDays(1);
        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'due_date' => $pastDueDate,
            'status' => 'applied',
        ]);

        // Just switching to manual for the same day should trigger maintenance and catch the overdue
        Livewire::actingAs($admin)
            ->test(GeneralSettings::class)
            ->set('use_manual_date', true)
            ->set('operating_date', $today->format('Y-m-d'))
            ->call('save');

        $this->assertEquals('overdue', $loan->fresh()->status);
        Carbon::setTestNow();
    }
}
