<?php

namespace Tests\Feature\Livewire\Borrower;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BorrowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $borrower;

    protected $org;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->org->id,
            'phone' => '2348012345678',
        ]);
        $this->borrower = Borrower::create([
            'user_id' => $this->user->id,
            'organization_id' => $this->org->id,
            'phone' => $this->user->phone,
            'kyc_status' => 'approved',
            'trust_score' => 50,
        ]);

        $this->product = LoanProduct::create([
            'organization_id' => $this->org->id,
            'name' => 'Test Product',
            'default_interest_rate' => 10,
            'default_duration' => 3,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_render_borrow_page()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Borrower\Borrow::class)
            ->assertStatus(200)
            ->assertSee($this->product->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_installments_correctly()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Borrower\Borrow::class)
            ->set('amount', 10000)
            ->set('duration', 3)
            ->set('duration_unit', 'month')
            ->set('repayment_cycle', 'monthly')
            ->assertSet('num_repayments', 3)
            ->assertSet('calculated.installment_amount', 11000 / 3); // 10000 + 1000 interest
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_submits_loan_application_with_applied_status()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Borrower\Borrow::class)
            ->set('amount', 5000)
            ->call('submitApplication')
            ->assertSet('showSuccess', true);

        $this->assertDatabaseHas('loans', [
            'borrower_id' => $this->borrower->id,
            'amount' => 5000,
            'status' => 'applied',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_borrowing_if_active_loan_exists()
    {
        Loan::create([
            'borrower_id' => $this->borrower->id,
            'organization_id' => $this->org->id,
            'amount' => 1000,
            'loan_number' => 'EXISTING',
            'status' => 'active',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Borrower\Borrow::class)
            ->assertRedirect(route('borrower.home'));
    }
}
