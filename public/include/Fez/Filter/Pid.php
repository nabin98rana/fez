<?php

/**
 * Filter out anything that does
 * not constitute a pid
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
		preg_match($pidFormat, $value, $filtered);
		return $filtered[0];
	}
}