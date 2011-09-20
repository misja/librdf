<?php

namespace librdf;

/* $Id: QueryResults.php 171 2006-06-15 23:24:18Z das-svn $ */
/**
 * QueryResults, the answer to a Query.
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
use librdf\URI;
use librdf\query\BindingsQueryResults;
use librdf\query\GraphQueryResults;
use librdf\query\BooleanQueryResults;

/**
 * A wrapper around the query_results datatype.
 *
 * This is the generic query results wrapper.  There are three possible types
 * of query results--boolean (those returned by SPARQL "ASK"), bindings
 * (returned by "SELECT" in SPARQL and RDQL) and graph (such as those returned
 * by SPARQL "CONSTRUCT" and "DESCRIBE")--each with a specialized class, but
 * each is an iterable object.  This creates an odd case for booleans, which
 * are an iterator containing one element.  As a special concession for this
 * single-result case, {@link BooleanQueryResults} objects also have a 
 * method to simply retrieve the boolean value without iteration.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
abstract class QueryResults implements \Iterator
{
    /**
     * The wrapped query_results datatype.
     *
     * This resource must be set by the concrete query results classes.
     *
     * @var     resource
     * @access  private
     */
    protected $query_results;

    /**
     * Free the query result resources.
     *
     * @return  void
     * @access  public
     */
    public function __destruct()
    {
        if ($this->query_results) {
            librdf_free_query_results($this->query_results);
        }
    }

    /**
     * Clone the query.
     *
     * Clonining a query is not supported, so this function disables the use
     * of the clone keyword by setting the underlying resource to NULL and
     * throwing an exception.
     *
     * @return  void
     * @throws  Error    Always
     * @access  public
     */
    public function __clone()
    {
        // destroying the results instead of resetting them
        // since there's no way to duplicate the resource or prevent
        // a parent object from freeing the results before its clone
        $this->query_results = NULL;
        throw new Error("Cloning query results is not supported");
    }

    /**
     * Return the query results as a string.
     *
     * The language of the results depends on the query type.
     *
     * @return  string  The query results as a string
     * @access  public
     */
    public function __toString()
    {
        return $this->to_string();
    }

    /**
     * Serialize the results to a string.
     *
     * @param   string      $uri        The uri of the target syntax or NULL
     * @param   string      $base_uri   The base URI for the output or NULL
     * @return  string                  The results as a string
     * @throws  Error            If unable to create a string from the results
     * @access  public
     */
    public function to_string($uri=NULL, $base_uri=NULL)
    {
        if ($uri) {
            $uri = new URI($uri);
        }

        if ($base_uri) {
            $base_uri = new URI($base_uri);
        }

        $ret = librdf_query_results_to_string($this->query_results,
            ($uri ? $uri->getURI() : NULL),
            ($base_uri ? $base_uri->getURI() : NULL));

        if ($ret) {
            return $ret;
        } else {
            throw new Error("Unable to convert the query results to a string");
        }
    }

    /**
     * Make a specialized query results object.
     *
     * This function is intended for use by {@link Query}, allowing
     * the creating of a specific query results object from a
     * query_results resource.
     *
     * @param   resource    $query_results  The query_results resource to wrap
     * @return  QueryResults         The wrapped query results
     * @throws  Error                If unable to wrap the object
     * @access  public
     * @static
     */
    public static function makeQueryResults($query_results)
    {
        if (!is_resource($query_results)) {
            throw new Error("Argument must be a query_results resource");
        }

        if (librdf_query_results_is_bindings($query_results)) {
            return new BindingsQueryResults($query_results);
        } elseif (librdf_query_results_is_boolean($query_results)) {
            return new BooleanQueryResults($query_results);
        } elseif (librdf_query_results_is_graph($query_results)) {
            return new GraphQueryResults($query_results);
        } else {
            throw new Error("Unknown query results type");
        }
    }
}

?>
