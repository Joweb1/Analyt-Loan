<?php

namespace Tests\Unit;

use App\Rules\FiftyPercentRule;
use Tests\TestCase;

class FiftyPercentRuleTest extends TestCase
{
    public function test_50_percent_rule_passes_when_collateral_is_sufficient(): void
    {
        $rule = new FiftyPercentRule(1000);
        $this->assertTrue($rule->passes('collateral_value', 500));
    }

    public function test_50_percent_rule_fails_when_collateral_is_insufficient(): void
    {
        $rule = new FiftyPercentRule(1000);
        $this->assertFalse($rule->passes('collateral_value', 499));
    }
}
