<?php
namespace librdf\tests\cases;

// test Node and subclasses

use librdf\URI;
use librdf\Node;
use librdf\node\URINode;
use librdf\node\LiteralNode;
use librdf\node\BlankNode;

class NodeTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->testURI = "http://www.example.com/";
        $this->testURI2 = "http://www.example.org/";
        $this->testNodeID = "abcd";
        $this->testNodeID2 = "efgh";
        $this->xmlDatatype = "http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral";
        $this->testType = "http://www.example.com/types/#testtype";
        $this->testType2 = "http://www.example.com/types/#testtype2";
        $this->testLang = "en-us";
        $this->testLang2 = "fr";
        $this->testLiteral = 'This is the first test literal';
        $this->testLiteral2 = "This is the second test literal";

        // create an xml test
        $xmlstr = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE document [
    <!ELEMENT document (nodelist+)>
    <!ELEMENT nodelist (child*)>
    <!ATTLIST nodelist id ID #REQUIRED>
    <!ELEMENT child (#PCDATA)>
]>
<document>
    <nodelist id="list1"><child/></nodelist>
</document>
EOT;

        $document = new \DOMDocument();
        $document->loadXML($xmlstr);
        $document->validate();
        $this->xmllist = $document->getElementById("list1")->childNodes;
    }

    public function testURIConstruct()
    {
        $uri = new URINode($this->testURI);
        $this->assertTrue($uri instanceof \librdf\node\URINode);
    }

    public function testURIAltConstruct()
    {
        $uri = new URINode($this->testURI);
        $uri2 = new URINode($uri->getNode());
        $this->assertTrue($uri2 instanceof \librdf\node\URINode);
    }

    public function testURIConstructFail()
    {
    	$this->expectException('Argument is not a string or node resource');
        $uri = new URINode(47);

        $blank_node = new BlankNode();
        $uri = new URINode($blank_node->getNode());
    }

    public function testURIGetNode()
    {
        $uri = new URINode($this->testURI);
        $this->assertTrue(is_resource($uri->getNode()));
    }

    public function testURIToString()
    {
        $uri = new URINode($this->testURI);
        $this->assertEqual($uri->__toString(),
            "<" . $this->testURI . ">");
    }

    public function testURIClone()
    {
        $uri1 = new URINode($this->testURI);
        $uri2 = clone $uri1;
        $this->assertTrue($uri2 instanceof \librdf\node\URINode);
        $this->assertNotEqual($uri1, $uri2);
        $this->assertTrue($uri1->isEqual($uri2));
        $this->assertTrue($uri2->isEqual($uri1));
    }

    public function testURIIsEqual()
    {
        $uri1 = new URINode($this->testURI);
        $uri2 = new URINode($this->testURI);
        $uri3 = new URINode($this->testURI2);

        $this->assertTrue($uri1->isEqual($uri2));
        $this->assertFalse($uri1->isEqual($uri3));
    }

    public function testBlankConstruct()
    {
        $blank1 = new BlankNode();
        $blank2 = new BlankNode($this->testNodeID);

        $this->assertTrue($blank1 instanceof \librdf\node\BlankNode);
        $this->assertTrue($blank2 instanceof \librdf\node\BlankNode);
    }

    public function testBlankAltConstruct()
    {
        $blank1 = new BlankNode();
        $blank2 = new BlankNode($blank1->getNode());
        $this->assertTrue($blank2 instanceof \librdf\node\BlankNode);
    }

    public function testBlankConstructFail()
    {
		$this->expectException('Resource argument not a valid node blank node');
        // only a bad node is failure: everything else is converted
        // to a string
        $literal_node = new LiteralNode("value");
        $blank = new BlankNode($literal_node->getNode());
    }

    public function testBlankToString()
    {
        $blank1 = new BlankNode();
        $blank2 = new BlankNode($this->testNodeID);

        $this->assertTrue(is_string($blank1->__toString()));
        $this->assertEqual($blank2->__toString(),
            "_:" . $this->testNodeID);
    }

    public function testBlankClone()
    {
        $blank1 = new BlankNode($this->testNodeID);
        $blank2 = clone $blank1;
        $this->assertTrue($blank2 instanceof \librdf\node\BlankNode);
        $this->assertNotEqual($blank1, $blank2);
        $this->assertEqual($blank1->__toString(), $blank2->__toString());
    }

    // just testing whether a node is equal to itself
    // librdf actually returns true for any two blank nodes with the same
    // nodeID, but this is somewhat ambiguous, since they should only
    // be equal if they are from the same document, and therefore the same
    // node
    public function testBlankIsEqual()
    {
        $blank1 = new BlankNode($this->testNodeID);
        $blank2 = new BlankNode($this->testNodeID2);

        $this->assertTrue($blank1->isEqual($blank1));
        $this->assertFalse($blank1->isEqual($blank2));
    }

    public function testLiteralConstructPlain()
    {
        $literal = new LiteralNode($this->testLiteral);
        $literalLang = new LiteralNode($this->testLiteral, NULL,
            $this->testLang);
        $this->assertTrue($literal instanceof \librdf\node\LiteralNode);
        $this->assertTrue($literalLang instanceof \librdf\node\LiteralNode);
    }

    public function testLiteralConstructTyped()
    {
        $literal = new LiteralNode($this->testLiteral, $this->testType);
        $this->assertTrue($literal instanceof \librdf\node\LiteralNode);

        $this->expectException('Unable to create new literal node');
        $literal = new LiteralNode($this->testLiteral,
            $this->testType, $this->testLang);

        $literal = new LiteralNode($this->xmllist);
        $this->assertTrue($literal instanceof \librdf\node\LiteralNode);

        $this->expectException('Object of class DOMNodeList could not be converted to string');
        $literal = new LiteralNode($this->xmllist, $this->testType);


    }

    public function testLiteralAltConstruct()
    {
        $literal = new LiteralNode($this->testLiteral, $this->testType);
        $literal1 = new LiteralNode($literal->getNode());
        $this->assertTrue($literal1 instanceof \librdf\node\LiteralNode);
    }

    public function testLiteralConstructFail()
    {
    	$this->expectException('Invalid number of arguments');
        $literal = new LiteralNode();
        $this->fail("Constructor failed to throw exception for invalid arguments");

        // one argument, wrong resource type
        $this->expectException('Argument 1 not a valid node  literal node');
        $blank_node = new BlankNode();
        $literal = new LiteralNode($blank_node->getNode());

        // more than three arguments
        $this->expectException('Invalid number of arguments');
        $literal = new LiteralNode("value",
            "http://www.example.org/",
            NULL, NULL);
    }

    /**
     * Enter description here ...
     *
     * @todo find out why __toString return a doubly quoted value
     */
    public function testLiteralToString()
    {
        $literal = new LiteralNode($this->testLiteral);
        $this->assertEqual($literal->__toString(), '"'.$this->testLiteral.'"');
    }

    public function testLiteralClone()
    {
        $literal1 = new LiteralNode($this->testLiteral);
        $literal2 = clone $literal1;

        $this->assertTrue($literal2 instanceof \librdf\node\LiteralNode);
        $this->assertNotEqual($literal1, $literal2);
        $this->assertTrue($literal1->isEqual($literal2));
    }

    public function testLiteralIsEqual()
    {
        $literal1 = new LiteralNode($this->testLiteral);
        $literal1_1 = new LiteralNode($this->testLiteral);
        $literal2 = new LiteralNode($this->testLiteral, $this->testType);
        $literal2_2 = new LiteralNode($this->testLiteral, $this->testType);
        $literal3 = new LiteralNode($this->testLiteral, $this->testType2);
        $literal4 = new LiteralNode($this->testLiteral2);
        $literal5 = new LiteralNode($this->testLiteral, NULL, $this->testLang);
        $literal5_2 = new LiteralNode($this->testLiteral, NULL, $this->testLang);
        $literal6 = new LiteralNode($this->testLiteral, NULL, $this->testLang2);

        $this->assertTrue($literal1->isEqual($literal1_1));
        $this->assertFalse($literal1->isEqual($literal2));
        $this->assertFalse($literal1->isEqual($literal4));
        $this->assertFalse($literal1->isEqual($literal5));
        $this->assertTrue($literal2->isEqual($literal2_2));
        $this->assertFalse($literal2->isEqual($literal3));
        $this->assertFalse($literal5->isEqual($literal6));
        $this->assertTrue($literal5->isEqual($literal5_2));
    }

    public function testLiteralGetDataType()
    {
        $literal = new LiteralNode($this->testLiteral, $this->testType);
        $literal1 = new LiteralNode($this->testLiteral);
        $this->assertEqual($literal->getDataType(), $this->testType);
        $this->assertNull($literal1->getDataType());
    }

    public function testLiteralGetLanguage()
    {
        $literal = new LiteralNode($this->testLiteral, NULL, $this->testLang);
        $literal1 = new LiteralNode($this->testLiteral);
        $this->assertEqual($literal->getLanguage(), $this->testLang);
        $this->assertNull($literal1->getLanguage());
    }
}
