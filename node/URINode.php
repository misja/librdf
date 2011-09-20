<?php

namespace librdf\node;

/**
 *
 */
use librdf\URI;
use librdf\exception\Error;

/**
 * A specialized version of {@link Node} representing a URI.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class URINode extends \librdf\Node
{
    /**
     * Create a new URINode from a URI object.
     *
     * @param   mixed       $uri    The URI string or node value to use
     * @return  void
     * @throws  Error        If unable to create a new URI
     * @access  public
     */
    public function __construct($uri)
    {
        if (is_string($uri)) {
            $uri = new URI($uri);
            $this->node = librdf_new_node_from_uri(librdf_php_get_world(),
                $uri->getURI());
        } elseif ((is_resource($uri)) and librdf_node_is_resource($uri)) {
            $this->node = $uri;
        } else {
            throw new Error("Argument is not a string or node resource");
        }

        if (!$this->node) {
            throw new Error("Unable to create new URI node");
        }
    }
}
