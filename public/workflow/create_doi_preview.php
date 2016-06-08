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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH.'class.crossref.php');

Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
$isAdministrator = Auth::isAdministrator();



$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'create_doi_preview');
Auth::checkAuthentication(APP_SESSION);
$tpl->setAuthVars();

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$wfstatus->checkStateChange();
if (empty($wfstatus)) {
    echo "This workflow has finished and cannot be resumed";
    FezLog::get()->close();
    exit;
}

$crossref = new Crossref;
$existingDoiIfExists = $crossref->hasDoi($pid);
if ($crossref->hasDoi($pid)) {
    $tpl->assign("existingDoi", "This pid already has the doi (".$crossref->hasDoi($pid).") allocated in the doi list, Crossref will be updated with any new meta information. Information may take a while to change");
}

$record_obj = new RecordObject($pid);
$doiCurrent = $record_obj->getFieldValueBySearchKey("DOI");
if (!empty($doiCurrent[0]) && ($doiCurrent[0] != $existingDoiIfExists) ) {
    echo "This pid (".$pid.") already has a doi allocated in the record (".$doiCurrent[0].") which doesn't match our records  (".$existingDoiIfExists.") , please check if this is incorrect and remove it if so";
    FezLog::get()->close();
    exit;
}

$xdis_id = $wfstatus->getXDIS_ID();
$xdis_id_name = XSD_Display::getTitle($xdis_id);

if ($xdis_id_name != 'Thesis' && $xdis_id_name != 'Data Collection' && $xdis_id_name != 'Working Paper'&& $xdis_id_name != 'Conference Paper') {
    echo "This record is the wrong type (".$xdis_id_name.") Thesis, Data Collection, Working Paper or Conference Paper needed";
    FezLog::get()->close();
    exit;
}

$doi = ($existingDoiIfExists) ? $existingDoiIfExists : $crossref->getNextDoi();

$wfstatus->setTemplateVars($tpl);
$record = new Record($pid);
$result[0]["rek_pid"] = $pid;
$details = $record->getSearchKeysByPIDS($result, true);

$dslist = $record_obj->getDatastreams();
$warning = "<div style = 'color:red'>No open access datastreams are attached, doi will be for metadata only</div>";
foreach ($dslist as $datastream) {
    if ($datastream['controlGroup'] == 'M' && $datastream['state'] == 'A') {
        $pidPermisisons = Auth::getAuthPublic($pid, $datastream['ID']);
        if ($pidPermisisons['lister'] && $pidPermisisons['viewer']) {
            $warning = "";
        }
    }
}

foreach($result[0]['rek_author'] as $i => $author ) {
    if ($result[0]['rek_author_id'][$i]) {
        $result[0]['rek_author_firstname'][$i] = Author::getFirstname($result[0]['rek_author_id'][$i]);
        $result[0]['rek_author_lastname'][$i] = Author::getLastname($result[0]['rek_author_id'][$i]);
    } else {
        $names = Author::guessFirstLastName($author);
        $result[0]['rek_author_firstname'][$i] = $names['firstname'];
        $result[0]['rek_author_lastname'][$i] = $names['lastname'];
    }
}

$publishedDate = $result[0]['rek_date'];

if ($isAdministrator) {
    $tpl->assign("doi", $doi);
    $tpl->assign("details", $result[0]);
    $tpl->assign("xdis_title", $xdis_id_name);
    $tpl->assign("depositor_full_name", Auth::getUserFullName());
    $tpl->assign("depositor_email", Auth::getUserEmail());
    $tpl->assign("pid", $pid);
    $tpl->assign("author_firstname", $author_firstname);
    $tpl->assign("author_lastname", $author_lastname);
    $tpl->assign("published_date",$publishedDate);
    $tpl->assign("title", $record_obj->getTitle());
    $tpl->assign("link", 'http://'.APP_HOSTNAME.'/view/'.$pid);
    $tpl->assign("warning", $warning);

    $tpl->displayTemplate();
} else {
    $tpl->assign("show_not_allowed_msg", true);
}