<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
// */
//echo "\n\nbefore\n\n"; echo get_include_path();
//require_once('unit_test_setup.php');
//echo "\n\nafter\n\n"; echo get_include_path();


require_once(APP_INC_PATH.'class.workflow_status.php');
 
class Unit_WorkflowStatusTest extends PHPUnit_Framework_TestCase
{
    protected $fixture;
    protected $list;
    protected $found_item;
    protected $pid = 'UQ:5000';
    protected $wft_id = 289;
    protected $xdis_id = 179;
    
    protected function setUp()
    {
        global $auth_isBGP;
        $auth_isBGP = true;
        // the password is not used.  The auth system won't be able to access any AD info
        Auth::LoginAuthenticatedUser('admin', 'blah');    
        $this->fixture = new WorkflowStatus($this->pid, $this->wft_id, $this->xdis_id);
        $this->list = WorkflowStatusStatic::getList();
        $this->found_item = null;
        foreach ($this->list as $item) {
            if ($item['wfses_id'] == $this->fixture->id) {
                $this->found_item = $item;
                break;
            }
        }

    }

    protected function tearDown()
    {
        if (is_object($this->fixture)) {
            $this->fixture->clearSession();
        }
    }

    
    public function testNewWorkflowHasDatabaseId()
    {
        $this->assertTrue($this->fixture->id > 0);
    }

    public function testWorkflowCanBeListed()
    {
        $this->assertTrue(is_array($this->found_item));
    }

    public function testWorkflowListingHasTitle()
    {
        $this->assertTrue(!empty($this->found_item['wfses_listing']));
    }

    public function testWorkflowListingHasDate()
    {
        $this->assertTrue(!empty($this->found_item['wfses_date']));
    }

    public function testWorkflowListingDoesntIncludeBlob()
    {
        // don't want to retrieve the whole object if we're just listing 
        $this->assertTrue(!isset($this->found_item['wfses_object']));
    }
 
    public function testWorkflowCanBeRestoredFromDatabase()
    {
        $obj = WorkflowStatusStatic::getSession($this->fixture->id);
        $this->assertEquals($obj->id, $this->fixture->id);
    }
}
 
?>
