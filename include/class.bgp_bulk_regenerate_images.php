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
include_once("../config.inc.php");
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.workflow_status.php');

class BackgroundProcess_Bulk_Regenerate_Images extends BackgroundProcess
{
   
   function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_bulk_regenerate_images.php';
        $this->name = 'Bulk Regenerate Images';

	}  

    function run($pids)
    {
		
       $this->setState(1);  // Status: Running
       extract(unserialize($this->inputs));
       $numPIDSUpdated = 0;

		/*
		 * Regenerate image datastreams for given $pids
		 */
		if (!empty($pids) && is_array($pids)) {
		
            $totalpids = count($pids);
		    $this->setStatus("Regenerating images for ".$totalpids." records");
		    
    		foreach ($pids as $pid) {
    	        $this->setHeartbeat();
				$numPIDSUpdated ++;
				$this->setStatus("Regenerating images for record $numPIDSUpdated/$totalpids, $pid");

				$rec_obj = new RecordObject($pid);
				$rec_obj->regenerateImages();
				
				$this->setProgress($numPIDSUpdated);
				$this->markPidAsFinished($pid);
    		}
    		
            $this->setStatus("Finished Regenerating Images");
            
		}
        $this->setState(2);  // Status: Done
    }
    
}

?>
