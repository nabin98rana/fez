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
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.select_collection.php");

NAJAX_Server::allowClasses('SelectCollection');
if (NAJAX_Server::runServer()) {
	exit;
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "select_collection");
$tpl->assign("type_name", "Select Collection");

Auth::checkAuthentication(APP_SESSION);
$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session

$wfstatus->setTemplateVars($tpl);
$cat = Misc::GETorPOST('cat');
if ($cat == 'submit') {
    $wfstatus->pid = Misc::GETorPOST('collection_pid');
    $wfstatus->parent_pid = Misc::GETorPOST('community_pid');
}
$wfstatus->checkStateChange();

// Find collections that the current user can create records in and then list the parent communities.
$communities = Community::getList(0,1000);
$communities_list = Misc::keyPairs($communities['list'], 'pid', 'title');
$communities_list = Misc::stripOneElementArrays($communities_list);
$communities_pids = array_keys($communities_list);
$collection_list = Collection::getEditList();

$communities_list2 = array();
foreach ($collection_list as &$item) {
   $parents = Misc::keyPairs(Collection::getParents2($item['pid']),'pid','title');
   foreach ($parents as $parent_pid => $parent_title) {
       if (in_array($parent_pid, $communities_pids)) {
           $communities_list2[$parent_pid] = $communities_list[$parent_pid];
       }
   }
}
$communities_list = $communities_list2;
$tpl->assign('communities_list', $communities_list);
$tpl->assign('communities_list_selected', $communities['list'][0]['pid']);
$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->registerNajax( NAJAX_Client::register('SelectCollection', 'select_collection.php'));

$tpl->displayTemplate();
?>
