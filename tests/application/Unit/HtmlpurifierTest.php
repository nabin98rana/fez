<?php

require_once '../../../public/config.inc.php';

class HtmlpurifierTest extends PHPUnit_Framework_TestCase
{
	public function testPurify()
	{
		$testdat = <<<DAT
<script type="text/javascript">alert(document.cookies);</script>
<a title="Browse by Author Name for Baggio, Rodolfo" href="/list/author/Baggio%2C+Rodolfo/">Baggio, Rodolfo</a> 
(<a title="Browse by Year 0000" href="/list/year/0000/">0000</a>)<span>I am a span</span>
<a title="Click to view Preprint: SSSSSSSSSSSSSSSSSSSSSS" href="/view/UQ:10000">SSSSSSSSSSSSSSSSSSSSSS</a>.
DAT;
		$purifier = new Fez_Filter_Htmlpurify();
		$purified = $purifier->filter($testdat);
		$this->assertFalse(strstr($purified,'<script'));
	}
}