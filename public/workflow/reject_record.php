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
include_once(APP_INC_PATH . "class.api.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

$pid = $wfstatus->pid;
$record = new RecordObject($pid);

$record->getObjectAdminMD();
$usrDetails = User::getDetailsByID($record->depositor);

$tpl = new Template_API();

if (APP_API) {
    // If there is a workflow specified honour that and try to populate the email finalisation message.
    if ($_REQUEST['workflow_val'] == 'Reject Finalise' && (HTTP_METHOD == 'POST')) {
        API::populateRejectionEmail();
    } else {
        $tpl->setTemplate("workflow/reject.tpl.xml");
    }
} else {
    $tpl->setTemplate("workflow/index.tpl.html");
}
$tpl->assign("type", "reject_record");

$wfstatus->setTemplateVars($tpl);
$wfstatus->checkStateChange();

$tplEmail = new Template_API();
$tplEmail->setTemplate('workflow/emails/reject.tpl.txt');
$tplEmail->assign('application_name', APP_NAME);
$tplEmail->assign('title', $record->getTitle());
$tplEmail->assign('name', $usrDetails['usr_full_name']);

$email_txt = $tplEmail->getTemplateContents();

$tpl->assign('email_body', $email_txt);
$tpl->displayTemplate();
