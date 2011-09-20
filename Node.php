<?php

namespace librdf;

/**
 * Node, a node or arc in an RDF graph.
 *
 * A Node is the type of the {@link Statement} triples.
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
use librdf\node\BlankNode;
use librdf\node\LiteralNode;
use librdf\node\URINode;
/**
 * A wrapper around the node datatype.
 *
 * The values of nodes come from three potential, disjoint sets: URIs,
 * literal strings and blank identifiers.  These types are represented by
 * {@link URINode}, {@link LiteralNode} and 
 * {@link BlankNode}, respectively.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
abstract class Node
{
    /**
     * The underlying node resource.
     *
     * This value must be set by the constructors for the concrete node types.
     *
     * @var     resource
     * @access  protected
     */
    protected $node;

    /**
     * Destroy the Node object.
     *
     * @return  void
     * @access  public
     */
    public function __destruct()
    {
        if ($this->node) {
            librdf_free_node($this->node);
        }
    }

    /**
     * Create a new node object from an existing node.
     *
     * @return  void
     * @throws  Error    If unable to copy the node
     * @access  public
     */
    public function __clone()
    {
        $this->node = librdf_new_node_from_node($this->node);

        if (!$this->node) {
            throw new Error("Unable to create new Node from Node");
        }
    }

    /**
     * Return a string representation of the node.
     *
     * @return  string  A string representation of the node
     * @access  public
     */
    public function __toString()
    {
        return librdf_node_to_string($this->node);
    }

    /**
     * Compare this node with another node for equality.
     *
     * Nodes of different types are not equal; thus, a URI of
     * http://example.org/ and a literal string of http://example.org are not
     * equal, even though they contain the same string.  Similarly, literal
     * nodes must match in both type and language to be considered equal.
     *
     * @param   Node $node   The node against which to compare
     * @return  boolean             Whether the nodes are equal
     * @access  public
     */
    public function isEqual(Node $node)
    {
        return (boolean) librdf_node_equals($this->node, $node->node);
    }

    /**
     * Return the underlying node resource.
     *
     * This function is intended for other LibRDF classes and should not
     * be called.
     *
     * @return  resource    The wrapped node
     * @access  public
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Wrap a node resource in the correct Node object.
     *
     * This function is intended for use by LibRDF classes, allowing them
     * to easily convert a node resource into the correct type of
     * Node object.
     *
     * @param   resource    $node   The node to convert
     * @return  Node         A concrete object implementing Node
     * @throws  Error        If unable to create a new node
     * @access  public
     * @static
     */
    public static function makeNode($node)
    {
        if (!is_resource($node)) {
            throw new Error("Argument must be a node resource");
        }

        if (librdf_node_is_resource($node)) {
            return new URINode($node);
        } elseif (librdf_node_is_literal($node)) {
            return new LiteralNode($node);
        } elseif (librdf_node_is_blank($node)) {
            return new BlankNode($node);
        } else {
            throw new Error("Unknown query results type");
        }
    }
}