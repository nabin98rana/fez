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
	 * Creates a filter instance and
	 * filters supplied input. Returns
	 * filtered input.
	 * @param string $filterClassName
	 */
	public static function get($filterClassName, $input)
	{
		$log = FezLog::get();
		$filtered = false;
		
		try 
		{
			$filter = new $filterClassName();
			$filtered = $filter->filter($input);
		}
		catch(Exception $e)
		{
			$log->err($e->getMessage());
		}
		
		return $filtered;
	}
}