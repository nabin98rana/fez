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
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.fezacml.php");
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

	function insert($files, $files_FezACML, $xdis_id, $pid, $wftpl) {
		if (is_array($files)) {
			foreach($files as $key => $ds) {
		         $short_ds = $ds;
		         if (is_numeric(strpos($ds, "/"))) {
		             $short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
		         }

		         // ID must start with _ or letter
	         	$short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 64);

		
		        $temp_store = APP_TEMP_DIR.$short_ds;
				copy($ds,$temp_store);
		
		
                 $mimetype = Misc::mime_content_type($temp_store);
				 if (APP_VERSION_UPLOADS_AND_LINKS == "ON") {
				 	$versionable = "true";
				 } else {
					$versionable = "false";
				 }

 				 Fedora_API::getUploadLocationByLocalRef($pid, $short_ds, $temp_store, $short_ds, $mimetype,"M",null,$versionable);
				// Seeing if record::generatePresmd will work as well (will also do exiftool at the same time)
				Record::generatePresmd($pid, $short_ds);
				if (array_key_exists($key, $files_FezACML)) {
					if (!empty($files_FezACML[$key])) {
						$xmlObjNum = $files_FezACML[$key];
						if (is_numeric($xmlObjNum) && $xmlObjNum != "-1" && $xmlObjNum != -1) {
							$xmlObj = FezACML::getQuickTemplateValue($xmlObjNum);
							
							if ($xmlObj != false) {
								$dsID = $short_ds;				
								$FezACML_dsID = FezACML::getFezACMLDSName($dsID);
								if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
									Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
											$xmlObj, "text/xml", "true");
								} else {
									Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID,
											"text/xml", "X",null,"true");
								}
							}
						}
					}
				}
				Workflow::processIngestTrigger($pid, $short_ds, $mimetype);
 		        if (is_file($temp_store)) {
		            unlink($temp_store);
		        }

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
