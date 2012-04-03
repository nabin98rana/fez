<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 11/07/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
//require_once('unit_test_setup.php');

require_once(APP_INC_PATH.'class.record_lock.php');
 
class Unit_RecordLock_GetLockTest extends PHPUnit_Framework_TestCase
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

    public function testGetLockNullPid()
    {
        $res = RecordLock::getLock(null, 1);
        $this->assertEquals(-1, $res);
    }

    public function testGetLockNullUser()
    {
        $res = RecordLock::getLock('Test:123', null);
        $this->assertEquals(-1, $res);
    }

    public function testGetLockNullContext()
    {
        $res = RecordLock::getLock('Test:123', 1, '');
        $this->assertEquals(-1, $res);
    }

    public function testGetLockNullExtra()
    {
        $res = RecordLock::getLock('Test:123', 1, RecordLock::CONTEXT_NONE, '');
        $this->assertEquals(-1, $res);
    }

    public function testGetLockZeroUser()
    {
        $res = RecordLock::getLock('Test:123', 0);
        $this->assertEquals(-1, $res);
    }

    public function testGetLockNegativeUser()
    {
        $res = RecordLock::getLock('Test:123', -1);
        $this->assertEquals(-1, $res);
    }


    public function testGetLockNoConflict()
    {
        $res = RecordLock::getLock('Test:123', 1);
        $this->assertEquals(1, $res);
    }

    public function testGetLockTwoLocks()
    {
        $res = RecordLock::getLock('Test:123', 1);
        $res = RecordLock::getLock('Test:321', 1);
        $this->assertEquals(1, $res);
    }

    public function testGetLockConflictWithSelf()
    {
        $res = RecordLock::getLock('Test:123', 1);
        $res = RecordLock::getLock('Test:123', 1);
        $this->assertEquals(1, $res);
    }
    
    public function testGetLockConflict()
    {
        $res = RecordLock::getLock('Test:123', 1);
        $res = RecordLock::getLock('Test:123', 2);
        $this->assertEquals(-1, $res);
    }
    
}
 
?>
