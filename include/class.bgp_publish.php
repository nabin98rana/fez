<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Lachlun Kuhn <l.kuhn@library.uq.edu.au>                     |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

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
			$sta_id = Status::getID("Published");
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
            	$rec = new RecordObject($pid);
				if ($rec->canApprove()) {
					$rec->setStatusId($sta_id);
					History::addHistory($pid, null, '', '', true, 'Published');
				} else {
					//echo "no publishing".$pid." as user does not have approver rights\n";
				}
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