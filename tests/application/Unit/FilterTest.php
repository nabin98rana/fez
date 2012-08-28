<?php

require_once '../../../public/config.inc.php';

class FilterTest extends PHPUnit_Framework_TestCase
{
	public function testBstring()
	{
		$bstringFilter = new Fez_Filter_Basicstring();
		
		$goodString = "John O'Reilly";
		$filtered = $bstringFilter->filter($goodString);
		$this->assertEquals($goodString, $filtered);
		
		$goodString = "Funny-Name 8876";
		$filtered = $bstringFilter->filter($goodString);
		$this->assertEquals($goodString, $filtered);
		
		$hasBadChars = "t?<>ry, som//3@th!!n*& F(#%%)\-nny";
		$expected = "try som3thn F-nny";
		$filtered = $bstringFilter->filter($hasBadChars);
		$this->assertEquals($expected, $filtered);
	}
	
	public function testPid()
	{
		$pidFilter = new Fez_Filter_Pid();
		
		$goodPids = array('XYZ:8765', 'CN:223', 'FJFUTAASEDQWE:2');
		$filtered = array();
		foreach($goodPids as $goodPid)
		{
			$filtered[] = $pidFilter->filter($goodPid);
		}
		$this->assertEquals($goodPids, $filtered);
		
		$badPids = array('7654', '', 'vfg', ':', 'A:334', 'JGFH:', '23:234', 'GF:GFRE', 'TR:765A543', '#$');
		$filtered = array();
		foreach($badPids as $badPid)
		{
			$filtered[] = $pidFilter->filter($badPid);
		}
		$this->assertEquals(array('','','','','','','','','',''), $filtered);
	}
}