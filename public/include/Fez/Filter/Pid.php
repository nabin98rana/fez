<?php

/**
 * Filter out anything that does
 * not constitute a pid in provided
 * array or string
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_Pid implements Zend_Filter_Interface
{
	public function filter($value)
	{
		$pidFormat = "/^[a-zA-Z]{2,60}\:[0-9]+$/";
		$filtered = array();
		
		if(is_array($value))
		{
			foreach($value as $pid)
			{
				$tmpFiltered = array();
				$filteredPid = preg_match($pidFormat, $pid, $tmpFiltered);
				if($filteredPid)
				{
					$filtered[] = $tmpFiltered[0];
				}
			}
		}
		else 
		{
			preg_match($pidFormat, $value, $filtered);
			$filtered = $filtered[0];
		}
		
		return $filtered;
	}
}