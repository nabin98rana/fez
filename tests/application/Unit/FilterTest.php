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
        
        $goodString = "";
        $filtered = $bstringFilter->filter($goodString);
        $this->assertEquals($goodString, $filtered);
        
        $hasBadChars = "t?<>ry, som//3@th!!n*& F(#%%)\-nny";
        $expected = "try, som3thn F-nny";
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
    
    public function testDatearray()
    {
        $datearrayFilter = new Fez_Filter_DateArray();
        
        $goodDate = array('Month' => '03', 'Year' => '2009', 'Day' => '26');
        $filtered = $datearrayFilter->filter($goodDate);
        $this->assertEquals($goodDate, $filtered);
        
        $goodDate = array('Month' => '03', 'Year' => '2009');
        $filtered = $datearrayFilter->filter($goodDate);
        $this->assertEquals($goodDate, $filtered);
        
        $goodDate = array('Month' => '3', 'Year' => '2009', 'Day' => '5');
        $filtered = $datearrayFilter->filter($goodDate);
        $this->assertEquals($goodDate, $filtered);
        
        $badDate = array('Month' => 'three', 'Year' => '2009blah', 'Day' => '!5');
        $filtered = $datearrayFilter->filter($badDate);
        $this->assertEquals(array('Month' => '', 'Year' => '2009', 'Day' => '5'), $filtered);
        
        $badDate = array('Attack' => 'three', 'Year' => '2009blah', 'Day' => '!5');
        $filtered = $datearrayFilter->filter($badDate);
        $this->assertEquals(array('Year' => '2009', 'Day' => '5'), $filtered);
    }
    
    public function testPidarray()
    {
        $pidFilter = new Fez_Filter_Pid();
    
        $goodPids = array('XYZ:8765', 'CN:223', 'FJFUTAASEDQWE:2');
        $filtered = false;
        $filtered = $pidFilter->filter($goodPids);
        $this->assertEquals($goodPids, $filtered);
    
        $badPids = array('7654', '', 'vfg', ':', 'A:334', 'JGFH:', '23:234', 'GF:GFRE', 'TR:765A543', '#$');
        $filtered = array();
        $filtered = $pidFilter->filter($badPids);
        $this->assertEquals(array(), $filtered);
    }
    
    public function testStringarray()
    {
    	$stringArrayFilter = new Fez_Filter_Basicstring();
    	
    	$goodStrings = array('Kang, MK', 'Zhang, MX', 'Liu, F', 'Funny-Name 8876', "John O'Reilly");
    	$filtered = $stringArrayFilter->filter($goodStrings);
    	$this->assertEquals($goodStrings, $filtered);
    	
    	$badStrings = array('!7', 'an!m$l Food', '$%*', 'not s@, b4d');
    	$filtered = $stringArrayFilter->filter($badStrings);
    	$expected = array('7', 'anml Food', 'not s, b4d');
    	$this->assertEquals($expected, $filtered);
    }
}