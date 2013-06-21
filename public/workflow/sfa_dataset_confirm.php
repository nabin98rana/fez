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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");


// Authentication check
Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

// Checks Workflow status
$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$wfstatus->checkStateChange();
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

// Get PID
$pid = $wfstatus->pid;
$record = new RecordObject($pid);
$record->getObjectAdminMD();
$title = $record->getTitle();
$view_record_url = 'http://'.APP_HOSTNAME.APP_RELATIVE_URL."view/".$pid;
// Email Settings
$usrDetails = User::getDetailsByID($record->depositor);

$tplEmail = new Template_API();
$tplEmail->setTemplate('workflow/emails/sfa_dataset_confirm.tpl.txt');
$tplEmail->assign('application_name', APP_NAME);
$tplEmail->assign('title', $title);
$tplEmail->assign('name', $usrDetails['usr_full_name']);
$tplEmail->assign('view_record_url', $view_record_url);

$email_txt = $tplEmail->getTemplateContents();

//Mail the depositor
$mail = new Mail_API;
$mail->setTextBody(stripslashes($email_txt));
$subject = '['.APP_NAME.'] - Your dataset submission has been completed';
$from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
$to = $usrDetails['usr_email'];
$mail->send($from, $to, $subject, false);

//Mail the dataset team
$mail->setTextBody(stripslashes($email_txt));
$subject = '['.APP_NAME.'] - Dataset Submission: '.$usrDetails['usr_full_name'].' :: '.$pid.' :: '.$title;
$from = $usrDetails['usr_email'];
$to = APP_EVENTUM_NEW_DATASET_JOB_EMAIL_ADDRESS;
$mail->send($from, $to, $subject, false);


// Display Submission confirmation
$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'sfa_dataset_confirm');
$tpl->assign('application_name', APP_NAME);
$tpl->assign('record_title', $record_title);
$tpl->assign('title', $record_title);
$tpl->assign("display_data", $display_data);
$tpl->assign('view_record_url', $view_record_url);

$tpl->displayTemplate();

// This is a special ending workflow state -> so end the workflow manually rather than goto the redirect screen (to prevent users clicking back the browser and causing all sorts of trouble)

$wfstatus->theend(false);