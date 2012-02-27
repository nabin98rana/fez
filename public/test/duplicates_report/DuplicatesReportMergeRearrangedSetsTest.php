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
 
class DuplicatesReportMergeRearrangedSetsTest extends PHPUnit_Framework_TestCase
{
    protected $report;
    
    protected function setUp()
    {
        $this->fixture = new DuplicatesReport;
        // similar to the test for rearranging sets except now we have two UQ:4 pids which will becoem the base records
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
                    array('pid' => 'UQ:4', 'probability' => '0.7', 'title' => 'Title UQ:4'),
                )
            ),
        );
        
    }
    
    public function testRearrangeSetsBasePids()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        //print_r($rearranged_report);
        $base_pids = array();
        foreach ($rearranged_report as $item) {
            $base_pids[] = $item['pid'];
        }
        $this->assertEquals(0, count(array_diff($base_pids,array('UQ:4','UQ:3','UQ:4'))));
    }

    public function testMergeRearrangedSetsPids()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $final_groups = $this->fixture->mergeRearrangedSets($rearranged_report);
        $this->assertEquals(array('UQ:4','UQ:3'), array_keys($final_groups));
    }    
    
    public function testMergeRearrangedSetsMergedList()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $final_groups = $this->fixture->mergeRearrangedSets($rearranged_report);
        $this->assertEquals(array('UQ:5','UQ:6','UQ:7','UQ:8'), array_keys($final_groups['UQ:4']['list']));
    }    
    
    public function testMergeRearrangedSetsUnmergedList()
    {
        $rearranged_report = $this->fixture->rearrangeSets($this->report);
        $final_groups = $this->fixture->mergeRearrangedSets($rearranged_report);
        $this->assertEquals(array('UQ:5','UQ:6','UQ:7'), array_keys($final_groups['UQ:3']['list']));
    }    
    
}
 
?>
