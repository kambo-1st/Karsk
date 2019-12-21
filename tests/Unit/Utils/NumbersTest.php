<?php

namespace Kambo\Tests\Karsk\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Kambo\Karsk\Utils\Numbers;

/**
 * Test for the Kambo\Karsk\Utils\Numbers
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class NumbersTest extends TestCase
{
    /**
     * Tests a representation of the specified floating-point value according to the IEEE 754 floating-point
     *
     * @return void
     */
    public function testFloatToRawIntBits() : void
    {
        $numbers = new Numbers();

        $this->assertEquals(
            1092616192,
            $numbers->floatToRawIntBits(10)
        );

        $this->assertEquals(
            1065353216,
            $numbers->floatToRawIntBits(1)
        );

        $this->assertEquals(
            1120403456,
            $numbers->floatToRawIntBits(100)
        );

        $this->assertEquals(
            1118453760,
            $numbers->floatToRawIntBits(85.125)
        );
    }

    /**
     * Tests a representation of the specified floating-point value according to the IEEE 754 floating-point for
     * negative numbers.
     *
     * @return void
     */
    public function testFloatToRawIntBitsNegative() : void
    {
        $this->markTestSkipped('Negative numbers are not implemented.');
        $numbers = new Numbers();

        $this->assertEquals(
            -1082130432,
            $numbers->floatToRawIntBits(-1)
        );
    }
}
