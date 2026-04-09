<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanExtraRepaymentToSavingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_extra_repayment_balance_is_moved_to_savings()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        // Create a loan of 10,000 with 10% interest (Total 11,000)
        $loan = Loan::factory()->create([
            'organization_id' => $organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'active',
            'amount' => 10000.0,
            'interest_rate' => 10,
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'num_repayments' => 1,
        ]);

        // Create one schedule for the full amount
        $loan->scheduledRepayments()->create([
            'due_date' => now()->addMonth(),
            'principal_amount' => 10000.0,
            'interest_amount' => 1000.0,
            'status' => 'applied',
            'installment_number' => 1,
        ]);

        // Repay 15,000 (4,000 extra)
        $this->actingAs($user);
        /** @var \App\Models\Repayment $repayment */
        $repayment = $loan->repayments()->create([
            'amount' => 15000.0,
            'paid_at' => now(),
            'payment_method' => 'Cash',
            'collected_by' => $user->id,
            'extra_amount' => 4000.0, // Normally calculated by controller/Livewire
        ]);

        // Trigger the logic
        $loan->refreshRepaymentStatus();

        // Assert loan is repaid
        $this->assertEquals('repaid', $loan->fresh()->status);

        // Assert savings account exists and has 4,000 balance
        $savingsAccount = $borrower->fresh()->savingsAccount;
        $this->assertNotNull($savingsAccount);
        $this->assertEquals(400000, $savingsAccount->balance->getMinorAmount());

        // Assert transaction record exists
        $this->assertDatabaseHas('savings_transactions', [
            'repayment_id' => $repayment->id,
            'amount' => 400000,
            'type' => 'deposit',
        ]);
    }

    public function test_large_repayment_calculation_only_moves_excess_to_savings()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        // Loan of 20,000 total
        $loan = Loan::factory()->create([
            'organization_id' => $organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'active',
            'amount' => 20000,
            'interest_rate' => 0,
            'num_repayments' => 1,
        ]);

        $loan->scheduledRepayments()->create([
            'due_date' => now()->addMonth(),
            'principal_amount' => 20000,
            'interest_amount' => 0,
            'status' => 'applied',
            'installment_number' => 1,
        ]);

        // Repay 50,000 (30,000 extra)
        $this->actingAs($user);
        $repayment = $loan->repayments()->create([
            'amount' => 50000,
            'paid_at' => now(),
            'payment_method' => 'Cash',
            'collected_by' => $user->id,
            'extra_amount' => 30000,
        ]);

        $loan->refreshRepaymentStatus();

        $this->assertEquals('repaid', $loan->fresh()->status);

        $savingsAccount = $borrower->fresh()->savingsAccount;
        $this->assertNotNull($savingsAccount);
        $this->assertEquals(3000000, $savingsAccount->balance->getMinorAmount());
    }

    public function test_deleting_repayment_removes_savings_transaction()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        $loan = Loan::factory()->create([
            'organization_id' => $organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'active',
            'amount' => 10000,
            'interest_rate' => 0,
            'num_repayments' => 1,
        ]);

        $loan->scheduledRepayments()->create([
            'due_date' => now()->addMonth(),
            'principal_amount' => 10000,
            'interest_amount' => 0,
            'status' => 'applied',
            'installment_number' => 1,
        ]);

        $this->actingAs($user);
        $repayment = $loan->repayments()->create([
            'amount' => 15000,
            'paid_at' => now(),
            'payment_method' => 'Cash',
            'collected_by' => $user->id,
            'extra_amount' => 5000,
        ]);

        $loan->refreshRepaymentStatus();

        $savingsAccount = $borrower->fresh()->savingsAccount;
        $this->assertEquals(500000, $savingsAccount->balance->getMinorAmount());
        $this->assertEquals(1, $savingsAccount->transactions()->count());

        // Delete the repayment
        $repayment->delete();

        // Assert savings transaction is gone and balance is reverted
        $this->assertEquals(0, $savingsAccount->fresh()->balance->getMinorAmount());
        $this->assertEquals(0, $savingsAccount->transactions()->count());
    }
}
