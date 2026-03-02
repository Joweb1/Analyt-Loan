<?php

namespace Tests\Feature;

use App\Livewire\CollectionEntry;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_render_collection_entry_page_and_add_repayment()
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
            'status' => 'overdue',
            'amount' => 10000,
            'interest_rate' => 10,
            'num_repayments' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(CollectionEntry::class)
            ->assertSee($loan->loan_number)
            ->call('selectLoan', $loan->id)
            ->assertSet('showRepaymentModal', true)
            ->set('amount', 11000) // 10000 principal + 1000 interest
            ->call('addRepayment')
            ->assertSet('showRepaymentModal', false);

        $this->assertDatabaseHas('repayments', [
            'loan_id' => $loan->id,
            'amount' => 11000,
            'collected_by' => $user->id,
        ]);
    }
}
