<?php

namespace Tests\Feature\Livewire\Cashbook;

use App\Livewire\Cashbook\MonthRecord;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonthRecordTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'admin',
        ]);
        $this->admin->assignRole('Admin');

        $this->staff = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'staff',
        ]);
        $this->staff->assignRole('Staff');
    }

    public function test_admin_can_set_budget()
    {
        Livewire::actingAs($this->admin)
            ->test(MonthRecord::class)
            ->set('newBudgetAmount', 5000)
            ->call('saveBudget')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('expense_budgets', [
            'organization_id' => $this->organization->id,
            'total_budget_amount' => 500000,
        ]);
    }

    public function test_staff_cannot_set_budget()
    {
        Livewire::actingAs($this->staff)
            ->test(MonthRecord::class)
            ->set('newBudgetAmount', 5000)
            ->call('saveBudget');

        $this->assertDatabaseMissing('expense_budgets', [
            'organization_id' => $this->organization->id,
            'total_budget_amount' => 500000,
        ]);
    }

    public function test_admin_can_set_initial_balance()
    {
        Livewire::actingAs($this->admin)
            ->test(MonthRecord::class)
            ->set('newOpeningBalanceAmount', 10000)
            ->call('saveBalance')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('account_balances', [
            'organization_id' => $this->organization->id,
            'opening_balance' => 1000000,
        ]);
    }

    public function test_staff_cannot_set_initial_balance()
    {
        Livewire::actingAs($this->staff)
            ->test(MonthRecord::class)
            ->set('newOpeningBalanceAmount', 10000)
            ->call('saveBalance');

        $this->assertDatabaseMissing('account_balances', [
            'organization_id' => $this->organization->id,
            'opening_balance' => 1000000,
        ]);
    }
}
