<?php

namespace Tests\Feature\Livewire\Settings;

use App\Livewire\Settings\TeamManagement;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_can_search_users_in_same_organization_only()
    {
        $org1 = Organization::factory()->create(['name' => 'Org One']);
        $admin1 = User::factory()->create(['organization_id' => $org1->id, 'name' => 'Admin One']);
        $admin1->assignRole('Admin');

        $borrower1 = User::factory()->create([
            'organization_id' => $org1->id,
            'name' => 'John Doe',
        ]);
        $borrower1->assignRole('Borrower');

        $org2 = Organization::factory()->create(['name' => 'Org Two']);
        $borrower2 = User::factory()->create([
            'organization_id' => $org2->id,
            'name' => 'John Smith',
        ]);
        $borrower2->assignRole('Borrower');

        // Search for "John" as Admin 1
        Livewire::actingAs($admin1)
            ->test(TeamManagement::class)
            ->set('searchUser', 'John')
            ->assertSet('userResults', function ($results) use ($borrower1, $borrower2) {
                // Should contain John Doe (Org 1)
                $containsOrg1User = $results->contains('id', $borrower1->id);
                // Should NOT contain John Smith (Org 2)
                $containsOrg2User = $results->contains('id', $borrower2->id);

                return $containsOrg1User && ! $containsOrg2User;
            })
            // Verify that the count matches only local results
            ->assertCount('userResults', 1);
    }

    public function test_can_promote_borrower_to_staff()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = User::factory()->create(['organization_id' => $org->id]);
        $borrower->assignRole('Borrower');

        Livewire::actingAs($admin)
            ->test(TeamManagement::class)
            ->set('selectedUserId', $borrower->id)
            ->set('role', 'Loan Analyst')
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertTrue($borrower->fresh()->hasRole('Loan Analyst'));
        $this->assertFalse($borrower->fresh()->hasRole('Borrower'));
    }

    public function test_can_change_staff_role()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $staff = User::factory()->create(['organization_id' => $org->id]);
        $staff->assignRole('Loan Analyst');

        Livewire::actingAs($admin)
            ->test(TeamManagement::class)
            ->call('changeRole', $staff->id, 'Collection Officer')
            ->assertHasNoErrors();

        $this->assertTrue($staff->fresh()->hasRole('Collection Officer'));
        $this->assertFalse($staff->fresh()->hasRole('Loan Analyst'));
    }

    public function test_can_revoke_staff_access()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $staff = User::factory()->create(['organization_id' => $org->id]);
        $staff->assignRole('Loan Analyst');

        Livewire::actingAs($admin)
            ->test(TeamManagement::class)
            ->call('removeStaffAccess', $staff->id)
            ->assertHasNoErrors();

        $this->assertTrue($staff->fresh()->hasRole('Borrower'));
        $this->assertFalse($staff->fresh()->hasRole('Loan Analyst'));
    }

    public function test_cannot_revoke_own_access()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        Livewire::actingAs($admin)
            ->test(TeamManagement::class)
            ->call('removeStaffAccess', $admin->id)
            ->assertDispatched('custom-alert');

        $this->assertTrue($admin->fresh()->hasRole('Admin'));
    }
}
