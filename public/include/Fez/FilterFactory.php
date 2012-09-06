<?php

/*
 * Not currently used. Not sure if this class will be needed anymore but
 * may be useful for grouping filters if we are to have only
 * one filter per element (ie no chaining). Still useful for quick 
 * filtration where only one filter for one piece of data is required. 
 * Fez_Filter::get('Fez_Filter_Blah')->filter($data);
 */

/**
 * 
 * Factory to create filter instances
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_FilterFactory
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