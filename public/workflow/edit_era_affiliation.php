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
include_once(APP_INC_PATH . "class.author_era_affiliations.php");
include_once(APP_INC_PATH . "class.record.php");

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    exit;
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "edit_author_era_affiliations");

$pid = $wfstatus->pid;
$wfstatus->setTemplateVars($tpl);
$wf_id = $wfstatus->id;
$wfstatus->checkStateChange();

$record = new RecordObject($pid);

$access_ok = $record->canEdit();
if ($access_ok) {

	// get list of authors for this pid
	$authors = author_era_affiliations::getAuthorsAll($pid);//Misc::array_flatten($record->getFieldValueBySearchKey('Author'),'',true);

    $tpl->assign('era_affiliation_list', author_era_affiliations::getAssocListEraAffiliation());
	$tpl->assign("cycle_colours", "#" . APP_CYCLE_COLOR_TWO . ",#FFFFFF");

	$tpl->assign('authors',$authors);
    $tpl->assign('wf_id',$wf_id);
    $title = Record::getTitleFromIndex($authors[0]['pid']);
    $tpl->assign('title', $title);

    $pidDetails = Record::getDetailsLite($pid);
    $tpl->assign('herdc_notes', $pidDetails[0]['rek_herdc_notes']);

    $xdis_id = $record->getXmlDisplayId();
    $tpl->assign('xdis_id', $xdis_id);

    $datastreams = Fedora_API::callGetDatastreams($pid, null , 'A');
    $datastreamLinks = array();
    foreach ($datastreams as $ds_key => $ds) {
        if (!empty($datastreams[$ds_key]['location'])) {
            $datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);
            array_push($datastreamLinks,$datastreams[$ds_key]);
        }
    }
    $tpl->assign('links', $datastreamLinks);

    //Find link to HERDC edit
    $tpl->assign('hLink', author_era_affiliations::returnHERDCLink($xdis_id));


} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->assign("submit_to_popup", true);

$tpl->displayTemplate();

?>
