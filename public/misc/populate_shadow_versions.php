<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH.'class.auth.php');

// Only super admins here
Auth::checkAuthentication(APP_SESSION);
$isUser = Auth::getUsername();
if (!User::isUserSuperAdministrator($isUser)) {
    echo 'Admins only';
    exit;
}

echo '<pre>';
updateShadowVersions();
echo 'Done!';


function updateShadowVersions()
{
    updateVersion();
    $searchKeys = Search_Key::getList(false);
    foreach ($searchKeys as $sekDetails) {
        if ($sekDetails['sek_relationship'] == 1) {
            updateVersion($sekDetails['sek_title_db']);
            echo "Updated versions for ${sekDetails['sek_title_db']}\n";
        }
    }
}

function updateVersion($tblPart = "")
{
    $db = DB_API::get();
    if (!empty($tblPart)) {
        $tblPart = '_' . $tblPart;
    }
    $tbl = APP_TABLE_PREFIX . "record_search_key{$tblPart}__shadow";
    $db->query("update {$tbl} set rek{$tblPart}_version = CONCAT(rek{$tblPart}_pid,' ',rek{$tblPart}_stamp)");
}
