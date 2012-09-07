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
		$filtered = false;
		
		if(is_array($value))
		{
			foreach($value as $element)
			{
				$filteredElement = preg_replace($goodChars, '', $element);
				if($filteredElement)
				{
					$filtered[] = $filteredElement;
				}
			}
		}
		else 
		{
			$filtered = preg_replace($goodChars, '', $value);
		}
		
		return $filtered;
	}
	
	public function xfilter($value)
	{
		$basicStringFilter = new Fez_Filter_Basicstring();
		$filtered = false;
	
		if(is_array($value))
		{
			foreach($value as $element)
			{
				$filteredElement = $basicStringFilter->filter($element);
				if($filteredElement)
				{
					$filtered[] = $filteredElement;
				}
			}
		}
	
		return $filtered;
	}
}