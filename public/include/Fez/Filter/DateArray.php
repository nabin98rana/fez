<?php

/**
 * Filter Fez date array elements and 
 * throw out any disallowed keys
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_DateArray implements Zend_Filter_Interface
{
	public function filter($value)
	{
		$digits = new Zend_Filter_Digits();
		$possibleKeys = array('Year', 'Month', 'Day');
		$filtered = false;
		
		foreach($possibleKeys as $possibleKey)
		{
			if(is_array($value) && isset($value[$possibleKey]))
			{
				$filtered[$possibleKey] = $digits->filter($value[$possibleKey]);
			}
		}
		
		return $filtered;
	}
}