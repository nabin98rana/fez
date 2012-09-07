<?php

/**
 * 
 * Factory to create filter instances
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter
{
	/**
	 * Creates and returns a filter instance.
	 * @param string $filterClassName
	 */
	public static function get($filterClassName)
	{
		$log = FezLog::get();
		$filter = false;
		
		try 
		{
			$filter = new $filterClassName();
		}
		catch(Exception $e)
		{
			$log->err($e->getMessage());
		}
		
		return $filter;
	}
}