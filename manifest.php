<?php

include_once('config.inc.php');

$issue_pid = $_REQUEST['issue_pid'];

$stmt = "select im1.rek_ismemberof_pid as section_pid, rs1.rek_title as section_title,
 im2.rek_ismemberof_pid as article_pid, 
 rs2.rek_title as article_title 
from fez_record_search_key_ismemberof AS im1
inner join fez_record_search_key_ismemberof AS im2
ON im2.rek_ismemberof=im1.rek_ismemberof_pid
inner join fez_record_search_key rs1 ON rs1.rek_pid=im1.rek_ismemberof_pid
inner join fez_record_search_key rs2 ON rs2.rek_pid=im2.rek_ismemberof_pid
WHERE im1.rek_ismemberof='$issue_pid'
ORDER BY rs2.rek_sequence, rs1.rek_sequence
";
$res = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);

$sections = array();
foreach ($res as $item) {
	$sections[$item['section_pid']]['articles'][] = array('article_pid' => $item['article_pid']);
	$sections[$item['section_pid']]['section_title'] = $item['section_title'];
}
$sections = array_values($sections);


$stmt = "SELECT rek_file_attachment_name 
			FROM ".APP_TABLE_PREFIX."record_search_key_file_attachment_name 
				WHERE rek_file_attachment_name_pid='$issue_pid' ";
$res =  $GLOBALS['db_api']->dbh->getCol($stmt);
foreach ($res as $file) {
    if ((stristr($file, ".jpg") 
            	|| stristr($file, ".jpeg") 
            	|| stristr($file, ".png") 
            	|| stristr($file, ".gif"))
            && stristr($file, "web_")
	    ) {
	    	$attachment = APP_BASE_URL.'eserv.php?pid='.$issue_pid.'&amp;dsID='.$file;
    	}
}

header('Content-Type: application/xml');
$tpl = new Template_API;
$tpl->setTemplate('themes/manifest.tpl.xml');
$tpl->assign(compact('sections','issue_pid','attachment'));
$tpl->displayTemplate();

?>