<?php

namespace librdf\tests\cases;

// test the Serializer class

use librdf\Serializer;

class SerializerTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->serializer = new Serializer("rdfxml");
    }

    public function testConstructor()
    {
        $this->assertTrue($this->serializer instanceof \librdf\Serializer);
    }

    public function testGetSerializer()
    {
        $this->assertTrue(is_resource($this->serializer->getSerializer()));
    }

    public function testSetNamespace()
    {
        // just make sure it doesn't throw an exception
        $this->serializer->setNamespace("http://www.example.com/#",
            "ex");
        $this->assertTrue(true);
    }
}
