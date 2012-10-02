<?php

/**
 * Filter HTML input using HTML Purifier
 * from http://htmlpurifier.org
 * @author uqckorte
 * @since October 2012
 *
 */
class Fez_Filter_RichTextHtmlpurify implements Zend_Filter_Interface
{
	public function filter($value)
	{
		require_once APP_PATH . 'include/htmlpurifier/library/HTMLPurifier.auto.php';

		$config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', 'p,b,strong,u,i');
		$purify = new HTMLPurifier($config);

		if(is_array($value))
		{
			$purified = array();
			foreach($value as $k => $v)
			{
				$purified[$k] = $this->filter($v);
			}
		}
		else
		{
			$purified = $purify->purify($value);
		}

		return $purified;
	}
}