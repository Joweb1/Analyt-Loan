<?php

namespace Tests\Feature;

use App\Livewire\Admin\LoanApproval;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_approve_loan()
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
            'status' => 'applied',
        ]);

        Livewire::actingAs($user)
            ->test(LoanApproval::class)
            ->assertSee($loan->loan_number)
            ->call('approveLoan', $loan->id);

        $this->assertEquals('approved', $loan->fresh()->status);
    }
}
