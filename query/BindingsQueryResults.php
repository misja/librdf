<?php

namespace librdf\query;

/**
 * 
 */
use librdf\exception\Error;
use librdf\Node;

/**
 * A specialized query_results wrapper for bindings results.
 *
 * Bindings are returned by SELECT statements and associate result nodes with
 * names for each tuple in the result set.  For example, the query
 *
 * <samp>SELECT ?book, ?author WHERE (?book, dc:creator, ?author)<br>
 * USING dc for <http://purl.org/dc/elements/1.1/>"</samp>
 *
 * in RDQL would result in a set of tuples, each containing a value for
 * "author" and "book".  This results of iterating over this object are
 * associative arrays of the result names and values.  The iterator cannot
 * be rewound.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class BindingsQueryResults extends \librdf\QueryResults
{
    /**
     * Whether the iterator is still valid.
     *
     * @var     boolean
     * @access  private
     */
    private $isvalid;

    /**
     * Whether the iterator is rewindable; i.e., whether the iterator has been
     * advanced.
     *
     * @var     boolean
     * @access  private
     */
    private $rewindable;

    /**
     * Create a new bindings query result object.
     *
     * @param   resource    $query_results  The query results to wrap
     * @return  void
     * @throws  Error                If unable to wrap the query results
     * @access  public
     */
    public function __construct($query_results)
    {
        if ((!is_resource($query_results)) or
            (!librdf_query_results_is_bindings($query_results))) {
            throw new Error("Argument must be a bindings query_results resource");
        }
        $this->query_results = $query_results;
        $this->isvalid = true;
        $this->rewindable = true; 
        $this->key = 0;
    }

    /**
     * Rewind the iterator.
     *
     * Rewinding is not supported, so this function will invalidate the
     * iterator unless it is still in the initial (rewound) position.
     *
     * @return  void
     * @access  public
     */
    public function rewind()
    {
        if (!($this->rewindable)) {
            $this->isvalid = false;
        }
    }

    /**
     * Return the current tuple of bindings.
     *
     * The result is an associative array using the binding names as the
     * indices.
     *
     * @return  array           The current bindings tuple
     * @throws  Error    If unable to get the current bindings tuple
     * @access  public
     */
    public function current()
    {
        if (($this->isvalid) and (!librdf_query_results_finished($this->query_results))) {
            $retarr = array();
            $numbindings = librdf_query_results_get_bindings_count($this->query_results);
            if ($numbindings < 0) {
                throw new Error("Unable to get number of bindings in result");
            }

            for ($i = 0; $i<$numbindings; $i++) {
                $key = librdf_query_results_get_binding_name($this->query_results, $i);
                $value = librdf_query_results_get_binding_value($this->query_results, $i);

                if ((!$key) or ((!$value))) {
                    throw new Error("Failed to get current binding $i");
                }
                $retarr[$key] = Node::makeNode(librdf_new_node_from_node($value));
            }

            return $retarr;
        } else {
            return NULL;
        }
    }

    /**
     * Return the current key.
     *
     * @return  integer     The current key
     * @access  public
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Advance the iterator.
     *
     * @return  void
     * @access  public
     */
    public function next()
    {
        if ($this->isvalid) {
            $this->rewindable = false;
            $ret = librdf_query_results_next($this->query_results);
            if ($ret) {
                $this->isvalid = false;
            } else {
                $this->key++;
            }
        }
    }

    /**
     * Return whether the iterator is still valid.
     *
     * @return  boolean     Whether the iterator is valid
     * @access  public
     */
    public function valid()
    {
        if (($this->isvalid) and (!librdf_query_results_finished($this->query_results))) {
            return true;
        } else {
            return false;
        }
    }
}
