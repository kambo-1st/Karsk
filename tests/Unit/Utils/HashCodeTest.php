<?php
namespace Kambo\Tests\Karsk\Unit\Utils;

use PHPUnit\Framework\TestCase;

use Kambo\Karsk\Utils\HashCode;

/**
 * Test for the Kambo\Karsk\Utils\HashCode
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class HashCodeTest extends TestCase
{
    /**
     * Tests generating hash value for string "lorem ipsum"
     *
     * @return void
     */
    public function testGetHashValueSimpleString() : void
    {
        $hashCode = new HashCode();

        $this->assertEquals(91554759160509083, $hashCode->get('lorem ipsum'));
    }

    /**
     * Tests generating hash value for string with utf character.
     *
     * @return void
     */
    public function testGetHashValueUtf() : void
    {
        $hashCode = new HashCode();

        $this->assertEquals(-7704993204978618733, $hashCode->get('🐘 lorem ipsum'));
    }

    /**
     * Tests generating hash value for empty string.
     *
     * @return void
     */
    public function testGetHashValueEmpty() : void
    {
        $hashCode = new HashCode();

        $this->assertEquals(0, $hashCode->get(''));
    }
}
