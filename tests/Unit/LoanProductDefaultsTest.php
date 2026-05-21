<?php

namespace Tests\Unit;

use App\Models\LoanProduct;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanProductDefaultsTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_loan_product_with_defaults()
    {
        $org = Organization::factory()->create();

        $product = LoanProduct::create([
            'organization_id' => $org->id,
            'name' => 'Test Product',
            'default_interest_rate' => 15.5,
            'interest_calculation_type' => 'percentage',
            'default_duration' => 12,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'processing_fee' => 1000,
            'processing_fee_type' => 'fixed',
            'insurance_fee' => 500,
            'insurance_fee_type' => 'fixed',
        ]);

        $this->assertDatabaseHas('loan_products', [
            'name' => 'Test Product',
            'default_interest_rate' => 15.5,
            'interest_calculation_type' => 'percentage',
            'processing_fee_type' => 'fixed',
        ]);
    }
}
