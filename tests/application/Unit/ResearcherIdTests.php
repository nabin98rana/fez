<?php

//require_once APP_PATH . 'init.php';
require_once APP_INC_PATH . 'class.researcherid.php';

class Unit_ResearcherIdTests extends PHPUnit_Framework_TestCase
{
  protected $_ticketNo;
  protected $_isiLoc;
  protected $_rid;
  
  public function __construct()
  {
  }
  
  public function setUp()
  {
    $this->_ticketNo = '3AhPfJGaDFjmh97g6PA-689';
    $this->_isiLoc   = '000073882000025';
    $this->_rid      = 'A-3541-2009';
    
    // Remove any existing matching jobs
    ResearcherID::removeJob($this->_ticketNo);
    // Remove an existing record matching this ISI loc
    $pid = Record::getPIDByIsiLoc($this->_isiLoc);
    if ($pid) {
      Record::removeIndexRecord($pid);
    }
  }

  public function tearDown()
  {    
  }

  public function testDownloadRequest()
  {
    $return = ResearcherID::downloadRequest(
        array($this->_rid), 'researcherIDs', 'researcherID'
    );
    $this->assertEquals(TRUE, $return);
  }
  
  public function testGetDownloadStatus()
  {
//      echo RID_DL_SERVICE_URL."\n";
//      echo RID_UL_SERVICE_URL."\n";
    $return = ResearcherID::checkJobStatus($this->_ticketNo);
////    $pid = Record::getPIDByIsiLoc($this->_isiLoc);
////    $return = (! $pid) ? false : $return;
    $this->assertEquals(TRUE, $return);
  }
}
