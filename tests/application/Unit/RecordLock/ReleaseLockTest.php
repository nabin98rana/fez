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
 
class Unit_RecordLock_ReleaseLockTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        RecordLock::getLock('Test:123',1);
        RecordLock::getLock('Test:321',1);
    }

    public function tearDown()
    {
        RecordLock::releaseLock('Test:123');
        RecordLock::releaseLock('Test:321');
    }

    public function testReleaseLock1()
    {
        $res = RecordLock::releaseLock('Test:123');
        $this->assertEquals(1, $res);
    }
    
    public function testReleaseLock2()
    {
        RecordLock::releaseLock('Test:123');
        $res = RecordLock::releaseLock('Test:321');
        $this->assertEquals(1, $res);
    }     

    public function testReleaseLockTwice()
    {
        RecordLock::releaseLock('Test:123');
        $res = RecordLock::releaseLock('Test:123');
        $this->assertEquals(1, $res);
    }     
         
}
 
?>
