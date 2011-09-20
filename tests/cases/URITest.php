<?php
namespace librdf\tests\cases;

use librdf\exception\Error;
use librdf\URI;

class URITest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->test_string = "http://www.example.com/";
        $this->test_string2 = "http://www.example.org/";
    }

    public function testConstructor()
    {
        $uri = new URI($this->test_string);
        $this->assertTrue($uri instanceof \librdf\URI);
    }

    public function testToString()
    {
        $uri = new URI($this->test_string);
        $this->assertEqual($this->test_string, $uri->__toString());
    }

    public function testClone()
    {
        $uri1 = new URI($this->test_string);
        $uri2 = clone $uri1;
        $this->assertTrue($uri2 instanceof \librdf\URI);
        $this->assertNotEqual($uri1, $uri2);
        $this->assertNotEqual($uri1->getURI(), $uri2->getURI());
        $this->assertEqual($this->test_string, $uri2->__toString());
    }

    public function testGetURI()
    {
        $uri = new URI($this->test_string);
        $this->assertTrue($uri instanceof \librdf\URI);
    }

    public function testIsEqual()
    {
        $uri1 = new URI($this->test_string);
        $uri2 = new URI($this->test_string);
        $uri3 = new URI($this->test_string2);

        $this->assertTrue($uri1->isEqual($uri2));
        $this->assertFalse($uri1->isEqual($uri3));
    }
}