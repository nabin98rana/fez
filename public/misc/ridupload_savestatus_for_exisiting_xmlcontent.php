<?php

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

/**
 * One off script to load related author & summary status from any good XML response of RID Author Profile Upload request.
 *
 * @version 1.0, Mar 28, 2012
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.researcherid.php");


$db = DB_API::get();

$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "rid_profile_uploads;"; // WHERE rpu_response_status = '';";
$reports = $db->fetchAll($stmt);

foreach ($reports as $report){
    
    // Parse content of the URL - good response will be in XML format
    $xml_report = @simplexml_load_string($report['rpu_response']);  // we are using @ to silence the PHP warning 
    
    if (!isset($xml_report->profileList) && !isset($xml_report->publicationList)) {
        // Invalid XML response :
        // Do not move this email to "processed" directory, so we can re-process again.
        // Continue with next email. 
        continue;
    }

    // Process profile list
    if (isset($xml_report->profileList)) {
        ResearcherID::saveProfileUploadStatusAndAuthor($xml_report->profileList, $report["rpu_id"]);
        echo chr(10) . "<br /> Saved Author & Status for RPU_ID = ".$report['rpu_id'];
    } 

    
}

