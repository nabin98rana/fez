<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.background_process_list.php");
include_once(APP_INC_PATH.'najax/najax.php');
include_once(APP_INC_PATH.'najax_objects/class.background_process_list.php');

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");

$order_by = Misc::GETorPOST('order_by');
if (empty($order_by)) {
    $order_by = 'Title';
}
$order_by_list = array();
foreach (Search_Key::getAssocList() as $key => $value) {
    $order_by_list[$value] = $value;
}
$tpl->assign('order_by_list', $order_by_list);

$tpl->assign('order_by_default', $order_by);


Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
	$prefs = Prefs::get(Auth::getUserID());
} elseif ($username != ""){
// don't require registration now for logged in users, although they can (to get prefs etc) but don't force them
//	Auth::redirect(APP_RELATIVE_URL . "register.php?err=5&username=" . $username);	
}
$tpl->assign("isAdministrator", $isAdministrator);

$collection_list = Collection::getEditList();
//print_r($collection_list);
foreach ($collection_list as &$item) {
   $item['community'] = implode(',',Misc::keyPairs(Collection::getParents2($item['pid']),'pid','title'));
   $item['count'] = Collection::getEditListingCount($item['pid']);
}

$tpl->assign('my_collections_list', $collection_list);


$bgp_list = new BackgroundProcessList;
$tpl->assign('bgp_list', $bgp_list->getList(Auth::getUserID()));
$tpl->assign('bgp_states', $bgp_list->getStates());

$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");



$tpl->assign("roles_list", Auth::getDefaultRoles());
$pagerRow = Pager::getParam('pagerRow_my_assigned');
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$rows = Pager::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
$options = Pager::saveSearchParams();
$tpl->assign("options", $options);
$assigned_items= Record::getAssigned(Auth::getUsername(), $pagerRow, $rows, $order_by);
$tpl->assign('my_assigned_items_list', $assigned_items['list']);
$tpl->assign('my_assigned_items_info', $assigned_items['info']);

$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->assign('najax_register', NAJAX_Client::register('NajaxBackgroundProcessList', APP_RELATIVE_URL.'najax_services/generic.php'));

$tpl->displayTemplate();

//$bench->display(); // to output html formated
?>
