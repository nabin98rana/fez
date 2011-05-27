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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+
//
//
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author_affiliations.php");
include_once(APP_INC_PATH . "class.main_chapter.php");
include_once(APP_INC_PATH . "class.db_api.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

if (APP_HERDC_SUPPORT !== 'ON') {
    die("<b>Error:</b> HERDC Support is not currently enabled. You can turn this feature on in Site Configuration.");
}

$tpl->assign("type", "integrity");
$tpl->assign("active_nav", "admin");
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator) {
    if (@$_POST["action"] == "report") {
		$tpl->assign("action", "report");
		$tpl->assign("orphaned_affiliations", AuthorAffiliations::getOrphanedAffiliationsAll());
		$tpl->assign("bad_sums", AuthorAffiliations::getBadSums());
		$tpl->assign("orphaned_main_chapters", MainChapter::getOrphanedMainChaptersAll());
    } else {
		$tpl->assign("action", "prompt");
	}
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->assign("cycle_colours", "#" . APP_CYCLE_COLOR_TWO . ",#FFFFFF");
$tpl->displayTemplate();
?>