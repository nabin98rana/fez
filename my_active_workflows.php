<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");
$tpl->assign('myFezView', "MWF");

Auth::checkAuthentication(APP_SESSION);
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
    $tpl->assign("isFezUser", $username);
    $prefs = Prefs::get(Auth::getUserID());
} elseif ($username != ""){
// don't require registration now for logged in users, although they can (to get prefs etc) but don't force them
//  Auth::redirect(APP_RELATIVE_URL . "register.php?err=5&username=" . $username);  
}
$tpl->assign("isAdministrator", $isAdministrator);

$list = WorkflowStatusStatic::getList(Auth::getUserID());

$tpl->assign(compact('list'));
$tpl->displayTemplate();
 
?>
