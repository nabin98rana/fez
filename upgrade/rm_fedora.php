<?php

/**
 * This script is intended to be run once, when the site owner is ready to switch off Fedora 
 * for good. A series of schema changes will be triggered, and Fedora-bypass functionality 
 * will be activated.
 * 
 * Developers working on the Fedora phase-out should add any necessary functions to this file.
 * In staging, you may find it useful to comment out anything you've already run, while testing
 * new upgrade code in this script.
 * 
 * I've like to thank my parents.
 **/

set_time_limit(0);
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.search_key.php");

echo "WELCOME TO THE WORLD OF TOMORROW!\n\n";

$log = FezLog::get();
$db = DB_API::get();

////////////////////////////////////////////////////////////////////////////////////////////
// SHADOW TABLES
////////////////////////////////////////////////////////////////////////////////////////////

// 1.1 Create core search key shadow table
echo "Creating core search key shadow table ... ";
$stmt = "CREATE TABLE fez_record_search_key__shadow LIKE fez_record_search_key;";
try {
	$db->exec($stmt);
} catch (Exception $ex) {
	$log->err($ex);
	return -1;
}
echo "done.\n";

// 1.2 Add stamp column to new shadow table
echo "Adding stamp column to new shadow table ... ";
$stmt = "ALTER TABLE fez_record_search_key__shadow ADD COLUMN `rek_stamp` datetime;";
try {
	$db->exec($stmt);
} catch (Exception $ex) {
	$log->err($ex);
	return -1;
}
echo "done.\n";

// 1.3 Create non-core search key shadow tables
echo "Creating non-core search key shadow tables ... \n";
$searchKeys = Search_Key::getList();
foreach ($searchKeys as $sk) {
	if ($sk['sek_relationship'] == '1') {
		echo "* Shadowing " . $sk['sek_title_db'] . " table ... ";
		$stmt = "CREATE TABLE fez_record_search_key_" . $sk['sek_title_db'] . "__shadow LIKE fez_record_search_key_" . $sk['sek_title_db'] . ";";
		try {
			$db->exec($stmt);
		} catch (Exception $ex) {
			$log->err($ex);
			return -1;
		}
		echo "ok!\n";
	}
}
echo "Done.\n\n";

// 1.4 Add stamp column to new shadow tables
$searchKeys = Search_Key::getList();
foreach ($searchKeys as $sk) {
	if ($sk['sek_relationship'] == '1') {
		echo "* Adding datestamp to " . $sk['sek_title_db'] . " ... ";
		$stmt = "ALTER TABLE fez_record_search_key_" . $sk['sek_title_db'] . "__shadow ADD COLUMN `rek_" . $sk['sek_title_db'] . "_stamp` datetime;";
		try {
			$db->exec($stmt);
		} catch (Exception $ex) {
			$log->err($ex);
			return -1;
		}
		echo "ok!\n";
	}
}
echo "Done.\n\n";

exit ("\n\nExiting Fedora upgrade script.\n");

?>