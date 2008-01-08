<?php

include_once("../config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.search_key.php");

if( $argc != 2 || !is_numeric($argv[1]) )
{
    usage();
    exit();
}

$limit = $argv[1];

$list = array();
$options = array();

$options["sort_order"] = "1";
$sort_by = "searchKey".Search_Key::getID("Created Date");
$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only

$list = Record::getListing($options, array("Lister"), 0, $limit, "Created Date", false, true);

foreach ( $list['list'] as $pidData )
{
    $pids[] = $pidData['rek_pid'];
}

if( count($pids) > 0 )
{
    Record::deleteRecentRecords();
    Record::insertRecentRecords($pids);
}


function usage()
{
    echo "\n\tUsage: path-to-php cache_recent_items.php [limit]\n";
    echo "\tWhere [limit] is the number of records to cache\n\n";
}

?>