<?php

namespace Tests\Feature;

use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollateralApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_collateral_via_api(): void
    {
        $loan = Loan::factory()->create();
        $collateralData = [
            'name' => 'Test Collateral',
            'description' => 'Test Description',
            'value' => 1000,
            'loan_id' => $loan->id,
            'status' => 'in_vault',
        ];

        $response = $this->postJson('/api/collaterals', $collateralData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('collaterals', $collateralData);
    }

    public function test_can_get_collateral_via_api(): void
    {
        $collateral = Collateral::factory()->create();

        $response = $this->getJson('/api/collaterals/'.$collateral->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $collateral->id]);
    }

    public function test_can_update_collateral_via_api(): void
    {
        $collateral = Collateral::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/collaterals/'.$collateral->id, $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('collaterals', ['id' => $collateral->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_collateral_via_api(): void
    {
        $collateral = Collateral::factory()->create();

        $response = $this->deleteJson('/api/collaterals/'.$collateral->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('collaterals', ['id' => $collateral->id]);
    }
}
