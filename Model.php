<?php

namespace librdf;

/* $Id: Model.php 171 2006-06-15 23:24:18Z das-svn $ */
/**
 * Model, a representation of an RDF graph.
 *
 * PHP version 5
 *
 * Copyright (C) 2006, David Shea <david@gophernet.org>
 *
 * LICENSE: This package is Free Software and a derivative work of Redland
 * http://librdf.org/.  This package is not endorsed by Dave Beckett or the 
 * University of Bristol. It is licensed under the following three licenses as 
 * alternatives:
 *   1. GNU Lesser General Public License (LGPL) V2.1 or any newer version
 *   2. GNU General Public License (GPL) V2 or any newer version
 *   3. Apache License, V2.0 or any newer version
 *
 * You may not use this file except in compliance with at least one of the
 * above three licenses.
 *
 * See LICENSE.txt at the top of this package for the complete terms and futher
 * detail along with the license tests for the licenses in COPYING.LIB, COPYING
 * and LICENSE-2.0.txt repectively.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */

/**
 */
use librdf\exception\Error;
use librdf\exception\LookupError;
use librdf\URI;
use librdf\Storage;
use librdf\Statement;
use librdf\Iterator;
use librdf\Parser;
use librdf\Serializer;
use librdf\Node;
use librdf\node\BlankNode;
use librdf\node\LiteralNode;
use librdf\node\URINode;

