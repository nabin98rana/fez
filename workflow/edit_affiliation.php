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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author_affiliations.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "edit_author_affiliations");

$pid = $wfstatus->pid;
$wfstatus->setTemplateVars($tpl);
$wf_id = $wfstatus->id;
$wfstatus->checkStateChange();

$record = new RecordObject($pid);

$access_ok = $record->canEdit();
if ($access_ok) {
	
	if ($_POST['action'] == 'save') {
		$saveResult = AuthorAffiliations::save($_POST['af_id'], $pid, $_POST['af_author_id'], $_POST['af_percent_affiliation'], $_POST['af_org_id']);
		$validateResult = AuthorAffiliations::validateAffiliations($pid);
		if ($saveResult != -1) {
			Auth::redirect(APP_BASE_URL.'workflow/edit_affiliation.php?id='.$wf_id);
		} else {
            $tpl->assign("error_message", "Error on save of author affiliation");
		}
	} elseif ($_REQUEST['action'] == 'delete') {
		AuthorAffiliations::remove($_REQUEST['af_id']);
		$validateResult = AuthorAffiliations::validateAffiliations($pid);
	}
	
	// get list of authors for this pid
	$authors = Misc::array_flatten($record->getFieldValueBySearchKey('Author'),'',true);
	$author_ids = Misc::array_flatten($record->getFieldValueBySearchKey('Author ID'),'',true);
	// remove blank author ids
	foreach ($authors as $key => $author) {
		if (empty($author_ids[$key])) {
			unset($authors[$key]);
			unset($author_ids[$key]);
		}
	}

	$suggestseans = array();
	$autOrgUnitCount = array();
	foreach ($author_ids as $key => $author_id) {
		$affiliationData = AuthorAffiliations::getPresetAffiliations($author_id);
		foreach ($affiliationData as $affiliationRecord) 
		{
		    if(array_key_exists($affiliationRecord['org_id'], $autOrgUnitCount))
		    {
		        $autOrgUnitCount[$affiliationRecord['org_id']] = $autOrgUnitCount[$affiliationRecord['org_id']] + 1;
		    }
		    else 
		    {
		        $autOrgUnitCount[$affiliationRecord['org_id']] = 1;
		    }
			array_push($suggestseans, $affiliationRecord);
		}
	}

	$tpl->assign('affiliation_suggestions', $suggestseans);
	$tpl->assign("cycle_colours", "#" . APP_CYCLE_COLOR_TWO . ",#FFFFFF");
	$authors = array_values($authors);
	$author_ids = array_values($author_ids);
	$list = AuthorAffiliations::getList($pid, 1);
	$problem_list = AuthorAffiliations::getList($pid, 0);
	$problem_list = array_merge($problem_list, AuthorAffiliations::getOrphanedAffiliations($pid));
	$listAll = AuthorAffiliations::getListAll($pid);
	$list_keyed = Misc::keyArray($listAll, 'af_id');
	$tpl->assign('orgs', Org_Structure::getAssocListHR());

	if ($_REQUEST['action'] == 'edit') {
		$tpl->assign('current', $list_keyed[$_REQUEST['af_id']]);
		$tpl->assign('action', 'edit');
	}
	
    $tpl->assign('autOrgUnitCount', $autOrgUnitCount);
	$tpl->assign(compact('list','authors','author_ids','wf_id', 'problem_list'));
	
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
