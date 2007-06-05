<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 16/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
$notify = $this->getvar('notify');
$wfses_id = $this->id;

$tpl = new Template_API();
$tpl->setTemplate('workflow/emails/notify_done.tpl.txt');

$tpl->assign(compact('notify','wfses_id'));
$email_txt = $tpl->getTemplateContents();

$mail = new Mail_API;
$mail->setTextBody($email_txt);
$subject = '['.APP_NAME.'] Workflow notification';
$from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
$to = User::getFromHeader(Auth::getUserID());
$mail->send($from, $to, $subject, false);
 
 
?>
