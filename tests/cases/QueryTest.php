<?php

namespace librdf\tests\cases;

// test the Query and QueryResults classes

use librdf\Storage;
use librdf\Statement;
use librdf\Query;
use librdf\Model;
use librdf\QueryResults;
use librdf\node\URINode;
use librdf\node\LiteralNode;

class QueryTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->rdqlQuery = new Query("SELECT ?a, ?c WHERE (?a, <http://www.example.com/predicates/#p2>, ?c)");

        // copied from ModelTest because I need a model
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
    }

    public function testConstructor()
    {
        $this->assertTrue($this->rdqlQuery instanceof \librdf\Query);
    }

    // clone not implemented and I don't care
    //public function testClone()
    // {
    //     $query = clone $this->rdqlQuery;
    //     $this->assertTrue($query instanceof \librdf\Query);
    //     $this->assertNotSame($this->rdqlQuery, $query);
    // }

    public function testExecute()
    {
        $result1 = $this->rdqlQuery->execute($this->model);

        $this->assertTrue($result1 instanceof \librdf\QueryResults);
    }

    public function testBindings()
    {
        $count = 0;
        foreach ($this->rdqlQuery->execute($this->model) as $binding) {
            $this->assertTrue($binding["a"]  instanceof \librdf\node\URINode);
            $this->assertTrue($binding["c"]  instanceof \librdf\node\LiteralNode);
            if ($count == 0) {
                $this->assertTrue($binding["a"]->isEqual($this->sourceNode1));
                $this->assertTrue($binding["c"]->isEqual($this->targetNode2));
            } elseif ($count == 1) {
                $this->assertTrue($binding["a"]->isEqual($this->sourceNode2));
                $this->assertTrue($binding["c"]->isEqual($this->targetNode2));
            }
            $count++;
        }
        $this->assertEqual($count, 2);
    }

    public function testBoolean()
    {
        $trueQuery = new Query("ASK WHERE { <http://www.example.com/sources/#s1> <http://www.example.com/predicates/#p1> ?x }", NULL, "sparql");
        $this->assertTrue($trueQuery->execute($this->model)->getValue());
        $falseQuery = new Query("ASK WHERE { <http://www.example.com/sources/#s1> <http://www.example.com/predicates/#p3> ?x }", NULL, "sparql");
        $this->assertFalse($falseQuery->execute($this->model)->getValue());

        $count = 0;
        foreach ($trueQuery->execute($this->model) as $bool) {
            $this->assertTrue(is_bool($bool));
            $count++;
        }
        $this->assertEqual($count, 1);
    }

    public function testGraph()
    {
        //$graphQuery = new Query("DESCRIBE <http://www.example.com/>", NULL, "sparql");
        $graphQuery = new Query("CONSTRUCT { <http://www.example.com/sources/#s1> <http://www.example.com/predicates/#p3> ?name } WHERE { ?x <http://www.example.com/predicates/#p2> ?name }", NULL, "sparql");
        $count = 0;
        foreach ($graphQuery->execute($this->model) as $statement) {
            $this->assertTrue($statement instanceof \librdf\Statement);
            $this->assertEqual($statement->getPredicate()->__toString(), "<http://www.example.com/predicates/#p3>");
            $count++;
        }
        $this->assertEqual($count, 2);
    }
}
