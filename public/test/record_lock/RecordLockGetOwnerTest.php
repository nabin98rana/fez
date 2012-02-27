<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 11/07/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
require_once('unit_test_setup.php');

require_once(APP_INC_PATH.'class.record_lock.php');
 
class RecordLockGetOwnerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        RecordLock::releaseLock('Test:123');
        RecordLock::releaseLock('Test:321');
    }

    public function tearDown()
    {
        RecordLock::releaseLock('Test:123');
        RecordLock::releaseLock('Test:321');
    }
    
    public function testGetOwnerInvalidPid()
    {
        $res = RecordLock::getOwner(null);
        $this->assertEquals(-1, $res);
    }

    public function testGetOwnerNoEntry()
    {
        $res = RecordLock::getOwner('Test:123');
        $this->assertEquals(0, $res);
    }

    public function testGetOwnerLocked()
    {
        $res = RecordLock::getLock('Test:123',2);
        $res = RecordLock::getOwner('Test:123');
        $this->assertEquals(2, $res);
    }

    public function testGetOwnerNonExpiredWorkflow()
    {
        $wft = WorkflowTrigger::getList(-1);
        $wfs = new WorkflowStatus(null,$wft[0]['wft_id']);
        $res = RecordLock::getLock('Test:123',2, RecordLock::CONTEXT_WORKFLOW, $wfs->id);
        $res = RecordLock::getOwner('Test:123');
        $wfs->clearSession();
        $this->assertEquals(2, $res);
    }

    public function testGetOwnerExpiredWorkflow()
    {
        RecordLock::getLock('Test:123',2, RecordLock::CONTEXT_WORKFLOW, 1);
        $res = RecordLock::getOwner('Test:123');
        $this->assertEquals(0, $res);
    }


    
}
 
?>
