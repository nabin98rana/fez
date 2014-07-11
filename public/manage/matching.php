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
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.language.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.matching.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.conference.php");
include_once(APP_INC_PATH . "class.journal.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$tpl->assign("type", "matching");

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
$tpl->assign("active_nav", "admin");

$pagerRow = Pager::getParam('pagerRow',$params);
if (empty($pagerRow)) {
    $pagerRow = 0;
}
$rows = Pager::getParam('rows',$params);
if (empty($rows)) {
    $rows = APP_DEFAULT_PAGER_SIZE;
}
$options = Pager::saveSearchParams($params);
$tpl->assign("options", $options);

if ($isAdministrator) {

    $type = Misc::GETorPOST('type');
    $action = Misc::GETorPOST('action');

    if ($action == 'save') {
        Matching::add();
    }

    if ($action == 'edit') {
        $recordDetails = Record::getDetailsLite(Misc::GETorPOST('pid'));
        $pid = $recordDetails[0]['rek_pid'];
        $tpl->assign("pid", $pid);
        if ($type == 'C') {
            $mapping = Record::getRankedConference($pid);
            $listing = Conference::getConferences();
        } elseif ($type == 'J') {
            $mapping = Record::getRankedJournal($pid);
            $listing = Journal::getJournals('2014');
        }
        $tpl->assign("mapping", $mapping);
        $tpl->assign("list", $listing);
        $tpl->assign("citation", $recordDetails[0]['rek_citation']);
        $tpl->assign("show", "edit-screen");
        

        
    } elseif ($action == 'new') {
        $message = '';
        $pid = Misc::GETorPOST('pid');
        $recordDetails = Record::getDetailsLite($pid);
        $tpl->assign("pid", $pid);
        
        if (empty($recordDetails)) {
            $message = "The PID you entered could not be found. Click back and ensure you have entered a valid PID.";
        }
        
        if ($type == 'C') {
            $mapping = Record::getRankedConference($pid);
            $listing = Conference::getConferences();
        } elseif ($type == 'J') {
            $mapping = Record::getRankedJournal($pid);
            $listing = Journal::getJournals('2014');
        }
        
        if (isset($mapping['status'])) {
            $message = "Cannot create a mapping for this PID, as one already exists. Return to the previous screen to edit it.";
        } 
        
        $tpl->assign("mapping", $mapping);
        $tpl->assign("list", $listing);
        $tpl->assign("citation", $recordDetails[0]['rek_citation']);
        $tpl->assign("message", $message);
        $tpl->assign("show", "new-screen");
        
    } elseif ($action == 'add') {
        Matching::add();
        
    } elseif ($action == 'search') {
        $filter = Pager::getParam('search_filter',$params);
        $tpl->assign("search_filter", $filter);
        $matches = Matching::getAllMatches($pagerRow, $rows, $filter);
        $tpl->assign("matches", $matches['list']);
        $tpl->assign("list_info", $matches['list_info']);
        
    } else {
        $matches = Matching::getAllMatches($pagerRow, $rows);
        $tpl->assign("matches", $matches['list']);
        $tpl->assign("list_info", $matches['list_info']);
    }
    
    $tpl->assign("match_type", $type);
    
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
