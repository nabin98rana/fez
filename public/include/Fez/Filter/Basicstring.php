<?php

/**
 * Filter to allow only a restricted
 * subset of string characters.
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_Basicstring implements Zend_Filter_Interface
{
	public function filter($value)
	{
		$goodChars = "/[^a-zA-Z0-9\,\-\s']/";
		$filtered = preg_replace($goodChars, '', $value);
		return $filtered;
	}
}