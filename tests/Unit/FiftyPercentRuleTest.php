<?php

namespace Tests\Unit;

use App\Models\Collateral;
use App\Rules\FiftyPercentRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiftyPercentRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_50_percent_rule_passes_when_collateral_is_sufficient(): void
    {
        $collateral = Collateral::factory()->create(['value' => 500]);
        $rule = new FiftyPercentRule(1000);
        $this->assertTrue($rule->passes('collateral_value', $collateral->id));
    }

    public function test_50_percent_rule_fails_when_collateral_is_insufficient(): void
    {
        $collateral = Collateral::factory()->create(['value' => 499]);
        $rule = new FiftyPercentRule(1000);
        $this->assertFalse($rule->passes('collateral_value', $collateral->id));
    }
}
