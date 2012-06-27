<?php

/**
 * Validator to check for valid PIDS
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */
class Fez_Validate_Pid extends Zend_Validate_Abstract
{
    protected $_messageTemplates = array(
        'msg' => "'%value%' does not appear to be a PID.");
        
    public function isValid($value)
    {
        $this->_setValue($value);
        
        if(!preg_match("/^[a-zA-Z]{2,60}\:[0-9]+$/", $value)) {
            $this->_error();
            return false;
        }
        
        return true;
    }
}