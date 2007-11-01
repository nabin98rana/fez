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

include_once("../config.inc.php");
include_once(APP_INC_PATH.'class.template.php');
include_once(APP_INC_PATH.'class.citation.php');

// setup the template
$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");
$tpl->assign("type", "citations");

// Only Admins here pelase 
Auth::checkAuthentication(APP_SESSION);

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if (!$isSuperAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
}

$action = $_REQUEST['action'];
if (empty($action)) {
    $action = 'select_display';
}
$tpl->assign('action', $action);
switch ($action) 
{
    case 'select_display':
        $xsd_id = Doc_Type_XSD::getFoxmlXsdId();
        $list = XSD_Display::getList($xsd_id);
        foreach ($list as $key => $item) {
            $det = Citation::getDetails($item['xdis_id']);
            $template = trim($det['cit_template']);
            if (!empty($det) && !empty($template)) {
                $list[$key]['cit'] = 1; 
            }
        }
        $tpl->assign("list", $list);
    break;
    case 'edit':
        $xdis_id = $_REQUEST['xdis_id'];
        $template = $_REQUEST['template'];
        $tpl->assign('xdis_id', $xdis_id);
        $tpl->assign('template', $template);
        $xdis_details = XSD_Display::getDetails($xdis_id);
        $tpl->assign('xdis_details', $xdis_details);
        $xsdmf_list = XSD_HTML_Match::getListByDisplay($xdis_id, array('FezACML'));
        $xsdmf_list = array_filter($xsdmf_list, create_function('$a','return $a[\'xsdmf_enabled\'] && $a[\'xsdmf_show_in_view\'];'));
        $xsdmf_select_list = Misc::keyPairs($xsdmf_list, 'xsdmf_id','xsdmf_title');
        $tpl->assign("xsdmf_select_list", $xsdmf_select_list);
        $det = Citation::getDetails($xdis_id);
        if (empty($template)) {
           $template = $det['cit_template'];
        }
        if (!empty($template)) {
            $xsdmf_ids = array();
            $preview = $template;
            $preview = Citation::renderCitationTemplate($preview, $xsdmf_select_list, $xsdmf_select_list);
            $tpl->assign('preview', $preview);
        }
        $tpl->assign($det);
    break;
    case 'save':
        $xdis_id = $_REQUEST['xdis_id'];
        $template = $_REQUEST['template'];
        $res = Citation::save($xdis_id, $template);
        if ($res == 1) {
            Session::setMessage('Saved citation template');
        } else {
            Session::setMessage('Error: Citation template not saved');
        }
        Auth::redirect($_SERVER['PHP_SELF']."?action=edit&xdis_id=$xdis_id");
    break;
    case 'convert':
        $xsd_id = Doc_Type_XSD::getFoxmlXsdId();
        $list = XSD_Display::getList($xsd_id);
        foreach ($list as $key => $item) {
            $det = Citation::getDetails($item['xdis_id']);
            if (empty($det) || empty($det['cit_template'])) {
                Citation::convert($item['xdis_id']);
            }
        }
        Auth::redirect($_SERVER['PHP_SELF']."?action=select_display");
    break;
}

$tpl->displayTemplate();
?>
