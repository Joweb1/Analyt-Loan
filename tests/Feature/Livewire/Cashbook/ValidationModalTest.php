<?php

namespace Tests\Feature\Livewire\Cashbook;

use App\Livewire\Cashbook\ValidationModal;
use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantSession;
use App\ValueObjects\Money;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidationModalTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create([
            'currency_code' => 'NGN',
        ]);
        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->user->assignRole('Collection Officer');

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'admin',
        ]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_enforces_bank_deposit_validation()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_repayments' => new Money(500000, 'NGN'), // 5000.00
            'status' => 'pending',
        ]);

        // Expected is 5000.00
        Livewire::actingAs($this->user)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 4000)
            ->call('validateAndClose')
            ->assertHasErrors(['bank_deposit']);

        Livewire::actingAs($this->user)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 5000)
            ->call('validateAndClose')
            ->assertHasNoErrors();
    }

    #[Test]
    public function it_recalculates_expected_bank_deposit_with_card_and_excess()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_repayments' => new Money(500000, 'NGN'), // 5000.00
            'status' => 'pending',
        ]);

        // 5000 + 200 (card) + 300 (excess) = 5500
        Livewire::actingAs($this->user)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 5000)
            ->set('card_payments', 200)
            ->set('excess_cash', 300)
            ->call('validateAndClose')
            ->assertHasErrors(['bank_deposit']);

        Livewire::actingAs($this->user)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 5500)
            ->set('card_payments', 200)
            ->set('excess_cash', 300)
            ->call('validateAndClose')
            ->assertHasNoErrors();
    }

    #[Test]
    public function admin_can_override_validation()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_repayments' => new Money(500000, 'NGN'), // 5000.00
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 4000)
            ->call('override')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertEquals(400000, $entry->refresh()->bank_deposit_amount->getMinorAmount());
    }

    #[Test]
    public function collection_officer_cannot_override_validation()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_repayments' => new Money(500000, 'NGN'), // 5000.00
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->user)
            ->test(ValidationModal::class, ['entry' => $entry])
            ->set('bank_deposit', 4000)
            ->call('override')
            ->assertSet('showModal', true); // Modal should stay open

        $this->assertEquals(0, $entry->refresh()->bank_deposit_amount->getMinorAmount());
    }
}
