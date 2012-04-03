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
 
class Unit_RecordLock_GetListTest extends PHPUnit_Framework_TestCase
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
    
    public function testGetListEmpty()
    {
        $res = RecordLock::getList(1);
        $this->assertEquals(array(), $res);
    }

    public function testGetListOneItem()
    {
        RecordLock::getLock('Test:123', 1);
        $res = RecordLock::getList(1);
        // fix the rl_id as it is an autoincrement
        $res[0]['rl_id'] = 12345;
        $expect = array(array('rl_id' => 12345, 
                                'rl_pid' => 'Test:123', 
                                'rl_usr_id' => 1, 
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0));
        $this->assertEquals($expect, $res);
    }

    public function testGetListTwoItems()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:321', 1);
        $res = RecordLock::getList(1);
        // fix the rl_id as it is an autoincrement
        $res[0]['rl_id'] = 12345;
        $res[1]['rl_id'] = 12346;
        $expect = array(array('rl_id' => 12345, 
                                'rl_pid' => 'Test:123', 
                                'rl_usr_id' => 1,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0),
                        array('rl_id' => 12346, 
                                'rl_pid' => 'Test:321', 
                                'rl_usr_id' => 1,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0));
        $this->assertEquals($expect, $res);
    }

    /**
     * Make sure the getList only gets the locks for the specified user if there are other locks for other users.
     */
    public function testGetListDifferentOwners()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:321', 2);
        $res = RecordLock::getList(1);
        // fix the rl_id as it is an autoincrement
        $res[0]['rl_id'] = 12345;
        $expect = array(array('rl_id' => 12345, 
                                'rl_pid' => 'Test:123', 
                                'rl_usr_id' => 1,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0));
        $this->assertEquals($expect, $res);
    }

    public function testGetListDifferentOwners2()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:321', 2);
        $res = RecordLock::getList(2);
        // fix the rl_id as it is an autoincrement
        $res[0]['rl_id'] = 12345;
        $expect = array(array('rl_id' => 12345, 
                                'rl_pid' => 'Test:321', 
                                'rl_usr_id' => 2,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0));
        $this->assertEquals($expect, $res);
    }
    
    
    /**
     * Check that getting a lock twice doesn't create a dup
     */
    public function testGetListCheckDups()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:123', 2);
        $res = RecordLock::getList(1);
        // fix the rl_id as it is an autoincrement
        $res[0]['rl_id'] = 12345;
        $expect = array(array('rl_id' => 12345, 
                                'rl_pid' => 'Test:123', 
                                'rl_usr_id' => 1,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0));
        $this->assertEquals($expect, $res);
        
    }
    
    public function testGetListAll()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:321', 2);
        $res = RecordLock::getList();
        // fix the rl_id as it is an autoincrement
        $found = array();
        if (!empty($res)) {
            foreach ($res as $key => $item) {
                unset($res[$key]['rl_id']);    
                if ($item['rl_pid'] == 'Test:123') {
                    $found = $res[$key];
                }                    
            }
        }
        $expect = array('rl_pid' => 'Test:123', 
                                'rl_usr_id' => 1,
                                'rl_context_type' => RecordLock::CONTEXT_NONE,
                                'rl_context_value' => 0);
        $this->assertEquals($expect, $found);
    }
    
    public function testGetListAll2()
    {
        RecordLock::getLock('Test:123', 1);
        RecordLock::getLock('Test:321', 2);
        $res = RecordLock::getList();
        // fix the rl_id as it is an autoincrement
        $found = array();
        if (!empty($res)) {
            foreach ($res as $key => $item) {
                unset($res[$key]['rl_id']);                        
                if ($item['rl_pid'] == 'Test:321') {
                    $found = $res[$key];
                }                    
            }
        }
        $expect = array('rl_pid' => 'Test:321', 
                            'rl_usr_id' => 2,
                            'rl_context_type' => RecordLock::CONTEXT_NONE,
                            'rl_context_value' => 0);
        $this->assertEquals($expect, $found);
    }
    
}
 
?>
