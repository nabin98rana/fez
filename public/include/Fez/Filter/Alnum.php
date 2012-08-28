<?php

/**
 * Wrapper for Zend_Filter_Alnum
 * with required parameters enabled.
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_Alnum implements Zend_Filter_Interface
{
	public function filter($value)
	{
		$ZAlnum = new Zend_Filter_Alnum(array('allowwhitespace' => true));
		$filtered = $ZAlnum->filter($value);
		return $filtered;
	}
}