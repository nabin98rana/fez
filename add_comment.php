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

include_once "config.inc.php";
include_once APP_INC_PATH . "db_access.php";
include_once APP_INC_PATH . "class.user_comments.php";
include_once APP_INC_PATH . "class.auth.php";

// bounce if not logged in

$username = Auth::getUsername();
if (empty($username)) 
{
    $errorurl = "" . APP_BASE_URL . "list.php";
    header("Location: $errorurl");
}

// grab the user comment POST vars
if (!empty($_POST['pid'])) {
    $pid = $_POST['pid'];
} else {
    $errorurl = "" . APP_BASE_URL . "list.php";
	header("Location: $errorurl");
}
$comment = $_POST['usercommenttext'];

$rating = 0;
if (isset($_POST['rating'])) {
    $rating  = $_POST['rating'];
}

// add the user comment
$uc = new UserComments($pid);
$uc->addUserComment($comment, $rating, Auth::getUserID());

if( !$uc->uploadCommentsToFedora() )
{
    Error_Handler::logError("Uploading Comments to Fedora - Failed",__FILE__,__LINE__);
}

// redirect back to the PID
$url = APP_BASE_URL . "view.php?pid=$pid";
header("Location: $url");

?>
