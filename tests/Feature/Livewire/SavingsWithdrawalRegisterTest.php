<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SavingsWithdrawalRegister;
use App\Models\Organization;
use App\Models\SavingsAccount;
use App\Models\SavingsWithdrawal;
use App\Models\User;
use App\Services\TenantSession;
use App\ValueObjects\Money;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SavingsWithdrawalRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(SavingsWithdrawalRegister::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_filters_by_search()
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $account1 = SavingsAccount::factory()->create(['user_id' => $user1->id, 'organization_id' => $this->organization->id]);
        SavingsWithdrawal::factory()->create([
            'organization_id' => $this->organization->id,
            'savings_account_id' => $account1->id,
            'reference' => 'W-111',
            'amount_withdrawn' => new Money(10000),
            'transaction_date' => now(),
        ]);

        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        $account2 = SavingsAccount::factory()->create(['user_id' => $user2->id, 'organization_id' => $this->organization->id]);
        SavingsWithdrawal::factory()->create([
            'organization_id' => $this->organization->id,
            'savings_account_id' => $account2->id,
            'reference' => 'W-222',
            'amount_withdrawn' => new Money(20000),
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(SavingsWithdrawalRegister::class)
            ->set('search', 'John')
            ->assertSee('W-111')
            ->assertDontSee('W-222');
    }

    #[Test]
    public function it_allows_admin_to_update_status()
    {
        $user = User::factory()->create();
        $account = SavingsAccount::factory()->create(['user_id' => $user->id, 'organization_id' => $this->organization->id]);
        $withdrawal = SavingsWithdrawal::factory()->create([
            'organization_id' => $this->organization->id,
            'savings_account_id' => $account->id,
            'status' => 'pending',
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(SavingsWithdrawalRegister::class)
            ->call('updateStatus', $withdrawal->id, 'approved')
            ->assertDispatched('notify');

        $this->assertEquals('approved', $withdrawal->refresh()->status);
    }

    #[Test]
    public function it_restricts_status_updates_to_admins()
    {
        $staff = User::factory()->create(['organization_id' => $this->organization->id]);
        $staff->assignRole('Collection Officer');

        $user = User::factory()->create();
        $account = SavingsAccount::factory()->create(['user_id' => $user->id, 'organization_id' => $this->organization->id]);
        $withdrawal = SavingsWithdrawal::factory()->create([
            'organization_id' => $this->organization->id,
            'savings_account_id' => $account->id,
            'status' => 'pending',
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($staff)
            ->test(SavingsWithdrawalRegister::class)
            ->call('updateStatus', $withdrawal->id, 'approved')
            ->assertDispatched('notify');

        $this->assertEquals('pending', $withdrawal->refresh()->status);
    }
}
