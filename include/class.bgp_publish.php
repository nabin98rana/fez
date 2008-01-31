<?php

include_once(APP_INC_PATH.'class.background_process.php');

class BackgroundProcess_Publish extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_publish.php';
        $this->name = 'Bulk Publish';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));


        if (!empty($options)) {
            $this->setStatus("Running search");
            $pids = $this->getPidsFromSearchBGP($options);
            $this->setStatus("Found ".count($pids). " records");
            $record_count = count($pids);
        }
        if (!empty($pids) && is_array($pids)) { 
            $record_counter = 0;
        	foreach ($pids as $pid) {
					$record_counter++;

					// display progress and message
                    $bgp_details = $this->getDetails();
                    $utc_date = Date_API::getSimpleDateUTC();
                    $time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
                    $date_new = new Date(strtotime($bgp_details['bgp_started']));
                    $time_per_object = round(($time_per_object / $record_counter), 2);
                    //$expected_finish = Date_API::getFormattedDate($date_new->getTime());
                    $date_new->addSeconds($time_per_object*$record_count);
                    $tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
    				$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
                    $expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);
					$this->setProgress(intval(100*$record_counter/$record_count));
                    $this->setStatus("Publishing:  '".$pid."'  (".$record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");

				// publish the pid
            	$rec = new RecordGeneral($pid);
				$sta_id = Status::getID("Published");
				$rec->setStatusId($sta_id);
				History::addHistory($pid, null, '', '', true, 'Published');
            }
        }
        $this->setState(2);
    }

	function getPidsFromSearchBGP($options)
    {

		$list = Record::getListing($options, array(9,10), 0, 'ALL');
		$pids = array_keys(Misc::keyArray($list['list'],'rek_pid'));
		return $pids;
    }
}



?>