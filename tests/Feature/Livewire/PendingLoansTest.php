<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PendingLoans;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingLoansTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->staff = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->staff)
            ->test(PendingLoans::class)
            ->assertStatus(200);
    }

    public function test_it_lists_pending_loans()
    {
        $borrower = \App\Models\Borrower::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'applied',
        ]);

        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($this->staff)
            ->test(PendingLoans::class)
            ->assertViewHas('loans', function ($loans) {
                return $loans->count() === 1 && $loans->first()->status === 'applied';
            });
    }
}
