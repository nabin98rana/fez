<?php

/**
 * Validation class to ensure a valid username is entered.
 * @author uqcmaj
 *
 */
class Fez_Validate_Username extends Zend_Validate_Abstract
{
	protected $_messageTemplates = array(
	        'msg' => "'%value%' is not a valid username.");
	
	/**
	 * Main validation method
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value)
	{
		//Is it blank?
		$val = trim($value);
		if(strlen($val) == 0)
		{
			$this->_error($this->_messageTemplates['msg']);
			return false;
		}
		
		//Is the string too long?
		if(strlen($val) > 50)
		{
			$this->_error($this->_messageTemplates['msg']);
			return false;
		}
		
		//Are all the chars legal?
		$goodChars = '/[^a-zA-Z0-9@\.\_\-\']/';
		if(preg_match($goodChars, $val))
		{
			$this->_error($this->_messageTemplates['msg']);
			return false;
		}
		
		return true;
	}
}
