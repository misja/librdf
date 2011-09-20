<?php

namespace librdf\query;

/**
 * 
 */
use librdf\exception\Error;

/**
 * A specialized query_results wrapper for boolean results.
 *
 * Boolean results are returned when using an "ASK" query form to test
 * whether triples exist that satisfy certain conditions.  For example,
 *
 * <samp>PREFIX dc: <http://purl.org/dc/elements/1.1/><br>
 * ASK WHERE { ?book dc:creator ?author }</samp>
 *
 * in SPARQL will return a boolean result representing whether there is any
 * triple with the http://purl.org/dc/elements/1.1/creator predicate.
 *
 * In addition to iteration (which will iterate over a single boolean element),
 * a function {@link getValue} is provided to simply retrieve the result the
 * query.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class BooleanQueryResults extends \librdf\QueryResults
{
    /**
     * Whether the iterator is still valid; i.e., whether next() has not been
     * called.
     *
     * @var     boolean
     * @access  private
     */
    private $isvalid;

    /**
     * Create a new boolean query result object.
     *
     * @param   resource    $query_results  The query results to wrap
     * @return  void
     * @throws  Error                If unable to wrap the query results
     * @access  public
     */
    public function __construct($query_results)
    {
        if ((!is_resource($query_results)) or 
            (!librdf_query_results_is_boolean($query_results))) {
            throw new Error("Argument must be a boolean query_results resource");
        }
        $this->query_results = $query_results;
        $this->isvalid = true;
    }

    /**
     * Return the boolean value of the result.
     *
     * @return  boolean     The value of the query
     * @access  public
     */
    public function getValue()
    {
        if (librdf_query_results_get_boolean($this->query_results)) {
            return true;
        } else {
            return false;
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
        $this->isvalid = true;
    }

    /**
     * Return the current (and only) boolean value.
     *
     * @return  boolean     The current value
     * @access  public
     */
    public function current()
    {
        $ret = librdf_query_results_get_boolean($this->query_results);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the iterator key (always 0).
     *
     * @return  integer     The current key
     * @access  public
     */
    public function key()
    {
        return 0;
    }

    /**
     * Advance the iterator.
     *
     * Since boolean results have only one result, this function renders the
     * iterator invalid.
     *
     * @return  void
     * @access  public
     */
    public function next()
    {
        $this->isvalid = false;
    }

    /**
     * Test whether the iterator is still valid.
     *
     * @return  boolean     Whether the iterator is valid
     * @access  public
     */
    public function valid()
    {
        return $this->isvalid;
    }
}
