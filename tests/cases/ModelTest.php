<?php

namespace librdf\tests\cases;

// test the Model class

use librdf\Model;
use librdf\Parser;
use librdf\Storage;
use librdf\Statement;
use librdf\Serializer;
use librdf\node\URINode;
use librdf\node\LiteralNode;

class ModelTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->storage = new Storage();

        $this->sourceURI1 = "http://www.example.com/sources/#s1";
        $this->sourceURI2 = "http://www.example.com/sources/#s2";
        $this->predURI1 = "http://www.example.com/predicates/#p1";
        $this->predURI2 = "http://www.example.com/predicates/#p2";
        $this->targetValue1 = "Literal value 1";
        $this->targetValue2 = "Literal value 2";

        $this->sourceNode1 = new URINode($this->sourceURI1);
        $this->sourceNode2 = new URINode($this->sourceURI2);
        $this->predNode1 = new URINode($this->predURI1);
        $this->predNode2 = new URINode($this->predURI2);
        $this->targetNode1 = new LiteralNode($this->targetValue1);
        $this->targetNode2 = new LiteralNode($this->targetValue2);

        $this->statement1 = new Statement($this->sourceNode1,
            $this->predNode1, $this->targetNode1);
        $this->statement2 = new Statement($this->sourceNode1,
            $this->predNode2, $this->targetNode1);
        $this->statement3 = new Statement($this->sourceNode1,
            $this->predNode2, $this->targetNode2);
        $this->statement4 = new Statement($this->sourceNode2,
            $this->predNode2, $this->targetNode2);

        $this->model = new Model($this->storage);
        $this->model->addStatement($this->statement1);
        $this->model->addStatement($this->statement3);
        $this->model->addStatement($this->statement4);

        // test.rdf is a copy of rmannoy's install.rdf file
        // store the filename and load it as a string
        $this->testXMLFile = dirname(dirname(__FILE__))."/mocks/test.rdf";
        $this->testXML = file_get_contents($this->testXMLFile);

        $this->parser = new Parser("rdfxml");
        $this->serializer = new Serializer("rdfxml");
    }

    public function testConstructor()
    {
        $model = new Model(new Storage());
        $this->assertTrue($model instanceof \librdf\Model);
    }

    public function testToString()
    {
        // this isn't really meant to be used except as a convenience
        // function, so just check that it spits out a string.  The
        // serializer functions are what really matter
        $this->assertTrue(is_string($this->model->__toString()));
    }

    public function testGetModel()
    {
        $this->assertTrue(is_resource($this->model->getModel()));
    }

    // clone isn't supported for memory storage, and I don't feel like
    // using a different backend

    public function testSize()
    {
        $this->assertEqual($this->model->size(), 3);
    }

    public function testAddRemove()
    {
        // ensure statement2 isn't in the model
        $count = 0;
        foreach ($this->model->findStatements($this->statement2) as $statement) {
            $count++;
        }
        $this->assertEqual($count, 0);

        // add the statement and make sure it shows up in find_statement
        $count = 0;
        $this->model->addStatement($this->statement2);
        foreach ($this->model->findStatements($this->statement2) as $statement) {
            $count++;
        }
        $this->assertEqual($count, 1);

        // remove it and make sure it's gone
        $count = 0;
        $this->model->removeStatement($this->statement2);
        foreach ($this->model->findStatements($this->statement2) as $statement) {
            $count++;
        }
        $this->assertEqual($count, 0);
    }

    public function testGetSource()
    {
        $source = $this->model->getSource($this->predNode1, $this->targetNode1);
        $this->assertTrue($this->sourceNode1->isEqual($source));

        $this->expectException('No such statement');
        $this->model->getSource($this->predNode1, $this->targetNode2);
    }

    public function testGetArc()
    {
        $arc = $this->model->getArc($this->sourceNode1, $this->targetNode1);
        $this->assertTrue($this->predNode1->isEqual($arc));

        $this->expectException('No such statement');
        $this->model->getArc($this->sourceNode2, $this->targetNode1);
    }

    public function testGetTarget()
    {
        $target = $this->model->getTarget($this->sourceNode1, $this->predNode1);
        $this->assertTrue($this->targetNode1->isEqual($target));

        $this->expectException('No such statement');
        $this->model->getTarget($this->sourceNode2, $this->predNode1);
    }

    public function testHasStatement() {
        $this->assertTrue($this->model->hasStatement(
            new Statement($this->sourceNode1, $this->predNode1, $this->targetNode1)));
        $this->assertFalse($this->model->hasStatement(
            new Statement($this->sourceNode2, $this->predNode1, $this->targetNode2)));
    }


    public function testFindStatements()
    {
        // two statements with sourceNode1 as subject
        $count = 0;
        foreach ($this->model->findStatements($this->sourceNode1, NULL, NULL)
            as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 2);

        // two statements with predNode2 as predicate
        $count = 0;
        foreach ($this->model->findStatements(NULL, $this->predNode2, NULL)
            as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 2);

        // one statement with targetNode1 as target
        $count = 0;
        foreach ($this->model->findStatements(NULL, NULL, $this->targetNode1)
            as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 1);
    }

    public function testIterator()
    {
        // just make sure that three statements pop out
        $count = 0;
        foreach ($this->model as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        $this->assertEqual($count, 3);
    }

    public function testLoadStatementsFromString()
    {
        $this->model->loadStatementsFromString($this->parser, $this->testXML);
        $count = 0;
        foreach ($this->model as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $count++;
        }
        // 3 initially plus 13 from the file
        $this->assertEqual($count, 16);
    }
/*
    // disabling to get rid of the network traffic
    public function testLoadStatementsFromURI()
    {
        $testURI = "http://www.w3.org/1999/02/22-rdf-syntax-ns";
        $count = 0;
        $this->model->loadStatementsFromURI($this->parser, $testURI);
        foreach ($this->model as $statement) {
            $count++;
            $this->assertTrue($statement instanceof \librdf\Statement);
        }
        $this->assertNotEqual($count, 0);
    }
*/
    public function testSerialize()
    {
        // just make sure it does something; other tests can make
        // sure it does the right thing
        $this->assertTrue(is_string($this->model->serializeStatements($this->serializer)));
    }

    public function testSerializeToFile()
    {
        $tempfile = tempnam(".", "serializer");
        $this->model->serializeStatementsToFile($this->serializer,
            $tempfile);
        $stat = stat($tempfile);
        unlink($tempfile);
        $this->assertNotEqual($stat["size"], 0);
    }
}
