<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 31/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
//require_once('unit_test_setup.php');

require_once(APP_INC_PATH.'class.duplicates_report.php');
 
class Unit_DuplicatesReport_MiscTest extends PHPUnit_Framework_TestCase
{
    protected $fixture;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;
        
    }
    
    public function testShortWordsFilterLong()
    {
        $this->assertTrue($this->fixture->shortWordsFilter('longword'));
    }
    
    public function testShortWordsFilterShort()
    {
        $this->assertTrue(!$this->fixture->shortWordsFilter('the'));
    }
    
    public function testTokeniseBasic()
    {
        $tokens = $this->fixture->tokenise(array('the longword','a second string'));
        $this->assertEquals(array('longword','second','string'),$tokens);
    }

    public function testTokeniseEmpty()
    {
        $this->assertEquals(array(),$this->fixture->tokenise(array()));
    }
    
    public function testTokeniseOneStringShort()
    {
        $tokens = $this->fixture->tokenise(array('a'));
        $this->assertEquals(array(), $tokens);
    }
    
    public function testCalcOverlap()
    {
        $overlap = $this->fixture->calcOverlap(array('a', 'b', 'c'), array('b','c','d'));
        $this->assertEquals(4 / 6, $overlap, '',0.1);
    }

    public function testSimilarPidsQueryNonExists()
    {
        $res = $this->fixture->similarTitlesQuery('UQ:1','asdfasd5484893093848fdasd');
        $this->assertEquals(array(), $res);
    }
    
    public function testSimilarPidsQueryFindsPID()
    {
        $res = $this->fixture->similarTitlesQuery('UQ:1','wave');
        $this->assertTrue(strpos($res[0]['pid'],':') > 0);
    }
    
    public function testSimilarPidsQueryFindsRelevance()
    {
        $res = $this->fixture->similarTitlesQuery('UQ:1','wave');
        $this->assertTrue(is_numeric($res[0]['relevance']) && $res[0]['relevance'] > 0);
    }
    
    
    
}
?>