/**
 * A wrapper around the model datatype.
 *
 * A Model is a collection of {@link Statement} objects using
 * a {@link Storage} object to save the statements.  Statements are
 * added using {@link addStatement} or through the use of a
 * {@link Parser} and {@link loadStatementsFromString} or
 * {@link loadStatementsFromURI}, and statements are removed using
 * {@link removeStatement}.  Statements can be queried through the use of
 * either {@link findStatements} or a {@link Query} object.  The
 * statements can be written to a stream using {@link Serializer} and
 * {@link serializeStatements} or {@link serializeStatementsToFile}.
 *
 * This object is iterable.  When used as part of a foreach statement, it
 * will iterate over every statement contained in the model.  For example,
 *
 * <code>foreach ($model as $statement) {
 *    echo $statement;
 * }</code>
 *
 * will echo each statement individually.  Unlike {@link StreamIterator},
 * the Model can be rewound and used for multiple iterations.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class Model implements \Iterator
{
    /**
     * The underlying model.
     *
     * @var     resource
     * @access  private
     */
    private $model;

    /**
     * The stream iterator over the model's statements.
     *
     * This variable begins as NULL and is set by the iteration functions
     * using {@link resetIterator}.  {@link rewind} resets this variable to
     * null, causing subsequent calls of the other iteration function to
     * begin anew with a fresh iterator.
     *
     * @var     StreamIterator
     * @access  private
     */
    private $iterator;

    /**
     * Create a new model.
     *
     * See the {@link http://librdf.org/ librdf} documentation for information
     * on the possible options.
     *
     * @param   Storage  $storage    The storage on which this model should be built
     * @param   string          $options    Options to pass to new_model
     * @return  void
     * @throws  Error    If unable to create a new model
     * @access  public
     */
    public function __construct(Storage $storage, $options=NULL)
    {
        $this->model = librdf_new_model(librdf_php_get_world(),
            $storage->getStorage(), $options);

        if (!$this->model) {
            throw new Error("Unable to create new model");
        }

        $this->iterator = NULL;
    }

    /**
     * Free a model's resources.
     *
     * @return  void
     * @access  public
     */
    public function __destruct()
    {
        if ($this->model) {
            librdf_free_model($this->model);
        }
    }

    /**
     * Return a string representation of the model.
     *
     * This function can be used as a lazy form of serializtion.  Use
     * a {@link Serializer} if you care about the format of the output.
     *
     * @return  string  The model as a string
     * @access  public
     */
    public function __toString()
    {
        return librdf_model_to_string($this->model, NULL, NULL, NULL, NULL);
    }
    
    /**
     * Create a copy of the model.
     *
     * Whether a model can be copied depends upon the underlying model factory.
     * In-memory storages cannot be cloned, so a clone of models using this
     * form of storage will fail.
     *
     * @return  void
     * @throws  Error    If unable to copy the model
     * @access  public
     */
    public function __clone()
    {
        $this->model = librdf_new_model_from_model($this->model);

        if (!$this->model) {
            throw new Error("Unable to create new model from model");
        }
    }

    /**
     * Return the model resource.
     *
     * This function is intended for other LibRDF classes and should not
     * be called.
     *
     * @return  resource    The wrapped model resource
     * @access  public
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Add a statement to the model.
     *
     * A statement can be added more than once by adding it under different
     * contexts, otherwise adding a duplicate statement will have no effect.
     * Not all models support contexts.
     *
     * @param   Statement    $statement  The statement to add
     * @param   URINode      $context    An optional context under which to add the statement
     * @return  void
     * @throws  Error        If unable to add the statement
     * @access  public
     */
    public function addStatement(Statement $statement,
        URINode $context=NULL)
    {
        // This function raises some issues with threading and what to do 
        // with an active iterator.  I don't know if PHP even has a concept
        // of threads, so I'm just going to leave any active iterator alone
        // and hope that the underlying stream does the right thing,
        // whatever that may be.
        if ($context != NULL) {
            $context = $context->getNode();
            $ret = librdf_model_context_add_statement($this->model,
                $context, $statement->getStatement());
        } else {
            $ret = librdf_model_add_statement($this->model,
                $statement->getStatement());
        }

        if ($ret) {
            throw new Error("Unable to add statement");
        }
    }

    /**
     * Remove a statement from the model.
     *
     * @param   Statement    $statement  The statement to remove
     * @param   URINode      $context    The context from which to remove the statement
     * @return  void
     * @throws  Error        If unable to remove the statement
     * @access  public
     */
    public function removeStatement(Statement $statement,
        URINode $context=NULL)
    {
        if ($context != NULL) {
            $context = $context->getNode();
            $ret = librdf_model_context_remove_statement($this->model, $context,
                $statement->getStatement());
        } else {
            $ret = librdf_model_remove_statement($this->model, 
                $statement->getStatement());
        }

        if ($ret) {
            throw new Error("Unable to remove statement");
        }
    }

    /**
     * Return the number of statements in the model.
     *
     * @return  integer The number of statements
     * @access  public
     */
    public function size()
    {
        return librdf_model_size($this->model);
    }

    /**
     * Return a single source node that is part of a statement containing
     * the given predicate and target.
     *
     * This function is equivalent to 
     * <code>$model->findStatements(NULL, $predicate, $target)->current()->getSubject()</code>
     *
     * @param   Node     $arc    The predicate node for which to search
     * @param   Node     $target The target node for which to search
     * @return  Node             A node that matches the criteria
     * @throws  LookupError      If no statement with the given predicate and target is found
     * @access  public
     */
    public function getSource(Node $arc, Node $target)
    {
        $source = librdf_model_get_source($this->model,
            $arc->getNode(), $target->getNode());
        if ($source) {
            return Node::makeNode($source);
        } else {
            throw new LookupError("No such statement");
        }
    }

    /**
     * Return a single predicate node that is part of a statement containing
     * the given source and target.
     *
     * This function is equivalent to
     * <code>$model->findStatements($source, NULL, $target)->current()->getPredicate()</code>
     *
     * @param   Node     $source The source node for which to search
     * @param   Node     $target The target node for which to search
     * @return  Node             A node that matches the criteria
     * @throws  LookupError      If no statement with the given source and target is found
     * @access  public
     */
    public function getArc(Node $source, Node $target)
    {
        $arc = librdf_model_get_arc($this->getModel(),
            $source->getNode(), $target->getNode());
        if ($arc) {
            return Node::makeNode($arc);
        } else {
            throw new LookupError("No such statement");
        }
    }

    /**
     * Return a single target node that is part of a statement containing the
     * given source and predicate.
     *
     * This function is equivalent to
     * <code>$model->findStatements($source, $predicate, NULL)->current()->getTarget()</code>
     *
     * @param   Node     $source The source node for which to search
     * @param   Node     $arc    The predicate node for which to search
     * @return  Node             A node that matches the criteria
     * @throws  LookupError      If no statement with the given source and predicate is found
     * @access  public
     */
    public function getTarget(Node $source, Node $arc)
    {
        $target = librdf_model_get_target($this->model,
            $source->getNode(), $arc->getNode());
        if ($target) {
            return Node::makeNode($target);
        } else {
            throw new LookupError("No such statement");
        }
    }

    /**
     * Test whether the model contains a statement.
     *
     * @param   Statement    $statement  The statement for which to search
     * @return  boolean                         Whether such a statement exists in the graph
     * @access  public
     */
    public function hasStatement(Statement $statement)
    {
        if (librdf_model_contains_statement($this->model, 
                $statement->getStatement())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Find a statement in the model.
     *
     * A NULL argument for any of source, predicate or target is treated as
     * a wildcard.  If a context is given, only statements from that context
     * will be returned.  The result is an object that be used in foreach
     * iteration.  The returned iterator cannot be rewound.
     *
     * The search arguments can be either a (source, predicate target) triple
     * of Node objects or a Statement object.  Valid argument 
     * lists are (source, predicate, target, [context]) or
     * (statement, [context]).
     *
     * For more complex queries, see {@link Query}.
     *
     * @param   mixed       $statement  The statement to match or a source node
     * @param   Node $predicate  The predicate to match
     * @param   Node $target     The target to match
     * @param   URINode  $context    The context in which to search
     * @return  StreamIterator   An iterator over the matched statements
     * @access  public
     */
    public function findStatements()
    {
        $num_args = func_num_args();
        if (($num_args == 1) or ($num_args == 2)) {
            $statement = func_get_arg(0);
            if (!($statement instanceof Statement)) {
                throw new Error("First argument must be a Statement");
            }

            if ($num_args == 2) {
                $context = func_get_arg(1);
                if (!($context instanceof URINode)) {
                    throw new Error("Context must be URINode");
                }
            } else {
                $context = NULL;
            }

            $statement = $statement->getStatement();
        } elseif (($num_args == 3) or ($num_args == 4)) {
            $source = func_get_arg(0);
            $predicate = func_get_arg(1);
            $target = func_get_arg(2);

            if ($source !== NULL) {
                if (!($source instanceof Node)) {
                    throw new Error("Argument 1 must be of type Node");
                } else {
                    $source = librdf_new_node_from_node($source->getNode());
                }
            }

            if ($predicate !== NULL) {
                if (!($predicate instanceof Node)) {
                    throw new Error("Argument 2 must be of type Node");
                } else {
                    $predicate = librdf_new_node_from_node($predicate->getNode());
                }
            }

            if ($target !== NULL) {
                if (!($target instanceof Node)) {
                    throw new Error("Argument 3 must be of type Node");
                } else {
                    $target = librdf_new_node_from_node($target->getNode());
                }
            }

            if ($num_args == 4) {
                $context = func_get_arg(3);
                if (!($context instanceof URINode)) {
                    throw new Error("Context must be URINode");
                }
            } else {
                $context = NULL;
            }

            $statement = librdf_new_statement_from_nodes(librdf_php_get_world(),
                $source, $predicate, $target);
        } else {
            throw new Error("findStatements takes 2-4 arguments");
        }

        if ($context !== NULL) {
            $stream_resource = librdf_model_find_statements_in_context($this->model,
                $statement, $context->getNode());
        } else {
            $stream_resource = librdf_model_find_statements($this->model,
                $statement);
        }

        if ($num_args > 2) {
            librdf_free_statement($statement);
        }

        if (!$stream_resource) {
            throw new Error("Unable to create new statement iterator");
        }

        return new StreamIterator($stream_resource, $this);
    }

    /**
     * Discard the current statement iterator and create a new one.
     *
     * @return  void
     * @access  private
     */
    private function resetIterator()
    {
        if ($this->iterator === NULL) {
            $this->iterator = new 
                StreamIterator(librdf_model_as_stream($this->model),
                    $this);
        }
    }

    /**
     * Reset the statement iterator.
     *
     * @return  void
     * @access  public
     */
    public function rewind()
    {
        $this->iterator = NULL;
    }

    /**
     * Return the current statement on the iterator.
     *
     * @return  Statement    The current statement
     * @access  public
     */
    public function current()
    {
        $this->resetIterator();
        return $this->iterator->current();
    }

    /**
     * Return the current iteration key.
     *
     * @return  integer The current key
     * @access  public
     */
    public function key()
    {
        $this->resetIterator();
        return $this->iterator->key();
    }

    /**
     * Advance the iterator's position.
     *
     * @return  void
     * @access  public
     */
    public function next()
    {
        $this->resetIterator();
        return $this->iterator->next();
    }

    /**
     * Check whether the statement iterator is still valid.
     *
     * @return  boolean     Whether the iterator is still valid
     * @access  public
     */
    public function valid()
    {
        $this->resetIterator();
        return $this->iterator->valid();
    }

    /**
     * Load statements using a {@link Parser}.
     *
     * If no $base_uri is given, the RDF namespace URI will be used as the
     * base for relative URIs.
     *
     * @param   Parser   $parser The parser with which to parse the string
     * @param   string          $string The string to parse
     * @param   string          $base_uri   The base URI to use for relative URIs in the string
     * @return  void
     * @throws  Error    If unable to parse the string
     * @access  public
     */
    public function loadStatementsFromString(Parser $parser,
        $string, $base_uri=NULL)
    {
        if ($base_uri) {
            $base_uri = new URI($base_uri);
        } else {
            $base_uri = new URI(URI::RDF_BASE_URI);
        }

        $ret = librdf_parser_parse_string_into_model($parser->getParser(),
            $string, $base_uri->getURI(), $this->model);

        if ($ret) {
            throw new Error("Unable to parse string into model");
        }
    }

    /**
     * Load statements from a URI using a {@link Parser}.
     *
     * @param   Parser   $parser The parser with which to parse the URI's contents
     * @param   string          $uri    The URI with the contents to load
     * @param   string          $base_uri   The base URI to use for relative URIs if different from $uri
     * @return  void
     * @throws  Error    If unable to parse the URI contents
     * @access  public
     */
    public function loadStatementsFromURI(Parser $parser,
        $uri, $base_uri=NULL)
    {
        $uri = new URI($uri);
        if ($base_uri) {
            $base_uri = new URI($base_uri);
        }

        $ret = librdf_parser_parse_into_model($parser->getParser(),
            $uri->getURI(), ($base_uri ? $base_uri->getURI() : $base_uri), 
            $this->model);

        if ($ret) {
            throw new Error("Unable to parse URI into model");
        }
    }

    /**
     * Serialize the model as a string.
     *
     * @param   Serializer   $serializer The serializer to use
     * @param   string              $base_uri   The base URI to use if relative URIs are desired
     * @return  string              The model as a string
     * @throws  Error        If unable to serialize the model
     * @access  public
     */
    public function serializeStatements(Serializer $serializer,
        $base_uri=NULL)
    {
        if ($base_uri) {
            $base_uri = new URI($base_uri);
        }

        $ret = librdf_serializer_serialize_model_to_string($serializer->getSerializer(),
            ($base_uri ? $base_uri->getURI() : $base_uri), $this->model);

        if (!$ret) {
            throw new Error("Unable to serialize model");
        } else {
            return $ret;
        }
    }

    /**
     * Serialize the model and write the contents to a file.
     *
     * @param   Serializer   $serializer The serializer to use
     * @param   string              $file_name  The name of the file to which to write
     * @param   string              $base_uri   The base URI to use
     * @return  void
     * @throws  Error        If unable to serialize the model
     * @access  public
     */
    public function serializeStatementsToFile(Serializer $serializer,
        $file_name, $base_uri=NULL)
    {
        if ($base_uri) {
            $base_uri = new URI($base_uri);
        }

        $ret = librdf_serializer_serialize_model_to_file($serializer->getSerializer(),
            $file_name, ($base_uri ? $base_uri->getURI() : $base_uri), $this->model);

        if ($ret) {
            throw new Error("Error serializing model to file");
        }
    }
}

?>
