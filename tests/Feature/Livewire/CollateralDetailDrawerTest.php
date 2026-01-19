<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CollateralDetailDrawer;
use App\Models\Collateral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollateralDetailDrawerTest extends TestCase
{
    use RefreshDatabase;

    public function test_collateral_detail_drawer_component_renders_and_displays_collateral(): void
    {
        $collateral = Collateral::factory()->create();

        Livewire::test(CollateralDetailDrawer::class)
            ->assertStatus(200)
            ->call('openDrawer', $collateral->id)
            ->assertSee($collateral->name)
            ->assertSee(number_format($collateral->value, 2));
    }

    public function test_collateral_detail_drawer_can_open_and_close(): void
    {
        Livewire::test(CollateralDetailDrawer::class)
            ->assertSet('isOpen', false)
            ->call('openDrawer', Collateral::factory()->create()->id) // Pass a collateral ID to openDrawer
            ->assertSet('isOpen', true)
            ->call('closeDrawer')
            ->assertSet('isOpen', false);
    }
}
