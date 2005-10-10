<?php

include_once('config.inc.php');
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.object_type.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.template.php");

$tpl = new Template_API();
$tpl->setTemplate("adv_search.tpl.html");
$list = Search_Key::getAdvSearchList();
$sta_list = Status::getAssocList();
$ret_list = Object_Type::getAssocList();
$cvo_list = Controlled_Vocab::getAssocListFullDisplay(false, "", 0, 2);
$xdis_list = XSD_Display::getAssocListDocTypes();

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$tpl->assign("isAdministrator", $isAdministrator);


foreach ($list as $list_key => $list_field) {
	if ($list_field["sek_html_input"] == 'combo' || $list_field["sek_html_input"] == 'multiple') {
		if (!empty($list_field["sek_smarty_variable"]) && $list_field["sek_smarty_variable"] != "none") {
			eval("\$list[\$list_key]['field_options'] = " . $list_field["sek_smarty_variable"] . ";");
		}
	}
	if ($list_field["sek_html_input"] == 'contvocab') {
		$list[$list_key]['field_options'] = $cvo_list[$list_field['sek_cvo_id']];
	}
	if ($list_field["sek_html_input"] == 'allcontvocab') {
		$list[$list_key]['field_options'] = array_values($cvo_list['data']);
		$list[$list_key]['cv_titles'] = array_values($cvo_list['title']);		
		$list[$list_key]['cv_ids'] = array_keys($cvo_list['title']);		
	}
}

$tpl->assign("list", $list);
$tpl->displayTemplate();
?>

