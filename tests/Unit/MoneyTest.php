<?php

namespace Tests\Unit;

use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_preserves_decimals_internally_but_formats_rounded_up()
    {
        // 100.01 (10001 minor) -> stores 10001
        $money = new Money(10001, 'NGN');
        $this->assertEquals(10001, $money->getMinorAmount());
        $this->assertEquals(100.01, $money->getMajorAmount());
        
        // Formats as rounded up whole number
        $this->assertEquals('101', $money->format());
    }

    public function test_money_can_be_created_from_major_units_preserving_precision()
    {
        // 100.50 -> 10050 minor
        $money = Money::fromMajor(100.50, 'NGN');
        $this->assertEquals(10050, $money->getMinorAmount());
        $this->assertEquals(100.50, $money->getMajorAmount());
        
        // Formats as 101
        $this->assertEquals('101', $money->format());
    }

    public function test_money_math_preserves_precision()
    {
        $m1 = Money::fromMajor(10.10);
        $m2 = Money::fromMajor(20.20);
        $result = $m1->add($m2);

        $this->assertEquals(3030, $result->getMinorAmount());
        $this->assertEquals(30.30, $result->getMajorAmount());
        $this->assertEquals('31', $result->format());
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
