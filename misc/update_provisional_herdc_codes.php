<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

set_time_limit(0);

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.record.php");

define('PROVISIONAL_CODE_UPDATE_FROM_SCRIPT', true);

/*
 * These three queries are retrieve the three sets of records that MG wants processed.
 * 

1. RID Download collection (UQ:183940)

	SELECT rek_pid AS pid
	FROM fez_record_search_key
	LEFT JOIN fez_record_search_key_ismemberof
		ON rek_pid = rek_ismemberof_pid
	LEFT JOIN fez_record_search_key_herdc_code
		ON rek_pid = rek_herdc_code_pid
	WHERE rek_ismemberof = 'UQ:183940'
	AND (rek_herdc_code IS NULL
		OR rek_herdc_code = '-1')
	AND (rek_subtype IS NOT NULL
		OR rek_genre_type IS NOT NULL)
	ORDER BY rek_pid;

2. WOS Import collection (UQ:180159)

	SELECT rek_pid AS pid
	FROM fez_record_search_key
	LEFT JOIN fez_record_search_key_ismemberof
		ON rek_pid = rek_ismemberof_pid
	LEFT JOIN fez_record_search_key_herdc_code
		ON rek_pid = rek_herdc_code_pid
	WHERE rek_ismemberof = 'UQ:180159'
	AND (rek_herdc_code IS NULL
		OR rek_herdc_code = '-1')
	AND (rek_subtype IS NOT NULL
		OR rek_genre_type IS NOT NULL)
	ORDER BY rek_pid;

3. Records that are:
	* conf papers, journal articles, online journal articles, books or book chapters.
	* do not have a herdc code
	* have at least 1 linked author

	SELECT DISTINCT(rek_pid) AS pid
	FROM fez_record_search_key
	LEFT JOIN fez_xsd_display
		ON fez_record_search_key.rek_display_type = fez_xsd_display.xdis_id
	LEFT JOIN fez_record_search_key_herdc_code
		ON rek_pid = rek_herdc_code_pid
	LEFT JOIN fez_record_search_key_author_id
		ON rek_pid = rek_author_id_pid
	WHERE
	(xdis_title = 'Conference Paper'
		OR xdis_title = 'Journal Article'
		OR xdis_title = 'Online Journal Article'
		OR xdis_title = 'Book'
		OR xdis_title = 'Book Chapter')
	AND xdis_version = 'MODS 1.0'
	AND (rek_herdc_code IS NULL
		OR rek_herdc_code = '-1')
	AND rek_author_id != 0
	AND (rek_subtype IS NOT NULL
		OR rek_genre_type IS NOT NULL)
	ORDER BY rek_pid;

 */

$query = "



		";

$db = DB_API::get();
$log = FezLog::get();

try {
        $result = $db->fetchAll($query);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}

echo "Total records queued for processing: " . count($result) . "\n";

/* Build a list of PIDs */
$pids = array();
foreach ($result as $item) {
	$pids[] = $item['rek_pid'];
}


// Processeroo
foreach ($pids as $pid) {
	echo "* " . $pid . "\n";
	Record::applyProvisionalCode($pid);
}

echo "\nDone.\n";
