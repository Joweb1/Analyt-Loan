<?php

namespace Tests\Feature\Livewire\Cashbook;

use App\Livewire\Cashbook\Dashboard;
use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
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
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Ensure Admin has manage_vault permission
        $role = \Spatie\Permission\Models\Role::findOrCreate('Admin');
        $permission = \Spatie\Permission\Models\Permission::findOrCreate('manage_vault');
        $role->givePermissionTo($permission);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'admin', // Some models/logic might use 'type'
        ]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_loads_entry_for_current_date()
    {
        $date = now()->toDateString();

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSet('currentDate', $date)
            ->assertViewHas('history');

        $this->assertDatabaseHas('cashbook_entries', [
            'organization_id' => $this->organization->id,
            'entry_date' => $date,
        ]);
    }

    #[Test]
    public function it_can_update_manual_fields()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'entry_date' => now()->toDateString(),
            'status' => 'pending',
            'description' => 'Original',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class, ['currentDate' => now()->toDateString()])
            ->set('manualFields.description', 'Updated Description')
            ->set('manualFields.excess_cash', 100)
            ->call('saveManualFields');

        $this->assertEquals('Updated Description', $entry->refresh()->description);
        $this->assertEquals(10000, $entry->refresh()->excess_cash->getMinorAmount());
    }

    #[Test]
    public function it_prevents_editing_verified_entries()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'entry_date' => now()->toDateString(),
            'status' => 'verified',
            'description' => 'Original',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class, ['currentDate' => now()->toDateString()])
            ->set('manualFields.description', 'New Description')
            ->call('saveManualFields');

        $this->assertEquals('Original', $entry->refresh()->description);
    }

    #[Test]
    public function it_allows_admin_to_unlock_entry()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'entry_date' => now()->toDateString(),
            'status' => 'verified',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class, ['currentDate' => now()->toDateString()])
            ->call('unlock')
            ->assertDispatched('notify');

        $this->assertEquals('pending', $entry->refresh()->status);
    }

    #[Test]
    public function it_restricts_unlocking_to_admins()
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $user->assignRole('Collection Officer');

        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'entry_date' => now()->toDateString(),
            'status' => 'verified',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class, ['currentDate' => now()->toDateString()])
            ->call('unlock')
            ->assertDispatched('notify');

        $this->assertEquals('verified', $entry->refresh()->status);
    }
}
