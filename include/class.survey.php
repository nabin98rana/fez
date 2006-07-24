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
        global $HTTP_POST_VARS;

		$sur_3_cat = @$HTTP_POST_VARS["sur_3_cat"] ? 1 : 0;
		$sur_3_elearn = @$HTTP_POST_VARS["sur_3_elearn"] ? 1 : 0;
		$sur_3_journals = @$HTTP_POST_VARS["sur_3_journals"] ? 1 : 0;
		$sur_3_blackboard = @$HTTP_POST_VARS["sur_3_blackboard"] ? 1 : 0;
		$sur_3_lecture = @$HTTP_POST_VARS["sur_3_lecture"] ? 1 : 0;				
		$sur_3_instrumentation = @$HTTP_POST_VARS["sur_3_instrumentation"] ? 1 : 0;		
		$sur_3_datasets = @$HTTP_POST_VARS["sur_3_datasets"] ? 1 : 0;
		$sur_3_remotedb = @$HTTP_POST_VARS["sur_3_remotedb"] ? 1 : 0;
		$sur_3_extcom = @$HTTP_POST_VARS["sur_3_extcom"] ? 1 : 0;
		$sur_3_collab = @$HTTP_POST_VARS["sur_3_collab"] ? 1 : 0;

		$sur_4_cat = @$HTTP_POST_VARS["sur_4_cat"] ? 1 : 0;
		$sur_4_elearn = @$HTTP_POST_VARS["sur_4_elearn"] ? 1 : 0;
		$sur_4_journals = @$HTTP_POST_VARS["sur_4_journals"] ? 1 : 0;
		$sur_4_blackboard = @$HTTP_POST_VARS["sur_4_blackboard"] ? 1 : 0;
		$sur_4_lecture = @$HTTP_POST_VARS["sur_4_lecture"] ? 1 : 0;				
		$sur_4_instrumentation = @$HTTP_POST_VARS["sur_4_instrumentation"] ? 1 : 0;		
		$sur_4_datasets = @$HTTP_POST_VARS["sur_4_datasets"] ? 1 : 0;
		$sur_4_remotedb = @$HTTP_POST_VARS["sur_4_remotedb"] ? 1 : 0;
		$sur_4_extcom = @$HTTP_POST_VARS["sur_4_extcom"] ? 1 : 0;
		$sur_4_collab = @$HTTP_POST_VARS["sur_4_collab"] ? 1 : 0;
		
		$sur_experience = is_numeric(@$HTTP_POST_VARS["sur_experience"]) ? $HTTP_POST_VARS["sur_experience"] : 5;
		$sur_external_freq = is_numeric(@$HTTP_POST_VARS["sur_external_freq"]) ? $HTTP_POST_VARS["sur_external_freq"] : 5;
				
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "survey
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
					" . Auth::getUserID() . ",
                    " . $sur_experience . ",
                    " . $sur_external_freq . ",
                    " . $sur_3_cat . ",
                    " . $sur_3_elearn . ",
                    " . $sur_3_journals . ",
                    " . $sur_3_blackboard . ",
                    " . $sur_3_lecture . ",
                    " . $sur_3_instrumentation . ",
                    " . $sur_3_datasets . ",																									
                    " . $sur_3_remotedb . ",
                    " . $sur_3_extcom . ",																									
                    " . $sur_3_collab . ",																									
					'" . Misc::escapeString($HTTP_POST_VARS["sur_3_other"]) . "',
                    " . $sur_4_cat . ",
                    " . $sur_4_elearn . ",
                    " . $sur_4_journals . ",
                    " . $sur_4_blackboard . ",
                    " . $sur_4_lecture . ",
                    " . $sur_4_instrumentation . ",
                    " . $sur_4_datasets . ",																									
                    " . $sur_4_remotedb . ",
                    " . $sur_4_extcom . ",																									
                    " . $sur_4_collab . ",																													
					'" . Misc::escapeString($HTTP_POST_VARS["sur_4_other"]) . "',
					'" . Misc::escapeString($HTTP_POST_VARS["sur_comments"]) . "',
					NOW()
                 )"; 
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }

	function hasFilledSurvey($usr_id) {
		$stmt = "select count(*) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "survey where sur_usr_id = $usr_id";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (($res > 0) && (!empty($res))) {
			$filled = 1;
		} else {
			$filled = 0;
		}
		return $filled; 	
	}


}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Survey Class');
}
?>
