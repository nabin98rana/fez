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
// | Authors: Bernadette Houghton <bhoughton@deakin.edu.au       |
// |                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.record.php');

class BackgroundProcess_Bulk_Add_Handles extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_bulk_add_handles.php';
        $this->name = 'Bulk add handles';
    }

    function run()
    {
        $this->setState(BGP_RUNNING);
        extract(unserialize($this->inputs));
		$numPIDSUpdated = 0;
		
		/*
		 * Update PIDs
		 */
		if (!empty($pids) && is_array($pids)) {
		
			$totalpids = count($pids);
            
    		foreach ($pids as $pid) {
    	        $this->setHeartbeat();
				$numPIDSUpdated ++;
        	    $this->setProgress(++$this->pid_count);
    			$record = new RecordObject($pid);
    			if ($record->canEdit()) {
    				
    			    $res = $record->addHandle($pid);
    				if( $res ) {
    				    $this->setStatus("Added handle to $pid.  Completed $numPIDSUpdated/$totalpids, ");	
    				} else {
    				    $this->setStatus("ERROR adding handle to '".$pid."' ");
    				}
    	        	
    			} else {
    				$this->setStatus("Skipped '".$pid."'. User can't edit this record");
    			}
    		}
    		
            $this->setStatus("Finished Add Handles");
            
		}
        $this->setState(BGP_FINISHED);
    }
}

?>