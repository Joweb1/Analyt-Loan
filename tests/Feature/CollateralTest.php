<?php

namespace Tests\Feature;

use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollateralTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_can_create_collateral(): void
    {
        $collateral = Collateral::factory()->create();
        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
        ]);
    }

    public function test_collateral_relationships_and_fillable_properties(): void
    {
        $collateral = Collateral::factory()->create();

        $this->assertInstanceOf(Loan::class, $collateral->loan);

        $expectedFillable = [
            'name',
            'description',
            'value',
            'image_path',
            'loan_id',
            'status',
        ];
        $this->assertEquals($expectedFillable, $collateral->getFillable());
    }
}
