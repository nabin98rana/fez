<?php

//Pid check as per https://wiki.duraspace.org/display/FEDORA34/Fedora+Identifiers
class Fez_Validate_Pid extends Zend_Validate_Abstract
{
    protected $_messageTemplates = array(
        'msg' => "'%value%' does not appear to be a PID.");
        
    public function isValid($value)
    {
        $this->_setValue($value);
        
        if (!preg_match("/^([A-Za-z0-9]|-|\.)+:(([A-Za-z0-9])|-|\.|~|_|(%[0-9A-F]{2}))+$/", $value)) {
            $this->_error();
            return false;
        }
        
        return true;
    }
}