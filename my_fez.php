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
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
	$prefs = Prefs::get(Auth::getUserID());
} else {
	Auth::redirect(APP_RELATIVE_URL . "register.php?err=5&username=" . $username);	
}
$tpl->assign("isAdministrator", $isAdministrator);


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

$assigned_items= Record::getAssigned(Auth::getUsername());
$tpl->assign('my_assigned_items_list', $assigned_items);
$tpl->assign("roles_list", Auth::getDefaultRoles());

$tpl->displayTemplate();


?>
