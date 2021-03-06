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
 
class Unit_DuplicatesReport_GenerateXMLTest extends PHPUnit_Framework_TestCase
{
    protected $fixture;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;
        $this->report = array(
            'UQ:4' => array(
                'pid' => 'UQ:4',
                'title' => 'Title UQ:4',
                'list' => array(
                    'UQ:5' => array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    'UQ:7' => array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    'UQ:6' => array('pid' => 'UQ:6', 'probability' => '0.7', 'title' => 'Title UQ:6'),
                )
            ),
            'UQ:3' => array(
                'pid' => 'UQ:3',
                'title' => 'Title UQ:3',
                'list' => array(
                    'UQ:5' => array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    'UQ:7' => array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    'UQ:6' => array('pid' => 'UQ:6', 'probability' => '0.7', 'title' => 'Title UQ:6'),
                )
            ),
            'UQ:2' => array(
                'pid' => 'UQ:2',
                'title' => 'Title UQ:2',
                'list' => array(
                    'UQ:5' => array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    'UQ:7' => array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    'UQ:8' => array('pid' => 'UQ:8', 'probability' => '0.8', 'title' => 'Title UQ:8'),
                )
            ),
        );
        
        
    }
    
    public function testGenerateXMLEmpty()
    {
        $res = $this->fixture->generateXML(array());
        $this->assertEquals("<?xml version=\"1.0\"?>\n<DuplicatesReport/>\n", $res);
    }    
    
    public function testGenerateXMLEmptyString()
    {
        $res = $this->fixture->generateXML('');
        $this->assertEquals('', $res);
    }    
    
    public function testGenerateXMLNull()
    {
        $res = $this->fixture->generateXML(null);
        $this->assertEquals('', $res);
    }    

    public function testGenerateXMLParseOk()
    {
        $res = $this->fixture->generateXML($this->report);
        $dom = DOMDocument::loadXML($res);
        $this->assertTrue(!empty($dom));
    }    
    

}
?>
