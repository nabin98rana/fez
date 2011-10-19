<?php

/**
 * Validator to check for filenames containing only 
 * alpha characters, digits, underscores and dashes.
 * File extensions must be alphanumeric and up to 
 * four characters long.
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */
class Fez_Validate_Filename extends Zend_Validate_Abstract
{
    protected $_messageTemplates = array(
        'msg' => "'%value%' does not appear to be a filename.");
        
    public function isValid($value)
    {
        $this->_setValue($value);
        
        if(!preg_match("/^[a-zA-Z0-9\_\-]*\.[a-zA-Z0-9]{0,4}$/", $value))
        {
            $this->_error();
            return false;
        }
        
        return true;
    }
}