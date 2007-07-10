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
 
class DuplicatesReportSetDuplicateXMLTest extends PHPUnit_Framework_TestCase
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
    
    public function testSetDuplicateXMLEmpty1()
    {
        $res = $this->fixture->setDuplicateXML(null, null, null, null);
        $this->assertNull($res);
    }    

    public function testSetDuplicateXMLEmpty2()
    {
        $res = $this->fixture->setDuplicateXML('MSS:400', null, null, null);
        $this->assertNull($res);
    }    

    public function testSetDuplicateXMLEmpty3()
    {
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:405', null, null);
        $this->assertNull($res);
    }    

    public function testSetDuplicateXMLEmpty4()
    {
        // If $is_duplicate is null, then default to false
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:405', $this->report, null);
        // remove whitespace from XML since it seems to confuse things
        $expected_xml = TestCommon::treatXML(
'<?xml version="1.0"?>
<DuplicatesReport>
  <duplicatesReportItem pid="MSS:379" title="date test">
    <duplicateItem pid="MSS:407" probability="0.1"/>
    <duplicateItem pid="MSS:408" probability="0.21"/>
    <duplicateItem pid="MSS:409" probability="0.31"/>
    <duplicateItem pid="MSS:443" probability="0.41"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:400" title="This is a test">
    <duplicateItem pid="MSS:403" probability="0.51"/>
    <duplicateItem pid="MSS:405" probability="0.61" duplicate="false"/>
    <duplicateItem pid="MSS:406" probability="0.71"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:393" title="Catch the Wave">
    <duplicateItem pid="MSS:410" probability="0.81"/>
    <duplicateItem pid="MSS:411" probability="0.91"/>
  </duplicatesReportItem>
</DuplicatesReport>');
        $this->assertEquals($expected_xml, TestCommon::treatXML($res));
    }    

    public function testSetDuplicateXMLDefault4()
    {
        // If $is_duplicate is not specified, then default to true
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:405', $this->report);
        $expected_xml = TestCommon::treatXML(
'<?xml version="1.0"?>
<DuplicatesReport>
  <duplicatesReportItem pid="MSS:379" title="date test">
    <duplicateItem pid="MSS:407" probability="0.1"/>
    <duplicateItem pid="MSS:408" probability="0.21"/>
    <duplicateItem pid="MSS:409" probability="0.31"/>
    <duplicateItem pid="MSS:443" probability="0.41"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:400" title="This is a test">
    <duplicateItem pid="MSS:403" probability="0.51"/>
    <duplicateItem pid="MSS:405" probability="0.61" duplicate="true"/>
    <duplicateItem pid="MSS:406" probability="0.71"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:393" title="Catch the Wave">
    <duplicateItem pid="MSS:410" probability="0.81"/>
    <duplicateItem pid="MSS:411" probability="0.91"/>
  </duplicatesReportItem>
</DuplicatesReport>'); 
        $this->assertEquals($expected_xml, TestCommon::treatXML($res));
    }
    
    /**
     * What if the base pid isn't in the XML?  Return null
     */    
    public function testSetDuplicateXMLBaseNotExist()
    {
        $res = $this->fixture->setDuplicateXML('MSS:666', 'MSS:405', $this->report);
        $this->assertNull($res);
    }
    
    /**
     * What if the base pid exists as a dup of a different base pid but not a base pid?
     * Like if we swap the base and dup pids?
     */
    public function testSetDuplicateXMLBaseDupSwap()
    {
        $res = $this->fixture->setDuplicateXML('MSS:405', 'MSS:400', $this->report);
        $this->assertNull($res);
    }
    
    /**
     * What if Base pid is ok but the dup is not there?
     */
    public function testSetDuplicateXMLDupNotExists()
    {
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:666', $this->report);
        $this->assertNull($res);
    }
    
    /**
     * What if the dup exists but is on different base pid?
     */
    public function testSetDuplicateXMLDupNotElsewhere()
    {
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:407', $this->report);
        $this->assertNull($res);
    }
    
    /*    
    public function testSetDuplicateXMLDodgyXML()
    {
        $res = $this->fixture->setDuplicateXML('MSS:400', 'MSS:405', substr($this->report,0,100));
        $this->assertNull($res);
    }
    */
    

}
?>
