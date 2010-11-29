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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.bgp_import_xsds.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "import_xsds");
$tpl->assign("active_nav", "admin");
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if (!$isSuperAdministrator) {
  $tpl->assign("show_not_allowed_msg", true);
}

$step = Misc::GETorPOST('step');
if (empty($step)) {
  $step = 1;
}

switch ($step) {
  case 1:
    // just show the form
      break;
  case 2:
    if ($_POST['cat'] == 'go') {
      extract($_FILES['import_xml']);
      if ($type != 'text/xml') {
        Error_Handler::logError(
            "Can't import files of type $type", __FILE__, __LINE__
        );
        exit;
      }
      $filename = APP_TEMP_DIR.'fezxsd'.basename($tmpName);
      copy($tmpName, $filename);
    }
    $list = Doc_Type_XSD::listImportFile($filename);
    $tpl->assign('list', $list);
    $tpl->assign('filename', $filename);
      break;
  case 3:
    if ($_POST['cat'] == 'go') {
      $filename = $_POST['filename'];
      $xdisIds = $_POST['xdis_ids'];
      $bgp = new BackgroundProcess_Import_XDSs;
      $bgp->register(
          serialize(compact('filename', 'xdisIds')), Auth::getUserID()
      );
      $feedback[] = "The XSDs are being imported as a background process, ".
        "see My_Fez for progress.";
      $tpl->assign('feedback', $feedback);
    }
      break;
}

$tpl->assign('step', $step);
$tpl->displayTemplate();
