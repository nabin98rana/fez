<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
set_time_limit(0);
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.my_research.php");

if (APP_MY_RESEARCH_MODULE != 'ON') {
	die('Sorry - this module is not enabled.');
}

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$isUPO = User::isUserUPO($isUser);

$action = @$_POST['action'];
if ($isUPO && $action == 'change-user') {
	Auth::setActingUsername(@$_POST['change-user']); // Change to a new acting user
} elseif ($isUPO && $action == 'change-org-unit') {
	$_SESSION['my_researcher_aou'] = @$_POST['change-org-unit']; // Change to a new org unit
}

$tpl = new Template_API();
$tpl->setTemplate("myresearch/index.tpl.html");

MyResearch::addDatasetLink($tpl);


Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
$username = Auth::getUsername();
$actingUser = Auth::getActingUsername();
$author_id = Author::getIDByUsername($actingUser);
$actingUserArray = Author::getDetailsByUsername($actingUser);
$actingUserArray['org_unit_description'] = MyResearch::getHRorgUnit($actingUser);

/* Get &&|| set the AOU */
if (!isset($_SESSION['my_researcher_aou'])) {
	$_SESSION['my_researcher_aou'] = MyResearch::getDefaultAOU($username);
}
$currentAOU = $_SESSION['my_researcher_aou'];

//We allow admins to edit this page header, if it exits
$page = Page::getPage('my-research-header');
$zf = new Fez_Filter_RichTextHtmlpurifyWithLinks();
$page = $zf->filter($page);
if (empty($page)) {
  $page = array('content' => '');
}
$tpl->assign("headerContent", $page['content']);

$tpl->assign("type", "upo");

$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
$tpl->assign("isUPO", $isUPO);

$action = @$_POST['action'];
if ($action == 'author-search') {
	$authors = MyResearch::findAuthorsByUsername(@$_POST['username']);
	$tpl->assign("search_term", @$_POST['username']);
} else {
	$authors = MyResearch::listAuthorsByAOU($currentAOU);
}

$tpl->assign("org_units", Org_Structure::getOrgUnitList()); // All org units, for the drop-down
$tpl->assign("authors", $authors); // The authors in the currently-selected org unit
$tpl->assign("acting_user", $actingUserArray);
$tpl->assign("actual_user", $username);
$tpl->assign("current_aou", $currentAOU);

if (MyResearch::getHRorgUnit($username) == "" && !$isUPO) {
	$tpl->assign("non_hr", true); // This will cause a bail-out in template land
}

$tpl->assign("active_nav", "my_fez");

$tpl->displayTemplate();

?>
