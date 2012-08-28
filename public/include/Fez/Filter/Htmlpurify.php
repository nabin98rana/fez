<?php

/**
 * Filter HTML input using HTML Purifier
 * from http://htmlpurifier.org
 * @author uqcmaj
 * @since August 2012
 *
 */
class Fez_Filter_Htmlpurify implements Zend_Filter_Interface
{
	public function filter($value)
	{
		require_once APP_PATH . 'include/htmlpurifier/library/HTMLPurifier.auto.php';
		
		$config = HTMLPurifier_Config::createDefault();
		$purify = new HTMLPurifier($config);
		$purified = $purify->purify($value);
		
		return $purified;
	}
}