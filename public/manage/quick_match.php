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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>,       |
// +----------------------------------------------------------------------+
//
//
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.reports.php");
include_once(APP_INC_PATH . "class.matching.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "quick_match");
$tpl->assign("active_nav", "admin");
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

$worked = true;

if (!isAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
} else {
    if($_POST['submit'] == 'Save') {
        if ( strtolower($_GET['match_type']) == 'conference') {
            $worked = Matching::addConferenceMapping($_POST['pid'], $_POST['matching_id'], '2015');
        } else if (strtolower($_GET['match_type']) == 'journal') {
            $worked = Matching::addJournalMapping($_POST['pid'], $_POST['matching_id'], '2015');
        }
        $tpl->assign("previous_pid", $_POST['pid']);
        $tpl->assign("previous_match", $_POST['matching_id']);
    }
}

$match_type = "none!!!!";

if ( strtolower($_GET['match_type']) == 'conference') {
    $match_type = "Conference";
} else if (strtolower($_GET['match_type']) == 'journal') {
    $match_type = "Journal";
}

$tpl->assign("match_type", $match_type);
$tpl->assign("worked", $worked);
$tpl->displayTemplate();
