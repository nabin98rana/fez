<?php

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

/**
 * Workflow to display confirmation message on HERDC Evidence follow-up.
 * 
 * @example: Used by WorkflowStatus->run() method.
 * When the Workflow Trigger is set to 'Auto', 
 *   this file is included by WorkflowStatus->run() method, 
 *   so $this param refers to WorkflowStatus class.
 * Otherwise, WorkflowStatus redirect the URL to this file with following GET params:
 *   $_GET['id']     = Workflow Session ID 
 *   $_GET['wfs_id'] = Workflow Status ID
 * 
 * @version 1.0, April 2012
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");


// Are you allowed to access this workflow?
Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

// Get the WorkflowStatus object from session
$wfstatus = &WorkflowStatusStatic::getSession(); 
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "herdc_evidence_followup_confirmation");

$pid = $wfstatus->pid;
$wf_id = $wfstatus->id;

$record = new RecordGeneral($pid);
$tpl->assign("title", $record->getTitle());
$tpl->assign('pid', $pid);
$wfstatus->setTemplateVars($tpl);
$wfstatus->checkStateChange();

$tpl->displayTemplate();
