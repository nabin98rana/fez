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

// 1.5 Determine the maximum PID, save it to the new pid_index table.
$nextPID = Fedora_API::getNextPID(false);
$nextPIDParts = explode(":", $nextPID);
$nextPIDNumber = $nextPIDParts[1];

echo "Creating pid_index table ... ";
$stmt = "CREATE TABLE fez_pid_index (pid_number int(10) unsigned NOT NULL, PRIMARY KEY (pid_number));";
$db->exec($stmt);
echo "ok!\n";

echo "Fetching next PID from Fedora, and writing to pid_index table ... ";
$stmt = "INSERT INTO fez_pid_index (pid_number) values ('" . $nextPIDNumber . "');";
$db->exec($stmt);
echo "ok!\n";

// 1.6 Remove unique constraints from non-core shadow tables
$searchKeys = Search_Key::getList();
foreach ($searchKeys as $sk) {
	if ($sk['sek_relationship'] == '1') {
		echo "* Removing unique constraints from fez_record_search_key_" . $sk['sek_title_db'] . "__shadow ... ";
		$stmt = "DROP INDEX unique_constraint ON fez_record_search_key_" . $sk['sek_title_db'] . "__shadow;";
		try {
			$db->exec($stmt);
		} catch (Exception $ex) {
			echo "No constraint to remove.\n";
		}
		echo "ok!\n";
	}
}

// 1.7 Remove unique constraints from core shadow table
echo "* Removing unique constraint from fez_record_search_key__shadow ... ";
$stmt = "DROP INDEX unique_constraint ON fez_record_search_key__shadow;";
try {
	$db->exec($stmt);
} catch (Exception $ex) {
	echo "No constraint to remove.\n";
}
echo "ok!\n";

echo "* Removing primary key constraint from fez_record_search_key__shadow ... ";
$stmt = "ALTER TABLE fez_record_search_key__shadow DROP PRIMARY KEY;";
try {
	$db->exec($stmt);
} catch (Exception $ex) {
	echo "No constraint to remove.\n";
}
echo "ok!\n";

echo "Done.\n\n";

// Other steps as necessary.

exit ("\n\nExiting Fedora upgrade script.\n");

?>