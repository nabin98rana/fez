<?php

class Fez_Filter_BasicstringArray implements Zend_Filter_Interface
{
	public function filter($value)
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