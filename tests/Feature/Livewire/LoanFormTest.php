<?php

namespace Tests\Feature\Livewire;

use App\Livewire\LoanForm;
use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);

        // Create necessary roles
        Role::create(['name' => 'Borrower']);
    }

    public function test_loan_form_component_renders(): void
    {
        Livewire::actingAs($this->user)
            ->test(LoanForm::class)
            ->assertStatus(200);
    }

    public function test_can_create_loan_with_sufficient_collateral(): void
    {
        // Setup data
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $collateral = Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'value' => 60000,
            'status' => 'deposited',
            'loan_id' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(LoanForm::class)
            ->set('borrowerId', $borrower->id)
            ->set('loan_number', 'LN-TEST-001')
            ->set('amount', 100000)
            ->set('loan_product', 'Personal Loan') // Assuming field exists
            ->set('interest_rate', 10)
            ->set('duration', 6)
            ->set('duration_unit', 'month')
            ->set('repayment_cycle', 'monthly')
            ->set('collateralId', $collateral->id)
            ->call('saveLoan');

        $this->assertDatabaseHas('loans', [
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'organization_id' => $this->organization->id,
        ]);

        // Check if collateral is linked (LoanService handles this)
        $loan = Loan::latest()->first();
        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
            'loan_id' => $loan->id,
            'status' => 'in_vault',
        ]);
    }

    public function test_cannot_create_loan_with_insufficient_collateral(): void
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $collateral = Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'value' => 40000, // 40%
            'status' => 'deposited',
            'loan_id' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(LoanForm::class)
            ->set('borrowerId', $borrower->id)
            ->set('loan_number', 'LN-TEST-002')
            ->set('amount', 100000)
            ->set('collateralId', $collateral->id)
            ->set('loan_product', 'Personal Loan')
            ->set('interest_rate', 10)
            ->set('duration', 6)
            ->set('duration_unit', 'month')
            ->set('repayment_cycle', 'monthly')
            ->set('num_repayments', 6)
            ->set('interest_type', 'year')
            ->set('release_date', now()->format('Y-m-d'))
            ->call('saveLoan')
            ->assertHasErrors(); // Validation or Exception?

        // Assuming LoanForm catches the exception and adds an error, or the validation rule handles it.
        // If LoanService throws exception, Livewire might not catch it unless try-catch is in component.
        // Let's assume standard behavior for now.
    }
}
