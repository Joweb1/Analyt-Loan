<?php

namespace Tests\Unit;

use App\Traits\SterilizesPhone;
use Tests\TestCase;

class PhoneSterilizationTest extends TestCase
{
    use SterilizesPhone;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_removes_leading_zero_and_prepends_234()
    {
        $input = '08012345678';
        $expected = '2348012345678';
        $this->assertEquals($expected, $this->sterilize($input));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_10_digit_numbers_without_leading_zero()
    {
        $input = '8012345678';
        $expected = '2348012345678';
        $this->assertEquals($expected, $this->sterilize($input));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_leaves_already_sterilized_numbers_intact()
    {
        $input = '2348012345678';
        $expected = '2348012345678';
        $this->assertEquals($expected, $this->sterilize($input));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_strips_non_numeric_characters()
    {
        $input = '+234-801-234-5678';
        $expected = '2348012345678';
        $this->assertEquals($expected, $this->sterilize($input));
    }
}
