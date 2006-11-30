<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 29/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once('config.inc.php');
include_once(APP_INC_PATH.'class.sanity_checks.php');
include_once(APP_INC_PATH.'class.template.php');

$tpl = new Template_API();
$tpl->setTemplate('sanity_check.tpl.html');


Auth::checkAuthentication(APP_SESSION);


$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
    $tpl->assign("isFezUser", $username);
}
if (!$isAdministrator) {
	$tpl->assign('show_not_allowed_message', true);
} else {
    $res = SanityChecks::runAllChecks();
    $tpl->assign('sanity_results',$res);
	
}
 

$tpl->displayTemplate();



?>
