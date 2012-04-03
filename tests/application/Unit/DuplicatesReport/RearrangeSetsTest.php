<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 31/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
// require_once('unit_test_setup.php');

require_once(APP_INC_PATH.'class.duplicates_report.php');
 
class Unit_DuplicatesReport_RearrangeSetsTest extends PHPUnit_Framework_TestCase
{
    protected $report;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;
        $this->report = array(
            'UQ:6' => array(
                'pid' => 'UQ:6',
                'title' => 'Title UQ:6',
                'list' => array(
                    array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    array('pid' => 'UQ:4', 'probability' => '0.7', 'title' => 'Title UQ:4'),
                )
            ),
            'UQ:3' => array(
                'pid' => 'UQ:3',
                'title' => 'Title UQ:3',
                'list' => array(
                    array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    array('pid' => 'UQ:6', 'probability' => '0.7', 'title' => 'Title UQ:6'),
                )
            ),
            'UQ:8' => array(
                'pid' => 'UQ:8',
                'title' => 'Title UQ:8',
                'list' => array(
                    array('pid' => 'UQ:5', 'probability' => '0.7', 'title' => 'Title UQ:5'),
                    array('pid' => 'UQ:7', 'probability' => '0.7', 'title' => 'Title UQ:7'),
                    array('pid' => 'UQ:2', 'probability' => '0.7', 'title' => 'Title UQ:2'),
                )
            ),
        );
        
    }

    public function testRearrangeSetsEmpty()
    {
        $rearranged_report = $this->fixture->rearrangeSets(array());
        $this->assertEquals(array(), $rearranged_report);
    }
    
    
    public function testRearrangeSetsBasePids()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        //print_r($rearranged_report);
        $base_pids = array_keys(Misc::keyArray($rearranged_report,'pid'));
        $this->assertEquals(array('UQ:4','UQ:3','UQ:2'), $base_pids);
    }
    
    public function testRearrangeSetsBaseTitles()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $titles = array_keys(Misc::keyArray($rearranged_report,'title'));
        $this->assertEquals(array('Title UQ:4','Title UQ:3','Title UQ:2'), $titles);
    }
    
    public function testRearrangeSetsDupListPids()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $list_pids = array();
        foreach ($rearranged_report as $group) {
            if ($group['pid'] == 'UQ:4') {
                $list_pids = array_values($group['list']);
                break;
            }
        }

        $expected =  Array (
                         0 => Array (
                             'pid' => 'UQ:5',
                             'probability' => '0.7',
                             'title' => 'Title UQ:5'
                         ),
                         1 => Array (
                            'pid' => 'UQ:6',
                            'title' => 'Title UQ:6'
                         ),
                         2 => Array (
                            'pid' => 'UQ:7',
                            'probability' => '0.7',
                            'title' => 'Title UQ:7',
                         )
                     );
        $this->assertEquals($expected, $list_pids);
    }
    
    public function testRearrangeSetsDupListPids2()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $list_pids = array();
        foreach ($rearranged_report as $group) {
            if ($group['pid'] == 'UQ:4') {
                $list_pids = array_keys(Misc::keyArray($group['list'],'pid'));
                break;
            }
        }
        $this->assertEquals(array('UQ:5','UQ:6','UQ:7'), $list_pids);
    }
    
    public function testRearrangeSetsDupListTitles()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $list_pids = array();
        foreach ($rearranged_report as $group) {
            if ($group['pid'] == 'UQ:4') {
                $list_pids = array_keys(Misc::keyArray($group['list'],'title'));
                break;
            }
        }
        $this->assertEquals(array('Title UQ:5','Title UQ:6','Title UQ:7'), $list_pids);
    }
    
}
 
?>
