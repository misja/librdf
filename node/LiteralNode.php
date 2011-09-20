<?php

namespace librdf\node;

/**
 * 
 */
use librdf\URI;
use librdf\exception\Error;

/**
 * A representation of a literal node.
 *
 * Literal nodes can have a type and a language, but not both.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class LiteralNode extends \librdf\Node
{
    /**
     * Create a new Literal node.
     *
     * Both the $language and $datatype parameters are optional.
     *
     * The value of the literal node can either be a string or an XML literal
     * in the form of a DOMNodeList object.  If using XML, a datatype of
     * http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral is implied, so
     * the datatype parameter cannot be used with XML.  A literal cannot have
     * both a language and a datatype.
     *
     * @param   mixed       $value      The literal value, either a string, a DOMNodeList or a node resource
     * @param   string      $datatype   An optional datatype URI for the literal value
     * @param   string      $language   An option language for the literal value
     * @return  void
     * @throws  Error            If unabel to create a new node
     * @access  public
     */
    public function __construct()
    {
        $valuestr = "";
        $is_xml = 0;

        // possible parameter lists are either Node $resource or
        // string $value, $datatype=NULL, string $language=NULL
        $num_args = func_num_args();
        if (($num_args < 1) or ($num_args > 3)) {
            throw new Error("Invalid number of arguments");
        }
        $value = func_get_arg(0);
        if ($num_args >= 2) {
            $datatype = func_get_arg(1);
            if ($datatype) {
                $datatype = new URI($datatype);
            }
        } else {
            $datatype = NULL;
        }
        if ($num_args >= 3) {
            $language = func_get_arg(2);
        } else {
            $language = NULL;
        }

        if (($num_args == 1) and (is_resource($value))) {
            if (!librdf_node_is_literal($value)) {
                throw new Error("Argument 1 not a valid node " .
                    " literal node");
            } else {
                $this->node = $value;
            }
        } else {

            // value is XML, convert to a string and set the datatype
            if ($value instanceof DOMNodeList) {
                // XML values imply a datatype of
                // http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral, so
                // specifying a different datatype is an error
                if (($datatype !== NULL) and
                    ($datatype->__toString() !== "http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral")) {
                    throw new Error("Cannot override datatype for XML literal");
                } else {
                    $datatype = NULL;
                }

                $valuestr = "";
                foreach ($value as $item) {
                    $valuestr .= $item->ownerDocument->saveXML($item);
                }
                $is_xml = 1;
            } else {
                $valuestr = (string) $value;
                $is_xml = 0;
            }

            if ($datatype !== NULL) {
                $datatype_uri = $datatype->getURI();
            } else {
                $datatype_uri = NULL;
            }

            if (($is_xml) or (($datatype === NULL) and ($language === NULL))) {
                $this->node = librdf_new_node_from_literal(librdf_php_get_world(),
                    $valuestr, $language, $is_xml);
            } else {
                $this->node = librdf_new_node_from_typed_literal(librdf_php_get_world(),
                    $valuestr, $language, $datatype_uri);
            }
        }

        if (!$this->node) {
            throw new Error("Unable to create new literal node");
        }
    }

    /**
     * Return the datattype URI or NULL if this literal has no datatype.
     *
     * @return  string      The datatype URI
     * @access  public
     */
    public function getDatatype()
    {
        $uri = librdf_node_get_literal_value_datatype_uri($this->node);
        if ($uri !== NULL) {
            return librdf_uri_to_string($uri);
        } else {
            return NULL;
        }
    }

    /**
     * Return the language of this literal or NULL if the literal has no
     * language.
     *
     * @return  string  The literal's language
     * @access  public
     */
    public function getLanguage()
    {
        return librdf_node_get_literal_value_language($this->node);
    }
}