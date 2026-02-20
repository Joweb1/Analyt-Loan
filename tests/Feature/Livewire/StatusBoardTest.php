<?php

namespace Tests\Feature\Livewire;

use App\Livewire\StatusBoard;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatusBoardTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(StatusBoard::class)
            ->assertStatus(200);
    }

    public function test_it_filters_loans_by_search()
    {
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_number' => 'LN-SEARCH-ME',
        ]);

        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_number' => 'LN-IGNORE-ME',
        ]);

        Livewire::actingAs($this->admin)
            ->test(StatusBoard::class)
            ->set('search', 'SEARCH')
            ->assertSee('LN-SEARCH-ME')
            ->assertDontSee('LN-IGNORE-ME');
    }

    public function test_it_calculates_correct_metrics()
    {
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'amount' => 10000,
        ]);

        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'repaid',
            'amount' => 5000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(StatusBoard::class)
            ->assertSet('counts.active', 1)
            ->assertSet('counts.repaid', 1)
            ->assertSet('sums.active', 10000)
            ->assertSet('sums.repaid', 5000);
    }
}
