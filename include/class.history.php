<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
 * Class to handle the business logic related to the history logging
 * available in the application.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.fedora_api.php");

class History
{
   /**
     * Method used to create an audit part of a FezHistory datastream.
     *
     * @access  public
     * @return  void
     */
	function generateHistoryAction($audit_id, $audit_date, $audit_usr_id="", $audit_usr_full_name, $audit_justification, $audit_pid, $audit_dsID="") {
		$auditXML = "<audit>
						<audit_id>$audit_id</audit_id>
						<audit_date>$audit_date</audit_date>
						<audit_usr_id>$audit_usr_id</audit_usr_id>
						<audit_usr_full_name>$audit_usr_full_name</audit_usr_full_name>
						<audit_justification>$audit_justification</audit_justification>
						<audit_pid>$audit_pid</audit_pid>";
		if ($audit_dsID != "") {
			$auditXML .= "	<audit_dsID>$audit_dsID</audit_dsID>";
		} else {
			$auditXML .= "	<audit_dsID/>";		
		}
		$auditXML .= "		
					</audit>";
		return $auditXML;												
	}


   /**
     * Method used to add a new entry to the object's FezHistory log.
     *
     * @access  public
     * @return  void
     */
	function addHistory($pid, $audit_dsID="", $audit_justification) {
		$dsIDName = "FezHistory";
		$audit_usr_id = Auth::getUserID();
		$audit_usr_full_name = User::getFullName($audit_usr_id);
		$audit_date = date("Y-m-d H:i:s");
		// First check if a FezHistory datastream exists
		$dsExists = Fedora_API::datastreamExists($pid, $dsIDName);
	    if ($dsExists !== true) {
			$newAudit = History::generateHistoryAction(1, $audit_date, $audit_usr_id, $audit_usr_full_name, $audit_justification, $pid, $audit_dsID);
			$newXML = "<FezHistory>".$newAudit."</FezHistory>";
		    Fedora_API::getUploadLocation($pid, $dsIDName, $newXML, "Fez History Datastream", "text/xml", "X");
		} else {
			$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezHistory');
			print_r($xdis_array); exit;
//			Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, "A", "Fez History Datastream", $newXML, "text/xml", true);
		}


    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included History Class');
}
?>