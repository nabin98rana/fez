<?php

include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.statistics.php");

$tpl = new Template_API();
$tpl->setTemplate("stats_map.tpl.html");
$tpl->assign('maps_api_key', APP_GOOGLE_MAP_KEY);
$tpl->assign("list_heading", "Statistics Map");


if (WEBSERVER_LOG_STATISTICS != 'ON') {
	echo "WEB SERVER STATS CURRENLTY UNAVAILABLE";
	exit;
}

$tpl->displayTemplate();
