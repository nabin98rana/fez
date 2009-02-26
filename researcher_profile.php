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
// |                                                                      |
// +----------------------------------------------------------------------+
//
//
include_once('config.inc.php');
include_once(APP_INC_PATH . "class.template.php");
//include_once(APP_INC_PATH . "class.oai.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.author.php");

$tpl = new Template_API();

$tpl_file = "researcher_profile.tpl.html";
$tpl->setTemplate($tpl_file);
//Static test for now.
//$staff_ids = array('0042414', '0019904', '0030530', '0038034', '0008872', '0032765', '0009029', '0072870', '0052278', '0020332');
$staff_ids = array('0042414', '0019904', '0030530', '0038034', '0008872', '0032765', '0009029', '0052278', '0020332'); // without pw
$list = Author::getListByStaffIDList(0, 25, 'aut_lname', $staff_ids);

$tpl->assign("list", $list['list']);
$tpl->assign("app_admin_email", APP_ADMIN_EMAIL);
$tpl->assign("org_name", APP_ORG_NAME);
header("Content-type: text/xml");
//print_r($list); exit;
$tpl->displayTemplate();



?>
