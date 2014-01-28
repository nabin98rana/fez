<?php

//Checks dsid are valid. this is a rough check
class Fez_Validate_Dsid extends Zend_Validate_Abstract
{
    protected $_messageTemplates = array(
        'msg' => "'%value%' does not appear to be a valid DatastreamID.");
        
    public function isValid($value)
    {
        $this->_setValue($value);
        
        if (!preg_match("/^[a-zA-Z0-9\_\-\.]*$/", $value)) {
            $this->_error();
            return false;
        }
        
        return true;
    }
}