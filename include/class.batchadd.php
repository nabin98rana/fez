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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class designed to handle all business logic related to the batch importing of records in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.eprints.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.background_process.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.error_handler.php");


/**
  * Batch Add
  */
class BatchAdd
{
    var $pid;
    var $externalDatastreams;
    var $bgp; // background process object for keeping track of status since batch add runs in background

    function setBackgroundObject($bgp)
    {
        $this->bgp = $bgp;
    }

	function insert($files, $xdis_id, $pid, $wftpl) {
		if (is_array($files)) {
			foreach($files as $ds) {
		         $short_ds = $ds;
		         if (is_numeric(strpos($ds, "/"))) {
		             $short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
		         }
		         // ID must start with _ or letter
		         $short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 64);
                 $mimetype = Misc::mime_content_type($ds);
 				 Fedora_API::getUploadLocationByLocalRef($pid, $short_ds, $ds, $short_ds, $mimetype);
                 $presmd_check = Workflow::checkForPresMD($ds);  
                 if ($presmd_check != false) {
                    Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, 
                             $presmd_check, "text/xml", "M");
                 }			
                 if (is_file(APP_TEMP_DIR.basename($presmd_check))) {
                     $deleteCommand = APP_DELETE_CMD." ".APP_TEMP_DIR.basename($presmd_check);
                     exec($deleteCommand);
                 }
                 Workflow::processIngestTrigger($pid, $ds, $mimetype);
			}			
		} else {
			return false;
		}
		return true;
	}
}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Batch Add Class');
}

?>
