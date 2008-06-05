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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
 
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.community.php');
include_once(APP_INC_PATH.'class.record.php');
include_once(APP_INC_PATH.'class.reindex.php');
include_once(APP_INC_PATH.'class.origami.php');

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
            $index_type = Reindex::INDEX_TYPE_REINDEX;
        }
        $params['index_type'] = $index_type;
        $reindex->bgp = $this;
        if ($index_type == Reindex::INDEX_TYPE_FEDORAINDEX || $index_type == Reindex::INDEX_TYPE_UNDELETE) {
        	$reindex->reindexMissingList($params,$terms);
        } elseif ($index_type == Reindex::INDEX_TYPE_REINDEX)  {
            $reindex->reindexFullList($params,$terms);
        } elseif( $index_type == Reindex::INDEX_TYPE_ORIGAMI ) {
            
            $cnt    = 0;
            if($params['items']) {
                $pids   = $params['items'];
            } else {
                $pids   = Reindex::getPIDlist();
            }
            $total  = count($pids);
            
            foreach ($pids as $pid) {
                
                $ds = Fedora_API::callGetDatastreams($pid);
                
                foreach ($ds as $stream) {
                    
                    if((strpos($stream['ID'], 'web_') === false) &&
                        (strpos($stream['ID'], 'preview_') === false) &&
                        (strpos($stream['ID'], 'thumbnail_') === false) &&
                        ($stream['MIMEType'] == 'image/jpeg' || 
                         $stream['MIMEType'] == 'image/jpg' || 
                         $stream['MIMEType'] == 'image/tif' || 
                         $stream['MIMEType'] == 'image/tiff')) {
                            
                        $this->setStatus("Creating Title for DS - " .$stream['ID']);
                        Origami::createTitles($pid, $stream['ID'], $stream['MIMEType']);
                    }
                    
                }
                
                $cnt++;
                if(($cnt % 1000) == 0) {
                    $this->setStatus("Processed ($cnt/$total) pids");
                }
            }
            
            if($cnt > 0) {
                $this->setStatus("Processed ($cnt/$total) pids");
            }
            
        } elseif ($index_type == Reindex::INDEX_TYPE_REINDEX_OBJECTS) {
            $this->setStatus("Beginning Reindex of ".$pid." and any of its children objects");
        	if (!empty($pid)) {
        		$source_pids = array();
        		$parent_pids = array($pid);
        		for ($ii = 0; $ii < count($parent_pids); $ii++) {
//        			$record = new RecordGeneral($pid);
					$pid = $parent_pids[$ii];
        			$record = new RecordGeneral($pid);
        			if ($record->isCollection()) {
	            		$this->setStatus("Getting children of collection ".$pid."");
        				$source_pids = array_unique(array_merge($source_pids, $record->getChildrenPids()));
	            		$this->setStatus("Source pids of ".$pid." are ".implode(", ", $source_pids));
    				} elseif ($record->isCommunity()) {
	            		$this->setStatus("Getting children of community ".$pid."");
    					$parent_pids = array_unique(array_merge($parent_pids, $record->getChildrenPids()));
	            		$this->setStatus("Children of community ".$pid." are ".implode(", ", $parent_pids));
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
        }
        elseif( $index_type == Reindex::INDEX_TYPE_SOLR )
        {
            $reindex->reindexSolrFullList($params,$terms);
        }
        $this->setState(2);        // done
    }
}
 
?>
