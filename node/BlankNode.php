<?php

namespace librdf\node;

/**
 * 
 */
use librdf\exception\Error;

/**
 * A representation of a blank node.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class BlankNode extends \librdf\Node
{
    /**
     * Create a new blank node with an optional identifier.
     *
     * @param   mixed   $nodeId     The nodeId value or node resource
     * @return  void
     * @throws  Error        If unable to create a new node
     * @access  public
     */
    public function __construct($nodeId=NULL)
    {
        if ($nodeId !== NULL) {
            if (is_resource($nodeId)) {
                if (librdf_node_is_blank($nodeId)) {
                    $this->node = $nodeId;
                } else {
                    throw new Error("Resource argument not a valid" .
                        " node blank node");
                }
            } else {
                $this->node = librdf_new_node_from_blank_identifier(librdf_php_get_world(),
                    $nodeId);
            }
        } else {
            $this->node = librdf_new_node(librdf_php_get_world());
        }

        if (!$this->node) {
            throw new Error("Unable to create new blank node");
        }
    }
}
