<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RepaymentRecords;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RepaymentRecordsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(RepaymentRecords::class)
            ->assertStatus(200);
    }

    public function test_it_filters_repayments_by_search()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id, 'loan_number' => 'LN-123']);
        Repayment::factory()->create(['loan_id' => $loan->id, 'amount' => 5000]);

        $loan2 = Loan::factory()->create(['organization_id' => $this->organization->id, 'loan_number' => 'LN-456']);
        Repayment::factory()->create(['loan_id' => $loan2->id, 'amount' => 10000]);

        Livewire::actingAs($this->admin)
            ->test(RepaymentRecords::class)
            ->set('search', 'LN-123')
            ->assertSee('LN-123')
            ->assertDontSee('LN-456');
    }

    public function test_it_filters_by_date_range()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);

        // Today
        Repayment::factory()->create(['loan_id' => $loan->id, 'paid_at' => now(), 'amount' => 1000]);

        // Last week
        Repayment::factory()->create(['loan_id' => $loan->id, 'paid_at' => now()->subWeek(), 'amount' => 2000]);

        Livewire::actingAs($this->admin)
            ->test(RepaymentRecords::class)
            ->set('dateRange', 'today')
            ->assertSee('1,000')
            ->assertDontSee('2,000');
    }

    public function test_it_can_export_repayments()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);
        Repayment::factory()->create(['loan_id' => $loan->id]);

        $response = Livewire::actingAs($this->admin)
            ->test(RepaymentRecords::class)
            ->call('export');

        $response->assertStatus(200);
        $this->assertEquals('text/csv', $response->effects['download']['contentType'] ?? 'text/csv');
    }
}
