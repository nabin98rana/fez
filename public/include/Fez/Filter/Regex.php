<?php

/**
 * Filter used to nullify values which do not match a pattern
 * @author uqcmaj
 * @since September 2012
 *
 */
class Fez_Filter_Regex implements Zend_Filter_Interface
{
	/*
	 *
	 * Pattern to filter against.
	 */
	private $pattern = null;

	public function filter($value)
	{
		$filtered = $value;

	    if($this->pattern)
	    {
	        $matches = array();
	        preg_match($this->pattern, $value, $matches);
	        $filtered = $matches[0];
	    }

	    return $filtered;
	}

	/**
	 * Set the regex pattern to use
	 * @param string $regex
	 */
	public function setPattern($regex)
	{
		$this->pattern = $regex;
	}
}