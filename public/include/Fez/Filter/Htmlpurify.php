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
		//$config->set('Core.Encoding', 'ISO-8859-1');
		$config->set('Core.EscapeNonASCIICharacters', 1);
		$purify = new HTMLPurifier($config);
		$purified = $purify->purify($value);
		
		return $purified;
	}
}