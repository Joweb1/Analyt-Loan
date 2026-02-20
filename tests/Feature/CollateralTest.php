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
        $loan = Loan::factory()->create();
        $collateral = Collateral::factory()->create(['loan_id' => $loan->id]);

        $this->assertInstanceOf(Loan::class, $collateral->loan);

        $expectedFillable = [
            'organization_id',
            'name',
            'type',
            'condition',
            'description',
            'value',
            'image_path',
            'documents',
            'registered_date',
            'loan_id',
            'status',
        ];
        $this->assertEquals($expectedFillable, $collateral->getFillable());
    }
}
