<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialPrecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup a dummy user/org to satisfy multi-tenancy scopes if needed
        $org = Organization::factory()->create(['currency_code' => 'NGN']);
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->actingAs($user);
    }

    public function test_loan_amount_is_persisted_as_minor_units()
    {
        $amount = 1234.56;
        $loan = Loan::factory()->create([
            'amount' => $amount,
        ]);

        // Check DB directly
        $dbValue = \Illuminate\Support\Facades\DB::table('loans')->where('id', $loan->id)->value('amount');
        $this->assertEquals(123456, $dbValue);

        // Check model casting
        $this->assertInstanceOf(Money::class, $loan->amount);
        $this->assertEquals(123456, $loan->amount->getMinorAmount());
        $this->assertEquals(1234.56, $loan->amount->getMajorAmount());
    }

    public function test_loan_interest_calculation_is_precise()
    {
        $loan = Loan::factory()->create([
            'amount' => 1000.00,
            'interest_rate' => 10, // 10%
            'duration' => 1,
            'duration_unit' => 'year',
            'interest_type' => 'year',
        ]);

        $interest = $loan->getTotalExpectedInterest();

        $this->assertEquals(100.00, $interest->getMajorAmount());
        $this->assertEquals(10000, $interest->getMinorAmount());
    }

    public function test_loan_balance_calculation_uses_minor_units()
    {
        $loan = Loan::factory()->create([
            'amount' => 1000.00,
            'interest_rate' => 10,
            'duration' => 1,
            'duration_unit' => 'year',
            'interest_type' => 'year',
        ]);

        // Create a partial repayment
        $loan->repayments()->create([
            'amount' => 500.25,
            'paid_at' => now(),
        ]);

        // Manually trigger total expected interest in calculation context
        // (Simplified balance calc for test without schedules)
        $expectedTotal = 1100.00; // 1000 principal + 100 interest
        $paid = 500.25;
        $expectedBalanceMajor = 599.75;

        $this->assertEquals($expectedBalanceMajor, $loan->balance->getMajorAmount());
        $this->assertEquals(59975, $loan->balance->getMinorAmount());
    }

    public function test_route_serialization_conflict_is_resolved()
    {
        // This test ensures the LogicException from the prompt doesn't occur when caching/listing routes
        $output = \Illuminate\Support\Facades\Artisan::call('route:list');
        $this->assertEquals(0, $output);
    }
}
