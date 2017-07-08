<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Uri;

/**
 * Unit test for the UriTest object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test create URI object with all parameters
     *
     * @return void
     */
    public function testCreateUri()
    {
        $uri = new Uri(
            'http',
            'www.example.com',
            1111,
            '/path/123',
            'q=abc',
            'fragment',
            'user',
            'password'
        );

        $this->assertEquals('fragment', $uri->getFragment());
        $this->assertEquals('www.example.com', $uri->getHost());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals(1111, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
    }

}
