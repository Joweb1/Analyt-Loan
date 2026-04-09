<?php

namespace Tests\Unit;

use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_can_be_created_from_minor_units()
    {
        $money = new Money(10050, 'NGN');
        $this->assertEquals(10050, $money->getMinorAmount());
        $this->assertEquals(100.50, $money->getMajorAmount());
    }

    public function test_money_can_be_created_from_major_units()
    {
        $money = Money::fromMajor(100.50, 'NGN');
        $this->assertEquals(10050, $money->getMinorAmount());
    }

    public function test_money_addition()
    {
        $m1 = new Money(100, 'NGN');
        $m2 = new Money(200, 'NGN');
        $result = $m1->add($m2);

        $this->assertEquals(300, $result->getMinorAmount());
    }

    public function test_money_subtraction()
    {
        $m1 = new Money(500, 'NGN');
        $m2 = new Money(200, 'NGN');
        $result = $m1->subtract($m2);

        $this->assertEquals(300, $result->getMinorAmount());
    }

    public function test_money_multiplication_with_precision()
    {
        // 100.00 * 0.055 (5.5%) = 5.50 -> 550 minor units
        $m1 = Money::fromMajor(100.00, 'NGN');
        $result = $m1->multiply('0.055');

        $this->assertEquals(550, $result->getMinorAmount());
        $this->assertEquals(5.50, $result->getMajorAmount());
    }

    public function test_large_number_precision()
    {
        // 1,000,000,000.01 * 100 = 100,000,000,001 minor units
        $m1 = Money::fromMajor('1000000000.01', 'NGN');
        $this->assertEquals(100000000001, $m1->getMinorAmount());

        $m2 = $m1->multiply('2');
        $this->assertEquals(200000000002, $m2->getMinorAmount());
    }

    public function test_it_throws_exception_for_mismatched_currencies()
    {
        $this->expectException(\InvalidArgumentException::class);

        $m1 = new Money(100, 'NGN');
        $m2 = new Money(100, 'USD');
        $m1->add($m2);
    }
}
