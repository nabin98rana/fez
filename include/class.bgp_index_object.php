<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 28/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.community.php');
include_once(APP_INC_PATH.'class.record.php');
include_once(APP_INC_PATH . "class.reindex.php");


@define('INDEX_TYPE_FEDORAINDEX', 1);
@define('INDEX_TYPE_REINDEX', 2);
@define('INDEX_TYPE_REINDEX_OBJECTS', 3);  // index specific pids


class BackgroundProcess_Index_Object extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_index_object.php';
        $this->name = 'Index Object';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        $GLOBALS['app_cache'] = false; //SWITCH OFF the $GLOBALS['app_cache'] var for the bgp indexing
        if ($GLOBALS['app_cache']) {
            $this->setStatus("app_cache ON for background processing");
        } else {
            $this->setStatus("app_cache OFF for background processing");
        }
        $reindex = new Reindex;

        if (empty($terms)) {
            $terms = '';
        }
        if (empty($params)) {
            $params = array();
        }
        if (empty($index_type)) {
            $index_type = INDEX_TYPE_REINDEX;
        }
        $reindex->bgp = $this;
        if ($index_type == INDEX_TYPE_FEDORAINDEX) {
            $reindex->reindexMissingList($params,$terms);
        } elseif ($index_type == INDEX_TYPE_REINDEX_OBJECTS) {
        	if (!empty($pid)) {
        		$source_pids = array();
        		$parent_pids = array($pid);
        		for ($ii = 0; $ii < count($parent_pids); $ii++) {
        			$record = new RecordGeneral($pid);
        			if ($record->isCollection()) {
        				$source_pids = array_unique(array_merge($source_pids, $record->getChildrenPids()));
    				} elseif ($record->isCommunity()) {
    					$parent_pids = array_merge($parent_pids, $record->getChildrenPids());
					} else {
        				$source_pids = array_unique(array_merge($source_pids, array($pid)));
					}
				}
				$this->setProgress(1);
				$this->setStatus("Reindexing ".count($source_pids)." items.");
				$reindex_record_counter = 0;
				$record_count = count($source_pids);
				foreach ($source_pids as $source_pid) {
		            $reindex_record_counter++;

                    $bgp_details = $this->getDetails();
                    $utc_date = Date_API::getSimpleDateUTC();
                    $time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
                    $date_new = new Date(strtotime($bgp_details['bgp_started']));
                    $time_per_object = round(($time_per_object / $reindex_record_counter), 2);
                    //$expected_finish = Date_API::getFormattedDate($date_new->getTime());
                    $date_new->addSeconds($time_per_object*$record_count);
                    $tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
    				$res[$key]["bgp_started"] = Date_API::getFormattedDate($res[$key]["bgp_started"], $tz);
                    $expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);
					$this->setProgress(intval(100*$reindex_record_counter/$record_count));
                    $this->setStatus("Reindexing:  '".$source_pid."'  (".$reindex_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");

					$params['items'] = array($source_pid);
					$params['rebuild'] = false;
					$reindex->indexFezFedoraObjects($params);
				}
				$this->setProgress(100);
				$this->setStatus("Reindexed ".count($source_pids)." items.");
        	}
        } else  {
            $reindex->reindexFullList($params,$terms);
        }
        $this->setState(2);        // done
    }
}
 
?>
