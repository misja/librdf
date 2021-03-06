<?php

namespace librdf\exception;

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
 * The exception type used for statement lookup failures.
 *
 * An object of this type is thrown by {@link Model} when getSource,
 * getArc or getTarget is called with nodes that do not match any statement.
 *
 * @package     LibRDF
 * @author      David Shea <david@gophernet.org>
 * @copyright   2006 David Shea
 * @license     LGPL/GPL/APACHE
 * @version     Release: 1.0.0
 * @link        http://reallylongword.org/projects/librdf-php/
 */
class LookupError extends \Exception
{
    /**
     * Create a new LookupError.
     *
     * @param   string  $message    The error message to use
     * @return  void
     * @access  public
     */
    public function __construct($message=NULL)
    {
        parent::__construct($message);
    }
}
?>