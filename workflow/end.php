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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'end');

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$tpl->assign("isAdministrator", $isAdministrator);


// sometime in the future, this page should display a summary of the process record produced by the 
// workflow just finished - we would supply the PID and process record ID in the GET params.
// TODO: implement process records for workflows!

$wfl_title = Misc::GETorPOST('wfl_title');
$wft_type = Misc::GETorPOST('wft_type');
$parent_pid = Misc::GETorPOST('parent_pid');
$action = Misc::GETorPOST('action');
$href = Misc::GETorPOST('href');
$href_title = substr($href, 0, strpos($href, "?"));
$href_title = basename($href_title, ".php");
$href_title = ucwords(str_replace('_', ' ', basename($href_title, ".php")));

$tpl->assign('href',$href);
if ($href) {
    $tpl->assign('refresh_rate', 5);
    $tpl->assign('refresh_page', substr($href,strlen(APP_RELATIVE_URL)));
}

$parents_list = unserialize(Misc::GETorPOST('parents_list'));

if (is_array($parents_list)) {
    foreach ($parents_list as &$item) {
        if (Misc::isValidPid($item)) {
            $precord = new RecordObject($item);
            if ($precord->isCommunity()) {
                $item['url'] = APP_RELATIVE_URL."community/".$item;
            } else {
                $item['url'] = APP_RELATIVE_URL."collection/".$item;
            }
        }
    }
}
$pid = Misc::GETorPOST('pid');
if ($wft_type != 'Delete') {
    $view_record_url = APP_RELATIVE_URL."view/".$pid;
    if (Misc::isValidPid($pid)) {
        $record = new RecordObject($pid);
        if ($record->isCommunity()) {
            $view_record_url = APP_RELATIVE_URL."community/".$pid;
        } elseif ($record->isCollection()) {
            $view_record_url = APP_RELATIVE_URL."collection/".$pid;
        }
        $record_title = $record->getTitle();
    } else {
        $record_title = $pid;
    }
}
$parent_title = '';
if ($parent_pid) {
    if ($parent_pid == -1 || $parent_pid == -2) {
        $view_parent_url = APP_RELATIVE_URL."list.php";
        $parent_title = "Repository";
    } else {
        if (Misc::isValidPid($parent_pid)) {
            $precord = new RecordObject($parent_pid);
            if ($precord) {
                if ($precord->isCommunity()) {
                    $view_parent_url = APP_RELATIVE_URL."community/".$parent_pid;
                } else {
                    $view_parent_url = APP_RELATIVE_URL."collection/".$parent_pid;
                }
                $parent_title = $precord->getTitle();
            } else {
                $parent_title = $parent_pid;
            }
        }
    }
} 
$tpl->assign(compact('wfl_title','wft_type','parent_title','record_title', 'view_record_url', 
            'view_parent_url', 'parents_list', 'action', 'href_title'));


$tpl->displayTemplate();


?>
