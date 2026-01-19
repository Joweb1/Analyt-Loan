<?php

namespace Tests\Feature\Livewire;

use App\Livewire\LoanForm;
use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_form_component_renders(): void
    {
        Livewire::test(LoanForm::class)
            ->assertStatus(200);
    }

    public function test_can_create_loan_with_sufficient_collateral(): void
    {
        $borrower = Borrower::factory()->create();
        $loan = Loan::factory()->create(['borrower_id' => $borrower->id, 'amount' => 1000]);
        $collateral = Collateral::factory()->create([
            'value' => 600,
            'loan_id' => $loan->id, // Associate collateral with the created loan
        ]);

        Livewire::test(LoanForm::class)
            ->set('borrowerId', $borrower->id)
            ->set('amount', 1000)
            ->set('collateralId', $collateral->id)
            ->call('saveLoan');

        $this->assertDatabaseHas('loans', [
            'borrower_id' => $borrower->id,
            'amount' => 1000,
        ]);

        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
            'loan_id' => Loan::first()->id,
        ]);
    }

    public function test_cannot_create_loan_with_insufficient_collateral(): void
    {
        $borrower = Borrower::factory()->create();
        $loan = Loan::factory()->create(['borrower_id' => $borrower->id, 'amount' => 1000]);
        $collateral = Collateral::factory()->create([
            'value' => 400,
            'loan_id' => $loan->id, // Associate collateral with the created loan
        ]);

        Livewire::test(LoanForm::class)
            ->set('borrowerId', $borrower->id)
            ->set('amount', 1000)
            ->set('collateralId', $collateral->id)
            ->call('saveLoan')
            ->assertHasErrors(['collateralId']);
    }
}
