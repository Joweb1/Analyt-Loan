<?php

namespace Tests\Feature\Livewire\DailySavings;

use App\Livewire\DailySavings\Record;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Services\TenantSession;
use App\ValueObjects\Money;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create([
            'system_date' => now()->toDateString(),
        ]);
        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Record::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_calculates_week_days_correctly()
    {
        Livewire::actingAs($this->admin)
            ->test(Record::class)
            ->assertCount('weekDays', $this->organization->thrift_cycle_days ?: 6);
    }

    #[Test]
    public function it_can_record_savings_for_today()
    {
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'is_daily_saver' => true,
        ]);
        $user = User::factory()->create(['name' => 'John Doe']);
        $borrower->user()->associate($user);
        $borrower->save();

        $date = now()->toDateString();

        Livewire::actingAs($this->admin)
            ->test(Record::class)
            ->call('toggleUnlock', $user->id, $date)
            ->set('gridData.'.$user->id.'.'.$date, 500)
            ->call('recordSavings', $user->id, $date)
            ->assertDispatched('custom-alert');

        $this->assertDatabaseHas('savings_transactions', [
            'type' => 'daily_thrift',
            'amount' => 50000,
        ]);

        $account = SavingsAccount::where('user_id', $user->id)->first();
        $this->assertEquals(50000, $account->daily_savings_balance->getMinorAmount());
    }

    #[Test]
    public function it_restricts_past_date_recording_to_admins()
    {
        $staff = User::factory()->create(['organization_id' => $this->organization->id]);
        $staff->assignRole('Collection Officer');

        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id, 'is_daily_saver' => true]);
        $user = User::factory()->create();
        $borrower->user()->associate($user);
        $borrower->save();

        $pastDate = now()->subDays(2)->toDateString();

        Livewire::actingAs($staff)
            ->test(Record::class)
            ->call('toggleUnlock', $user->id, $pastDate)
            ->assertDispatched('custom-alert');

        Livewire::actingAs($this->admin)
            ->test(Record::class)
            ->call('toggleUnlock', $user->id, $pastDate);
    }

    #[Test]
    public function it_updates_existing_savings_transaction()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id, 'is_daily_saver' => true]);
        $user = User::factory()->create();
        $borrower->user()->associate($user);
        $borrower->save();

        $account = SavingsAccount::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'daily_savings_balance' => new Money(100000),
        ]);

        $date = now()->toDateString();
        SavingsTransaction::factory()->create([
            'savings_account_id' => $account->id,
            'amount' => new Money(100000),
            'type' => 'daily_thrift',
            'transaction_date' => $date,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Record::class)
            ->call('toggleUnlock', $user->id, $date)
            ->set('gridData.'.$user->id.'.'.$date, 150)
            ->call('recordSavings', $user->id, $date);

        $this->assertEquals(15000, $account->refresh()->daily_savings_balance->getMinorAmount());
    }
}
