<?php
namespace librdf\tests\cases;

// test the Parser class

use librdf\Parser;
use librdf\Statement;

class ParserTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->parser = new Parser();
        $this->testXMLFile = dirname(dirname(__FILE__))."/mocks/test.rdf";
        $this->testXML = file_get_contents($this->testXMLFile);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->parser instanceof \librdf\Parser);
    }

    public function testGetParser()
    {
        $this->assertTrue(is_resource($this->parser->getParser()));
    }

    public function testParseString()
    {
        // the file contains 13 statements, make sure all are parsed
        $count = 0;
        foreach ($this->parser->parseString($this->testXML) as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 13);

        $count = 0;
        foreach ($this->parser->parseString($this->testXML,
                "http://www.example.org/#") as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 13);
    }

    // I can't get the file URIs to work, but I don't know if that's because
    // I'm choosing the wrong parser or what.  It works fine with http, and
    // I don't want for an HTTP request every time I run the tests
    public function testParseURI()
    {
        $count = 0;
        $testURI = "http://www.w3.org/1999/02/22-rdf-syntax-ns";
        foreach ($this->parser->parseURI($testURI) as $statement) {
            $count++;
            $this->assertTrue($statement instanceof \librdf\Statement);
        }
        $this->assertNotEqual($count, 0);
    }
}
