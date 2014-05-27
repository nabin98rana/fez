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
// |          Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.favourites.php");

$tpl = new Template_API();
$tpl->setTemplate("search_favourites.tpl.html");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']);

$tpl->assign("type", "search_favourites");

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);

$issues = array();
if ($isUser) {
    if (@count($_POST) > 0) {
        if (@count($_POST["items"]) > 0) {
            Favourites::removeSearchFavourites();
        }
        $issues = Favourites::saveSearchFavourites();
    }

    $favourite_list = Favourites::getStarredSearches();
    $tpl->assign("list", $favourite_list);
    $tpl->assign("issues", $issues);
    $tpl->assign("active_nav", "saved_search");
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
