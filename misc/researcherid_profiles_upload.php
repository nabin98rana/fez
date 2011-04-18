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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

ini_set('display_errors', 1);

$log = FezLog::get();
$db = DB_API::get();

/*$blocked = array('uqwbowen');
$row = 1;
$handle = fopen("newstaff.csv", "r");
$author_ids = array();
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	if($row > 1) {
		$aut_id = getAutIdByUsername($data[10]);
		if($aut_id && (!in_array($data[10], $blocked))) {
			$author_ids[] = $aut_id;
		}
	}
    $row++;
}
fclose($handle);

ResearcherID::profileUpload($author_ids);
*/
/*foreach($author_ids as $id) {
	Author::setResearcherIdByAutId($id, '-1');
}*/



$row = 1;
$handle = fopen("newstaff.csv", "r");
$author_ids = array();
$_data = array();
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $_data[$row] = array();
	for($i=0; $i<count($data); $i++) {
		$_data[$row][] = $data[$i];
	}
	if($row > 1) {
		$_data[$row][] = getRidByUsername($data[10]);
		$_data[$row][] = getRidTempPwdByUsername($data[10]);		
	}
	else {
		$_data[$row][] = 'RID';
		$_data[$row][] = 'RID Temp Password';
	}
    $row++;
}
fclose($handle);

$fp = fopen('newstaffpass.csv', 'w');
foreach ($_data as $line) {
    fputcsv($fp, $line);
}
fclose($fp);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=newstaff.csv");
echo file_get_contents('newstaffpass.csv');

exit();


function getRidByUsername($username) {
	$log = FezLog::get();
	$db = DB_API::get();
	
	$stmt = "SELECT
				aut_researcher_id
			 FROM
				" . APP_TABLE_PREFIX . "author
			 WHERE
				aut_org_username=?";
	try {
		$res = $db->fetchOne($stmt, array($username));
	}
	catch(Exception $ex) {
		$log->err($ex);
		return '';
	}
	return $res;
}

function getRidTempPwdByUsername($username) {
	$log = FezLog::get();
	$db = DB_API::get();
	
	$stmt = "SELECT
				aut_rid_password
			 FROM
				" . APP_TABLE_PREFIX . "author
			 WHERE
				aut_org_username=?";
	try {
		$res = $db->fetchOne($stmt, array($username));
	}
	catch(Exception $ex) {
		$log->err($ex);
		return '';
	}
	return $res;
}

function getAutIdByUsername($username) {
	$log = FezLog::get();
	$db = DB_API::get();
	
	$stmt = "SELECT
				aut_id
			 FROM
				" . APP_TABLE_PREFIX . "author
			 WHERE
				aut_org_username=?";
	try {
		$res = $db->fetchOne($stmt, array($username));
	}
	catch(Exception $ex) {
		$log->err($ex);
		return false;
	}
	return $res;
}