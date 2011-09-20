<?php
namespace librdf\tests\cases;

// test the Statement class

use librdf\Statement;
use librdf\Node;
use librdf\node\URINode;
use librdf\node\LiteralNode;

class StatementTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->sourceURI = "http://www.example.com/#source";
        $this->sourceURI1 = "http://www.example.com/#source1";
        $this->source = new URINode($this->sourceURI);
        $this->source1 = new URINode($this->sourceURI1);
        $this->predicateURI = "http://www.example.com/#predicate";
        $this->predicate = new URINode($this->predicateURI);
        $this->targetValue = "Value";
        $this->target = new LiteralNode($this->targetValue);
        $this->statement = new Statement($this->source, $this->predicate,
            $this->target);
    }

    public function testConstructor()
    {
        $statement = new Statement($this->source, $this->predicate,
            $this->target);
        $this->assertTrue($statement instanceof \librdf\Statement);
    }

    public function testAltConstructor()
    {
        $librdf_statement = librdf_new_statement_from_nodes(librdf_php_get_world(),
            librdf_new_node_from_node($this->source->getNode()),
            librdf_new_node_from_node($this->predicate->getNode()),
            librdf_new_node_from_node($this->target->getNode()));
        $statement = new Statement($librdf_statement);
        $this->assertTrue($statement instanceof \librdf\Statement);
    }

    public function testNoArgConstructorFail()
    {
        // no arguments
        $this->expectException('Unable to create new statement');
        $statement = new Statement();
    }

    public function testOneArgNoResourceConstructorFail()
    {
        // one argument, not a resource
        $this->expectException('Single parameter must be a statement');
        $statement = new Statement("String");
    }

    public function testTwoArgConstructorFail()
    {
        // two arguments
        $this->expectException('Unable to create new statement');
        $statement = new Statement($this->source, $this->predicate);
    }

    public function testThreeArgNotNodeConstructorFail()
    {
        // three arguments, source not a node
        $this->expectException('Arguments must be of type Node');
        $statement = new Statement($this->source->__toString(),
            $this->predicate,
            $this->target);
    }

    public function testTooManyArgConstructorFail()
    {
        // too many arguments
        $this->expectException('Unable to create new statement');
        $statement = new Statement($this->source,
            $this->predicate,
            $this->target,
            $this->source);
    }

    public function testToString()
    {
        $this->assertEqual($this->statement->__toString(),
            $this->source->__toString() . " " .
            $this->predicate->__toString() . " " .
            $this->target->__toString());
    }

    public function testClone()
    {
        $statement2 = clone $this->statement;
        $this->assertTrue($statement2 instanceof \librdf\Statement);
        $this->assertNotEqual($statement2, $this->statement);
        $this->assertTrue($this->statement->isEqual($statement2));
        $this->assertTrue($statement2->isEqual($this->statement));
    }

    public function testGetStatement()
    {
        $this->assertTrue(is_resource($this->statement->getStatement()));
    }

    public function testGetSubject()
    {
        $subject = $this->statement->getSubject();
        $this->assertTrue($subject instanceof \librdf\Node);
        $this->assertTrue($subject->isEqual($this->source));
        $this->assertEqual($subject->__toString(),
            "<" . $this->sourceURI . ">");
    }

    public function testGetPredicate()
    {
        $predicate = $this->statement->getPredicate();
        $this->assertTrue($predicate instanceof \librdf\Node);
        $this->assertTrue($predicate->isEqual($this->predicate));
        $this->assertEqual($predicate->__toString(),
            "<" . $this->predicateURI . ">");
    }

    /**
     * Enter description here
     *
     * @todo check doubly quoted value returned by __toString
     */
    public function testGetObject()
    {
        $object = $this->statement->getObject();
        $this->assertTrue($object instanceof \librdf\Node);
        $this->assertTrue($object->isEqual($this->target));
        $this->assertEqual($object->__toString(), '"'.$this->targetValue.'"');
    }

    public function testIsEqual()
    {
        $statement1 = $this->statement;
        $statement2 = new Statement($this->source, $this->predicate,
            $this->target);
        $statement3 = new Statement($this->source1, $this->predicate,
            $this->target);

        $this->assertTrue($statement1->isEqual($statement2));
        $this->assertFalse($statement1->isEqual($statement3));
    }

}