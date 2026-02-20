<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SavingsDetails;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\SavingsAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected Borrower $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $borrowerUser->id,
        ]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(SavingsDetails::class, ['borrower' => $this->borrower])
            ->assertStatus(200);
    }

    public function test_it_records_a_deposit()
    {
        Livewire::actingAs($this->admin)
            ->test(SavingsDetails::class, ['borrower' => $this->borrower])
            ->call('openTransactionModal', 'deposit')
            ->set('amount', 5000)
            ->set('transactionDate', now()->format('Y-m-d'))
            ->call('submitTransaction')
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success' && str_contains($params[0]['message'], 'recorded');
            });

        $this->assertDatabaseHas('savings_accounts', [
            'borrower_id' => $this->borrower->id,
            'balance' => 5000,
        ]);

        $this->assertDatabaseHas('savings_transactions', [
            'amount' => 5000,
            'type' => 'deposit',
        ]);
    }

    public function test_it_records_a_withdrawal()
    {
        $account = SavingsAccount::factory()->create([
            'borrower_id' => $this->borrower->id,
            'organization_id' => $this->organization->id,
            'balance' => 10000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SavingsDetails::class, ['borrower' => $this->borrower])
            ->call('openTransactionModal', 'withdrawal')
            ->set('amount', 4000)
            ->set('transactionDate', now()->format('Y-m-d'))
            ->call('submitTransaction');

        $this->assertEquals(6000, $account->fresh()->balance);
        $this->assertDatabaseHas('savings_transactions', [
            'amount' => 4000,
            'type' => 'withdrawal',
        ]);
    }

    public function test_it_prevents_withdrawal_if_insufficient_balance()
    {
        SavingsAccount::factory()->create([
            'borrower_id' => $this->borrower->id,
            'organization_id' => $this->organization->id,
            'balance' => 1000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SavingsDetails::class, ['borrower' => $this->borrower])
            ->call('openTransactionModal', 'withdrawal')
            ->set('amount', 2000)
            ->call('submitTransaction')
            ->assertHasErrors(['amount']);
    }
}
