<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 31/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
require_once('unit_test_setup.php');

require_once(APP_INC_PATH.'class.duplicates_report.php');
 
class DuplicatesReportSwapBaseXMLTest extends PHPUnit_Framework_TestCase
{
    protected $fixture;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;
        $this->report = '<?xml version="1.0"?>
<DuplicatesReport>
  <duplicatesReportItem pid="MSS:379" title="date test">
    <duplicateItem pid="MSS:407" probability="0.1"/>
    <duplicateItem pid="MSS:408" probability="0.21"/>
    <duplicateItem pid="MSS:409" probability="0.31"/>
    <duplicateItem pid="MSS:443" probability="0.41"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:400" title="This is a test">
    <duplicateItem pid="MSS:403" probability="0.51"/>
    <duplicateItem pid="MSS:405" probability="0.61"/>
    <duplicateItem pid="MSS:406" probability="0.71"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:393" title="Catch the Wave">
    <duplicateItem pid="MSS:410" probability="0.81"/>
    <duplicateItem pid="MSS:411" probability="0.91"/>
  </duplicatesReportItem>
</DuplicatesReport>';
        
        
    }
    
    public function testDuplicatesReportSwapBaseXMLEmpty1()
    {
        $res = $this->fixture->swapBaseXML(null,null);
        $this->assertEquals(-1,$res);
    }
    
    public function testDuplicatesReportSwapBaseXMLEmpty2()
    {
        $res = $this->fixture->swapBaseXML('MSS:400',null);
        $this->assertEquals(-1,$res);
    }

    public function testDuplicatesReportSwapBaseXMLReturn()
    {
        $this->fixture->setXML_DOM(DOMDocument::loadXML($this->report));
        $res = $this->fixture->swapBaseXML('MSS:400','MSS:405');
        $this->assertEquals(1,$res);
    }
    
    public function testDuplicatesReportSwapBaseXML()
    {
        $this->fixture->setXML_DOM(DOMDocument::loadXML($this->report));
        $res = $this->fixture->swapBaseXML('MSS:400','MSS:405');
        $xml = $this->fixture->xml_dom->saveXML();
        $expect = '<?xml version="1.0"?>
<DuplicatesReport>
  <duplicatesReportItem pid="MSS:379" title="date test">
    <duplicateItem pid="MSS:407" probability="0.1"/>
    <duplicateItem pid="MSS:408" probability="0.21"/>
    <duplicateItem pid="MSS:409" probability="0.31"/>
    <duplicateItem pid="MSS:443" probability="0.41"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:405" title="This is a test">
    <duplicateItem pid="MSS:403" probability="0.51"/>
    <duplicateItem pid="MSS:400" probability="0.61"/>
    <duplicateItem pid="MSS:406" probability="0.71"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:393" title="Catch the Wave">
    <duplicateItem pid="MSS:410" probability="0.81"/>
    <duplicateItem pid="MSS:411" probability="0.91"/>
  </duplicatesReportItem>
</DuplicatesReport>';
        $this->assertEquals(TestCommon::treatXML($expect), TestCommon::treatXML($xml));
    }
    
    public function testDuplicatesReportSwapBaseXMLAlreadySwapped()
    {
        $this->fixture->setXML_DOM(DOMDocument::loadXML($this->report));
        $res = $this->fixture->swapBaseXML('MSS:405','MSS:400');
        $this->assertEquals(-1,$res);
    }

}
?>
