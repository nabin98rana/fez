<?php

/**
 * Validator to allow only alpha characters and underscores
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */
class Fez_Validate_Simpleparam extends Zend_Validate_Abstract
{
    protected $_messageTemplates = array(
        'msg' => "'%value%' contains illegal characters.");
        
    public function isValid($value)
    {
        $this->_setValue($value);
        
        if(!preg_match("/^[a-zA-Z\_]+$/", $value))
        {
            $this->_error();
            return false;
        }
        
        return true;
    }
}