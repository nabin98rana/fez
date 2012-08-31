<?php

/**
 * Filter to ensure a valid array of pids
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_PidArray implements Zend_Filter_Interface
{
	public function filter($value)
	{
		$pidFilter = new Fez_Filter_Pid();
		$filtered = false;
		
		if(is_array($value))
		{
			foreach($value as $pid)
			{
				$filteredPid = $pidFilter->filter($pid);
				if($filteredPid)
				{
					$filtered[] = $filteredPid;
				}
			}
		}
		
		return $filtered;
	}
}