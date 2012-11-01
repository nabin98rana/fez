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
		$hasAnchor = (strstr($purified, '<a')) ? true : false;
		$hasSpan = (strstr($purified, '<span>')) ? true : false;
		$this->assertTrue($hasAnchor);
		$this->assertTrue($hasSpan);
	}
	
	/**
	 * Purifier is run over all fields. 
	 * Primitives like int and boolean should be unaffected.
	 */
	public function testPurifyOddities()
	{
		$purifier = new Fez_Filter_Htmlpurify();
		$oddities = array(1, 'UQ:34556', false, null, array('Month' => '03', 'Year' => '2009', 'Day' => '26'));
		
		$filtered = array();
		foreach($oddities as $oddity)
		{
			$filtered[] = $purifier->filter($oddity);
		}
		
		$this->assertEquals($oddities, $filtered);
	}
	
	public function testPurifierTitle()
	{
		/*$purifier = new Fez_Filter_Basicstring();
		$db = DB_API::get();
		$sql = "select rek_title from fez_record_search_key where rek_pid = 'UQ:17239'";
		$stm = $db->query($sql);
		$res = $stm->fetchColumn();
		//var_dump($res);
		var_dump($purifier->filter($res));
		//print mb_detect_encoding($res);
		$purifier = new Fez_Filter_Htmlpurify();
		var_dump($purifier->filter("A Golden Age for Journalism? Collaborative process modelling - â€¨Tool analysis and design implications"));*/
		
		$regex = <<<END
			/
			  (
			    (?: [\x00-\x7F]               # single-byte sequences   0xxxxxxx
			    |   [\xC0-\xDF][\x80-\xBF]    # double-byte sequences   110xxxxx 10xxxxxx
			    |   [\xE0-\xEF][\x80-\xBF]{2} # triple-byte sequences   1110xxxx 10xxxxxx * 2
			    |   [\xF0-\xF7][\x80-\xBF]{3} # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
			    )+                            # ...one or more times
			  )
			| ( [\x80-\xBF] )                 # invalid byte in range 10000000 - 10111111
			| ( [\xC0-\xFF] )                 # invalid byte in range 11000000 - 11111111
			/x
END;
	preg_replace_callback($regex, "utf8replacer", $text);
	}
	
	
	public function utf8replacer($captures) {
		if ($captures[1] != "") {
			// Valid byte sequence. Return unmodified.
			return $captures[1];
		}
		elseif ($captures[2] != "") {
			// Invalid byte of the form 10xxxxxx.
			// Encode as 11000010 10xxxxxx.
			return "\xC2".$captures[2];
		}
		else {
			// Invalid byte of the form 11xxxxxx.
			// Encode as 11000011 10xxxxxx.
			return "\xC3".chr(ord($captures[3])-64);
		}
	}
	
	
}