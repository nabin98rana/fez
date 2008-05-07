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

// get the latest version of the XSD Display for the duplicates report object 
$xdises = XSD_Display::getList(Doc_Type_XSD::getFoxmlXsdId(), "AND xdis_title='Duplicates Report'");
$xdises = Misc::keyArray($xdises, 'xdis_version');
ksort($xdises); // sort by verion
$xdis = array_pop($xdises); // get the last one
$xdis_id = $xdis['xdis_id'];
$this->xdis_id = $xdis_id;

// create the report object
$collection_pid = $this->pid; // set by the select collection step
$col_record = new RecordGeneral($collection_pid);
$access_ok = $col_record->canCreate();
if (!$access_ok) {
    echo "You are not permitted to create a duplicates report in this collection {$collection_pid}";
    exit;
}
$params = array();
$params['xdis_id'] = $xdis_id;
$params['sta_id'] = 1; // unpublished record
$params['collection_pid'] = $collection_pid;

// Just want to find the basic xsdmf_ids for the title, date and user and set them to something useful
$params['xsd_display_fields'] = array(); 
$xsd_display_fields = XSD_HTML_Match::getListByDisplay($xdis_id, array("FezACML"), array(""));  // XSD_DisplayObject
foreach ($xsd_display_fields as $dis_key => $dis_field) {
    if ($dis_field['xsdmf_element'] == '!dc:title') {
        $params['xsd_display_fields'][$dis_field['xsdmf_id']] = 'Duplicates Report '.date('r').' '.Auth::getUserFullName(); 
    } elseif ($dis_field['xsdmf_element'] == '!dc:creator') {
        $params['xsd_display_fields'][$dis_field['xsdmf_id']] = Auth::getUserFullName();
    } elseif ($dis_field['xsdmf_element'] == '!dc:creator!authorID') {
        $params['xsd_display_fields'][$dis_field['xsdmf_id']] = Auth::getUserID();
    } elseif ($dis_field['xsdmf_element'] == '!description!isMemberOf!resource') {
        $params['xsd_display_fields'][$dis_field['xsdmf_id']] = array('info:fedora/'.$collection_pid);
    }
}
$record = new RecordObject();
$pid = $record->fedoraInsertUpdate(array(),array(),$params);
$this->pid = $pid;

// The actual report is generated as a background process.
$bgp = new BackgroundProcess_GenerateDuplicatesReport;
$bgp->register(serialize(array('report_pid' => $pid, 
								'pids' => $this->pids, 
								'source_collection_pid' => $this->getvar('source_collection_pid'))), 
				Auth::getUserID(), $this->id);

$this->assign('notify', "The duplicates report has been generated.");

// Set the next workflow for the (optional) chain state which will be following.
// get the workflow trigger for the Generate Duplicates Report
$wfls = WorkflowTrigger::getListByTriggerAndXDIS_ID(-1, 'Update', $xdis_id, true);
$this->assign('chain_wft_id', $wfls[0]['wft_id']);


?>