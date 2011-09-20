<?php

namespace librdf\query;

/**
 * 
 */
use librdf\exception\Error;
use librdf\StreamIterator;

/**
 * A specialized query_results wrapper for graph results.
 *
 * Graph results are returned by queries that construct a graph based on
 * triples that meet certain conditions such as those using the "CONSTRUCT"
 * or "DESCRIBE" SPARQL keywords.
 *
 * Iterating over this class will result in a stream of
 * {@link Statement} objects, similar to the result of iterating over
 * a {@link Model}.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class GraphQueryResults extends \librdf\QueryResults
{
    /**
     * The StreamIterator used for iterating over the statements.
     *
     * @var     StreamIterator
     * @access  private
     */
    private $iterator;

    /**
     * Create a new graph query result object.
     *
     * @param   resource    $query_results  The query results to wrap
     * @return  void
     * @throws  Error                If unable to wrap the query results
     * @access  public
     */
    public function __construct($query_results)
    {
        if ((!is_resource($query_results)) or
            (!librdf_query_results_is_graph($query_results))) {
            throw new Error("Argument must be a graph query_results resource");
        }
        $this->query_results = $query_results;
        $this->iterator = NULL;
    }

    /**
     * Reset the $iterator variable with a new stream.
     *
     * @return  void
     * @access  private
     */
    private function resetIterator()
    {
        if ($this->iterator === NULL) {
            $this->iterator = new StreamIterator(librdf_query_results_as_stream($this->query_results));
        }
    }

    /**
     * Rewind the iterator.
     *
     * @return  void
     * @access  public
     */
    public function rewind()
    {
        $this->iterator = NULL;
    }

    /**
     * Fetch the current statement on the iterator.
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
     * Fetch the iterator's current key.
     *
     * @return  integer             The current key
     * @access  public
     */
    public function key()
    {
        $this->resetIterator();
        return $this->iterator->key();
    }

    /**
     * Advance the iterator to the next statement.
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
     * Return whether the iterator is still valid.
     *
     * @return  boolean     Whether the iterator is valid
     * @access  public
     */
    public function valid()
    {
        $this->resetIterator();
        return $this->iterator->valid();
    }
}
