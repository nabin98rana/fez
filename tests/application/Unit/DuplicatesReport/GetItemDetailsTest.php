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
 
class Unit_DuplicatesReport_GetItemDetailsTest extends PHPUnit_Framework_TestCase
{
    protected $fixture;
    protected $reportShort;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;


        $this->reportShort = '<DuplicatesReport>
  <duplicatesReportItem pid="MSS:379" title="date test">
    <duplicateItem pid="MSS:407" probability="0.1"/>
    <duplicateItem pid="MSS:408" probability="0.21"/>
    <duplicateItem pid="MSS:409" probability="0.31"/>
    <duplicateItem pid="MSS:443" probability="0.41"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:400" title="This is a test">
    <duplicateItem pid="MSS:403" probability="0.51"/>
    <duplicateItem pid="MSS:405" probability="0.61" duplicate="true" />
    <duplicateItem pid="MSS:406" probability="0.71"/>
  </duplicatesReportItem>
  <duplicatesReportItem pid="MSS:393" title="Catch the Wave">
    <duplicateItem pid="MSS:410" probability="0.81"  duplicate="true"/>
    <duplicateItem pid="MSS:411" probability="0.91"  duplicate="false"/>
  </duplicatesReportItem>
</DuplicatesReport>';

    }
    
    public function testGetItemDetailsXMLNull()
    {
        $listing = $this->fixture->getItemDetails('MSS:379',null);
        $this->assertEquals(array(), $listing);
    }
  
    public function testGetItemDetailsXMLEmpty()
    {
        $listing = $this->fixture->getItemDetails('MSS:379','');
        $this->assertEquals(array(), $listing);
    }
  
    public function testGetItemDetailsXMLWrongType()
    {
        $listing = $this->fixture->getItemDetails('MSS:379',array($this->reportShort));
        $this->assertEquals(array(), $listing);
    }

    public function testGetItemDetailsPIDNull()
    {
        $listing = $this->fixture->getItemDetails(null, $this->reportShort);
        $this->assertEquals(array(), $listing);
    }
  
    public function testGetItemDetailsPIDEmpty()
    {
        $listing = $this->fixture->getItemDetails('', $this->reportShort);
        $this->assertEquals(array(), $listing);
    }
  
    public function testGetItemDetailsPIDWrongType()
    {
        $listing = $this->fixture->getItemDetails(array('pid' => 'MSS:379'), $this->reportShort);
        $this->assertEquals(array(), $listing['listing']);
    }
  
    public function testGetItemDetailsPids()
    {

//        $this->fixture->setXML_DOM(DOMDocument::loadXML($this->reportShort));
        $listing = $this->fixture->getItemDetails('MSS:379', $this->reportShort);
//        $listing = $this->fixture->getItemDetails('MSS:379');
        $this->assertEquals(array('MSS:407','MSS:408','MSS:409','MSS:443'),
                array_keys(Misc::keyArray($listing['listing'], 'pid')));
    }
  
    public function testGetItemDetailsScores()
    {
        $listing = $this->fixture->getItemDetails('MSS:379', $this->reportShort);
        $this->assertEquals(array('0.1','0.21','0.31','0.41'), 
                array_keys(Misc::keyArray($listing['listing'], 'probability')));
    }


}

?>
