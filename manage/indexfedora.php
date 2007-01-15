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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.reindex.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.bgp_index_object.php");
include_once(APP_INC_PATH . "najax_classes.php");


$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");
$tpl->assign("type", "fedoraindex");

Auth::checkAuthentication(APP_SESSION);

if (strstr($_SERVER['REQUEST_URI'], "indexfedora.php")) {
    $index_type = INDEX_TYPE_FEDORAINDEX; 
} else {
    $index_type = INDEX_TYPE_REINDEX; 
}
$tpl->assign("index_type", $index_type);

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$reindex = new Reindex;
$terms = Pager::getParam('keywords');

if ($isAdministrator) {
    if (!empty($HTTP_POST_VARS["go_list"])) {
		Reindex::indexFezFedoraObjects();
    }
    if (!empty($HTTP_POST_VARS["do_all"])) {
        $params = &$HTTP_POST_VARS;
        $bgp = new BackgroundProcess_Index_Object();
        $bgp->register(serialize(compact('params','terms','index_type')), Auth::getUserID());
    }
} else {
    $tpl->assign("show_not_allowed_msg", true);
}
$rows = Pager::getParam('rows');
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
$pagerRow = Pager::getParam('pagerRow');
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$terms = Pager::getParam('keywords');
$tpl->assign('keywords', $terms);
$options = Pager::saveSearchParams();
$tpl->assign("options", $options);
if ($index_type == INDEX_TYPE_FEDORAINDEX) {
    $details = $reindex->getMissingList($pagerRow, $rows, $terms);
} else {
	$details = $reindex->getFullList($pagerRow, $rows, $terms);
}
$tpl->assign("list", $details['list']);
$tpl->assign("list_info", $details['info']);		
//        return $details; 

$status_list = Status::getAssocList();
$communities = Community::getList();
$communities_list = Misc::keyPairs($communities['list'], 'pid', 'title');
$communities_list = Misc::stripOneElementArrays($communities_list);
$tpl->assign('status_list', $status_list);
$tpl->assign('communities_list', $communities_list);
if (is_array($communities) && isset($communities['list'][0]['pid'])) {
    $tpl->assign('communities_list_selected', $communities['list'][0]['pid']);
}
$tpl->registerNajax(NAJAX_Client::register('SelectReindexInfo', APP_RELATIVE_URL.'ajax.php'));
$tpl->onload("selectCommunity(getForm('reindex_form'), 'community_pid');");

$tpl->displayTemplate();
?>
