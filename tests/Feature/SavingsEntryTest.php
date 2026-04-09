<?php

namespace Tests\Feature;

use App\Livewire\SavingsEntry;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_render_savings_entry_page_and_add_deposit()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        Livewire::actingAs($user)
            ->test(SavingsEntry::class)
            ->assertSee($borrowerUser->name)
            ->call('selectBorrower', $borrower->id)
            ->assertSet('showSavingsModal', true)
            ->set('amount', 5000)
            ->call('addSavings')
            ->assertSet('showSavingsModal', false);

        $this->assertDatabaseHas('savings_accounts', [
            'borrower_id' => $borrower->id,
            'balance' => 500000,
        ]);

        $this->assertDatabaseHas('savings_transactions', [
            'amount' => 500000,
            'type' => 'deposit',
        ]);
    }
}
