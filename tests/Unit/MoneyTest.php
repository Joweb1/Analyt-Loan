<?php

namespace Tests\Unit;

use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_preserves_decimals_internally_but_formats_with_standard_rounding()
    {
        // 100.01 -> stores 10001
        $money = new Money(10001, 'NGN');
        $this->assertEquals(100.01, $money->getMajorAmount());

        // Formats as 100 (standard rounding)
        $this->assertEquals('100', $money->format());

        // 999.99 -> 1000
        $money = Money::fromMajor(999.99);
        $this->assertEquals('1,000', $money->format());
    }

    public function test_money_math_preserves_precision()
    {
        $m1 = Money::fromMajor(10.10);
        $m2 = Money::fromMajor(20.45);
        $result = $m1->add($m2); // 30.55

        $this->assertEquals(30.55, $result->getMajorAmount());
        $this->assertEquals('31', $result->format()); // 30.55 rounds up to 31
    }

    public function test_money_multiplication_preserves_precision()
    {
        // 100 * 0.055 = 5.50
        $m1 = Money::fromMajor(100);
        $result = $m1->multiply('0.055');

        $this->assertEquals(550, $result->getMinorAmount());
        $this->assertEquals(5.50, $result->getMajorAmount());
        $this->assertEquals('6', $result->format());
    }

    public function test_format_with_decimals_option()
    {
        $money = Money::fromMajor(1234.56);
        $this->assertEquals('1,235', $money->format());
        $this->assertEquals('1,234.56', $money->formatWithDecimals(2));
    }

    public function test_it_throws_exception_for_mismatched_currencies()
    {
        $this->expectException(\InvalidArgumentException::class);

        $m1 = new Money(100, 'NGN');
        $m2 = new Money(100, 'USD');
        $m1->add($m2);
    }
}
