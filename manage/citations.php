<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/03/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
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
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
if (!$isAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
    $tpl->displayTemplate();
    exit;
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
            if (!empty($det)) {
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
