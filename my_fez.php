<?php

include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.collection.php");

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");

Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);


if (Auth::userExists($username)) {
	$prefs = Prefs::get(Auth::getUserID());
}


$collection_list = Collection::getList();
$my_collections = array();
foreach ($collection_list as $col) {
    if (@$col['isEditor']) {
        // get parent community name
        $parents = Collection::getParents($col['pid']);
        $p2 = array();
        foreach ($parents as $p) {
            $p2[$p['pid']] = $p['title'];
        }
        $parents = implode(', ',array_values($p2));
        $col['community'] = $parents;
        // get the roles
        // get the number of records
        $col['items'] = Collection::getCount($col['pid']);
        $my_collections[] = $col;
    }
}
$tpl->assign('my_collections_list', $my_collections);

$workflow_items = Record::getAssigned($username);
$tpl->assign('my_workflow_items_list', $workflow_items);

$tpl->displayTemplate();


?>
