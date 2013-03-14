<?php

define("RID_DL_SERVICE_REQUEST_XSD", dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."public/include/xsd/download-request.xsd");
define("RID_DL_SERVICE_RESPONSE_XSD", dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."public/include/xsd/download-response.xsd");
define("RID_UL_SERVICE_PROFILES_XSD", dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."public/include/xsd/Researcher-Bulk-Profiles-schema.xsd");
define("RID_UL_SERVICE_PUBLICATIONS_XSD", dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."include/xsd/Researcher-Bulk-Publications-schema.xsd");
require_once 'config.inc.php';
require_once APP_INC_PATH . 'class.researcherid.php';

class Unit_ResearcherIdTests extends PHPUnit_Framework_TestCase
{
  protected $_rid;
  protected $_ticketNo;
  
  public function __construct()
  {
  }
  
  public function setUp()
  {
    $this->_rid      = 'A-3541-2009';
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
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT rij_ticketno FROM " . APP_TABLE_PREFIX . "rid_jobs WHERE rij_status = 'NEW' AND rij_downloadrequest LIKE ".$db->quote('%'.$this->_rid.'%');

    $res = $db->fetchOne($stmt);
    $this->assertEquals(TRUE, !empty($res));
    $return = ResearcherID::checkJobStatus($res);
    $this->assertEquals(TRUE, $return);
  }
}
