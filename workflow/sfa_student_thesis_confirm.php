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

// Email Settings
$subject = '['.APP_NAME.'] - Your submission has been completed';
$thesis_office_email = "libtheses@library.uq.edu.au";

// Set background process for sending email confirmation
$bgp = new Fez_BackgroundProcess_Sfa_ConfirmEmail();
$bgp->register(serialize(compact('pid', 'subject', 'thesis_office_email')), Auth::getUserID());


// Utilising Fez_Workflow_Sfa_Confirm class to produce a clean metadata that we can use on the template
// Instantiate Confirm class
$confirmation = new Fez_Workflow_Sfa_Confirm($pid);

// Get display data to be used by smarty template
$display_data = $confirmation->getDisplayData();

// Assigns the record title
$record_title = $confirmation->getRecordTitle();

$usrDetails = User::getDetailsByID($confirmation->record->depositor);


// Display Submission confirmation
$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'sfa_student_thesis_confirm');
$tpl->assign('application_name', APP_NAME);
$tpl->assign('record_title', $record_title);
$tpl->assign('title', $record_title);
$tpl->assign("display_data", $display_data);

$tpl->displayTemplate();

// This is a special ending workflow state -> so end the workflow manually rather than goto the redirect screen (to prevent users clicking back the browser and causing all sorts of trouble)

$wfstatus->theend(false);