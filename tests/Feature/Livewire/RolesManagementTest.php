<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Settings\RolesManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_successfully()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        Livewire::actingAs($user)
            ->test(RolesManagement::class)
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_edit_system_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $role = Role::findByName('Loan Analyst');

        Livewire::actingAs($user)
            ->test(RolesManagement::class)
            ->call('editRole', $role->id)
            ->assertSet('roleName', 'Loan Analyst')
            ->assertSet('editingRoleId', $role->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_delete_system_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $role = Role::findByName('Loan Analyst');

        Livewire::actingAs($user)
            ->test(RolesManagement::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'error' && $params[0]['message'] === 'Cannot delete system roles.';
            });

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_custom_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $role = Role::create(['name' => 'Custom Role']);

        Livewire::actingAs($user)
            ->test(RolesManagement::class)
            ->call('deleteRole', $role->id)
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'warning' && $params[0]['message'] === 'Role deleted.';
            });

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
