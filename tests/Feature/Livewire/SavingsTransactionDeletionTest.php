<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SavingsDetails;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsTransactionDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_delete_unlinked_savings_transaction()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $account = SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'TEST-123',
            'balance' => 5000,
            'status' => 'active',
        ]);

        $transaction = SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'amount' => 5000,
            'type' => 'deposit',
            'staff_id' => $admin->id,
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(SavingsDetails::class, ['borrower' => $borrower])
            ->call('deleteTransaction', $transaction->id)
            ->assertDispatched('custom-alert');

        $this->assertDatabaseMissing('savings_transactions', ['id' => $transaction->id]);
        $this->assertEquals(0, $account->fresh()->balance);
    }

    public function test_cannot_delete_transaction_linked_to_repayment()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $account = SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'TEST-456',
            'balance' => 2000,
            'status' => 'active',
        ]);

        // Create a fake repayment to link to
        $loan = \App\Models\Loan::factory()->create(['organization_id' => $org->id, 'borrower_id' => $borrower->id]);
        $repayment = Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 10000,
            'extra_amount' => 2000,
            'paid_at' => now(),
        ]);

        $transaction = SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'repayment_id' => $repayment->id, // LINKED
            'amount' => 2000,
            'type' => 'deposit',
            'staff_id' => $admin->id,
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(SavingsDetails::class, ['borrower' => $borrower])
            ->call('deleteTransaction', $transaction->id)
            ->assertDispatched('custom-alert');

        $this->assertDatabaseHas('savings_transactions', ['id' => $transaction->id]);
        $this->assertEquals(2000, $account->fresh()->balance);
    }

    public function test_unauthorized_user_cannot_delete_savings()
    {
        $org = Organization::factory()->create();
        $staff = User::factory()->create(['organization_id' => $org->id]);
        // No delete permission

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $account = SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'TEST-789',
            'balance' => 1000,
            'status' => 'active',
        ]);

        $transaction = SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'amount' => 1000,
            'type' => 'deposit',
            'staff_id' => $staff->id,
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($staff)
            ->test(SavingsDetails::class, ['borrower' => $borrower])
            ->call('deleteTransaction', $transaction->id)
            ->assertDispatched('custom-alert');

        $this->assertDatabaseHas('savings_transactions', ['id' => $transaction->id]);
    }
}
