<?php

namespace Tests\Feature\Livewire;

use App\Livewire\LoanDetails;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = ['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist', 'Borrower'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');

        // Create borrower and loan
        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $borrowerUser->assignRole('Borrower');

        // Need a borrower record linked to user
        $borrower = \App\Models\Borrower::factory()->create([
            'user_id' => $borrowerUser->id,
            'organization_id' => $this->organization->id,
        ]);

        $this->loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'interest_rate' => 10, // 10%
            'repayment_cycle' => 'monthly',
            'num_repayments' => 5, // 5 months
            'status' => 'active',
            'release_date' => now()->subMonth(), // started a month ago
        ]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->assertStatus(200);
    }

    public function test_it_generates_schedule()
    {
        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->call('generateSchedule');

        $this->assertDatabaseCount('scheduled_repayments', 5);

        // Total Principal + Interest should match Loan Amount + Interest
        // Principal: 100k. Interest: 10% of 100k = 10k. Total = 110k.
        // 5 installments -> 22k each.

        $schedule = ScheduledRepayment::first();
        $expectedAmount = (100000 + 10000) / 5;

        $this->assertEquals($expectedAmount, $schedule->principal_amount + $schedule->interest_amount);
    }

    public function test_it_adds_repayment_and_updates_schedule()
    {
        // First generate schedule
        $this->loan->scheduledRepayments()->create([
            'due_date' => now()->subDay(),
            'principal_amount' => 20000,
            'interest_amount' => 2000,
            'penalty_amount' => 0,
            'installment_number' => 1,
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->set('amount', 22000)
            ->set('payment_method', 'Bank Transfer')
            ->set('collected_by', $this->admin->id)
            ->set('paid_at', now()->format('Y-m-d'))
            ->call('addRepayment')
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success';
            });

        $this->assertDatabaseHas('repayments', [
            'loan_id' => $this->loan->id,
            'amount' => 22000,
        ]);

        // Check if schedule updated
        $this->assertDatabaseHas('scheduled_repayments', [
            'loan_id' => $this->loan->id,
            'status' => 'paid',
            'paid_amount' => 22000,
        ]);
    }

    public function test_it_closes_loan_when_fully_paid()
    {
        // Total Due: 110,000
        // Pay full amount
        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->set('amount', 110000)
            ->set('payment_method', 'Cash')
            ->set('collected_by', $this->admin->id)
            ->set('paid_at', now()->format('Y-m-d'))
            ->call('addRepayment');

        $this->loan->refresh();
        $this->assertEquals('repaid', $this->loan->status);
    }

    public function test_it_reopens_loan_if_repayment_deleted()
    {
        // Pay full amount first
        /** @var \App\Models\Repayment $repayment */
        $repayment = $this->loan->repayments()->create([
            'amount' => 110000,
            'payment_method' => 'Cash',
            'collected_by' => $this->admin->id,
            'paid_at' => now(),
        ]);

        $this->loan->update(['status' => 'repaid']);

        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->call('deleteRepayment', $repayment->id);

        $this->loan->refresh();
        $this->assertEquals('active', $this->loan->status);
    }

    public function test_it_allows_any_amount_when_flexible_repayments_enabled()
    {
        $this->organization->update(['allow_flexible_repayments' => true]);
        $this->admin->refresh();

        // Scheduled amount is 22,000 per installment
        // Try to pay only 5,000
        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->set('amount', 5000)
            ->set('payment_method', 'Cash')
            ->set('collected_by', $this->admin->id)
            ->set('paid_at', now()->format('Y-m-d'))
            ->call('addRepayment')
            ->assertHasNoErrors()
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success';
            });

        $this->assertDatabaseHas('repayments', [
            'loan_id' => $this->loan->id,
            'amount' => 5000,
        ]);
    }

    public function test_it_restricts_amount_when_flexible_repayments_disabled()
    {
        $this->organization->update(['allow_flexible_repayments' => false]);

        // Minimum required is 22,000
        Livewire::actingAs($this->admin)
            ->test(LoanDetails::class, ['loan' => $this->loan])
            ->set('amount', 5000)
            ->set('payment_method', 'Cash')
            ->set('collected_by', $this->admin->id)
            ->set('paid_at', now()->format('Y-m-d'))
            ->call('addRepayment')
            ->assertHasErrors(['amount']);
    }
}
