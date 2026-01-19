<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DigitalShelf;
use App\Models\Collateral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalShelfTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_shelf_component_renders_and_displays_collateral(): void
    {
        $collaterals = Collateral::factory()->count(3)->create();

        Livewire::test(DigitalShelf::class)
            ->assertStatus(200)
            ->assertSee($collaterals->first()->name)
            ->assertSee(number_format($collaterals->first()->value, 2))
            ->assertSee($collaterals->first()->image_path);
    }
}
