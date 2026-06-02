<?php

namespace Tests\Feature\Livewire\Cashbook;

use App\Livewire\Cashbook\Dashboard;
use App\Models\CashbookEntry;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $staff;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create([
            'cashbook_unlock_limit' => 2,
            'allow_staff_cashbook_unlock' => true,
        ]);

        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);

        // Setup Admin
        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'admin',
        ]);
        $this->admin->assignRole('Admin');

        // Setup Staff with record_cashbook permission
        Permission::firstOrCreate(['name' => 'record_cashbook']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $staffRole->syncPermissions(['record_cashbook', 'view_dashboard']);

        $this->staff = User::factory()->create([
            'organization_id' => $this->organization->id,
            'type' => 'staff',
        ]);
        $this->staff->assignRole('Staff');
    }

    public function test_staff_with_permission_can_access_cashbook()
    {
        Livewire::actingAs($this->staff)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_staff_can_unlock_verified_record_up_to_limit()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'verified',
            'staff_unlock_count' => 0,
        ]);

        // 1st Unlock
        Livewire::actingAs($this->staff)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->call('unlock')
            ->assertDispatched('notify');

        $this->assertEquals('pending', $entry->refresh()->status);
        $this->assertEquals(1, $entry->staff_unlock_count);

        // Lock it back
        $entry->update(['status' => 'verified']);

        // 2nd Unlock
        Livewire::actingAs($this->staff)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->call('unlock');

        $this->assertEquals(2, $entry->refresh()->staff_unlock_count);

        // Lock it back
        $entry->update(['status' => 'verified']);

        // 3rd Unlock (Should Fail - Limit is 2)
        Livewire::actingAs($this->staff)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->call('unlock')
            ->assertDispatched('notify');

        $this->assertEquals('verified', $entry->refresh()->status);
    }

    public function test_admin_can_always_unlock_regardless_of_limit()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'verified',
            'staff_unlock_count' => 5, // Already past the limit of 2
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->call('unlock');

        $this->assertEquals('pending', $entry->refresh()->status);
        $this->assertEquals(5, $entry->staff_unlock_count); // Count should NOT increment for admin
    }

    public function test_staff_cannot_unlock_if_disabled_in_settings()
    {
        $this->organization->update(['allow_staff_cashbook_unlock' => false]);

        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'verified',
        ]);

        Livewire::actingAs($this->staff)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->call('unlock');

        $this->assertEquals('verified', $entry->refresh()->status);
    }

    public function test_staff_cannot_edit_admin_only_fields()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'pending',
            'charges' => 0,
            'bonuses' => 0,
        ]);

        Livewire::actingAs($this->staff)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->set('manualFields.charges', 500)
            ->set('manualFields.bonuses', 200)
            ->set('manualFields.excess_cash', 100)
            ->call('saveManualFields');

        $entry->refresh();
        $this->assertEquals(0, $entry->charges->getMinorAmount()); // Failed (Staff)
        $this->assertEquals(0, $entry->bonuses->getMinorAmount()); // Failed (Staff)
        $this->assertEquals(10000, $entry->excess_cash->getMinorAmount()); // Success (Allowed)
    }

    public function test_admin_can_edit_all_fields()
    {
        $entry = CashbookEntry::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class, ['currentDate' => $entry->entry_date->toDateString()])
            ->set('manualFields.charges', 500)
            ->call('saveManualFields');

        $this->assertEquals(50000, $entry->refresh()->charges->getMinorAmount());
    }
}
