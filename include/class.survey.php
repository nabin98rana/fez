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
 * Class to handle fez user surveys.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");

class Survey
{
	/**
	 * Method used to add a new survey result to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the insert worked, -1 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$sur_3_cat = @$_POST["sur_3_cat"] ? 1 : 0;
		$sur_3_elearn = @$_POST["sur_3_elearn"] ? 1 : 0;
		$sur_3_journals = @$_POST["sur_3_journals"] ? 1 : 0;
		$sur_3_blackboard = @$_POST["sur_3_blackboard"] ? 1 : 0;
		$sur_3_lecture = @$_POST["sur_3_lecture"] ? 1 : 0;
		$sur_3_instrumentation = @$_POST["sur_3_instrumentation"] ? 1 : 0;
		$sur_3_datasets = @$_POST["sur_3_datasets"] ? 1 : 0;
		$sur_3_remotedb = @$_POST["sur_3_remotedb"] ? 1 : 0;
		$sur_3_extcom = @$_POST["sur_3_extcom"] ? 1 : 0;
		$sur_3_collab = @$_POST["sur_3_collab"] ? 1 : 0;

		$sur_4_cat = @$_POST["sur_4_cat"] ? 1 : 0;
		$sur_4_elearn = @$_POST["sur_4_elearn"] ? 1 : 0;
		$sur_4_journals = @$_POST["sur_4_journals"] ? 1 : 0;
		$sur_4_blackboard = @$_POST["sur_4_blackboard"] ? 1 : 0;
		$sur_4_lecture = @$_POST["sur_4_lecture"] ? 1 : 0;
		$sur_4_instrumentation = @$_POST["sur_4_instrumentation"] ? 1 : 0;
		$sur_4_datasets = @$_POST["sur_4_datasets"] ? 1 : 0;
		$sur_4_remotedb = @$_POST["sur_4_remotedb"] ? 1 : 0;
		$sur_4_extcom = @$_POST["sur_4_extcom"] ? 1 : 0;
		$sur_4_collab = @$_POST["sur_4_collab"] ? 1 : 0;

		$sur_experience = is_numeric(@$_POST["sur_experience"]) ? $_POST["sur_experience"] : 5;
		$sur_external_freq = is_numeric(@$_POST["sur_external_freq"]) ? $_POST["sur_external_freq"] : 5;

		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "survey
                 (
				 	sur_usr_id,
                    sur_experience,
                    sur_external_freq,
                    sur_3_cat,
                    sur_3_elearn,
                    sur_3_journals,
                    sur_3_blackboard,
                    sur_3_lecture,
                    sur_3_instrumentation,
                    sur_3_datasets,
                    sur_3_remotedb,
                    sur_3_extcom,					
                    sur_3_collab,					
                    sur_3_other,
                    sur_4_cat,
                    sur_4_elearn,
                    sur_4_journals,
                    sur_4_blackboard,
                    sur_4_lecture,
                    sur_4_instrumentation,
                    sur_4_datasets,
                    sur_4_remotedb,
                    sur_4_extcom,					
                    sur_4_collab,					
                    sur_4_other,
					sur_comments,
					sur_datetime
                 ) VALUES (
					" . $db->quote(Auth::getUserID(), 'INTEGER') . ",
                    " . $db->quote($sur_experience, 'INTEGER') . ",
                    " . $db->quote($sur_external_freq, 'INTEGER') . ",
                    " . $db->quote($sur_3_cat, 'INTEGER') . ",
                    " . $db->quote($sur_3_elearn, 'INTEGER') . ",
                    " . $db->quote($sur_3_journals, 'INTEGER') . ",
                    " . $db->quote($sur_3_blackboard, 'INTEGER') . ",
                    " . $db->quote($sur_3_lecture, 'INTEGER') . ",
                    " . $db->quote($sur_3_instrumentation, 'INTEGER') . ",
                    " . $db->quote($sur_3_datasets, 'INTEGER') . ",																									
                    " . $db->quote($sur_3_remotedb, 'INTEGER') . ",
                    " . $db->quote($sur_3_extcom, 'INTEGER') . ",																									
                    " . $db->quote($sur_3_collab, 'INTEGER') . ",																									
					" . $db->quote($_POST["sur_3_other"]) . ",
                    " . $db->quote($sur_4_cat, 'INTEGER') . ",
                    " . $db->quote($sur_4_elearn, 'INTEGER') . ",
                    " . $db->quote($sur_4_journals, 'INTEGER') . ",
                    " . $db->quote($sur_4_blackboard, 'INTEGER') . ",
                    " . $db->quote($sur_4_lecture, 'INTEGER') . ",
                    " . $db->quote($sur_4_instrumentation, 'INTEGER') . ",
                    " . $db->quote($sur_4_datasets, 'INTEGER') . ",																									
                    " . $db->quote($sur_4_remotedb, 'INTEGER') . ",
                    " . $db->quote($sur_4_extcom, 'INTEGER') . ",																									
                    " . $db->quote($sur_4_collab, 'INTEGER') . ",																													
					" . $db->quote($_POST["sur_4_other"]) . ",
					" . $db->quote($_POST["sur_comments"]) . ",
					NOW()
                 )"; 
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}

	function hasFilledSurvey($usr_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "select count(*) from " . APP_TABLE_PREFIX . "survey where sur_usr_id = ".$db->quote($usr_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return 0;
		}
		if (($res > 0) && (!empty($res))) {
			$filled = 1;
		} else {
			$filled = 0;
		}
		return $filled;
	}


}
