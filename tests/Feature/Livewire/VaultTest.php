<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Vault;
use App\Models\Collateral;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VaultTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Vault::class)
            ->assertStatus(200);
    }

    public function test_it_filters_assets_by_status()
    {
        Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'In Vault Asset',
            'status' => 'in_vault',
        ]);

        Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Returned Asset',
            'status' => 'returned',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Vault::class)
            ->set('filter', 'in_vault')
            ->assertSee('In Vault Asset')
            ->assertDontSee('Returned Asset');
    }

    public function test_it_deletes_an_asset()
    {
        $asset = Collateral::factory()->create(['organization_id' => $this->organization->id]);

        Livewire::actingAs($this->admin)
            ->test(Vault::class)
            ->call('deleteAsset', $asset->id)
            ->assertDispatched('custom-alert');

        $this->assertDatabaseMissing('collaterals', ['id' => $asset->id]);
    }
}
