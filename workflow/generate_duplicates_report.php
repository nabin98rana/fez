<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 10/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */

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
$bgp->register(serialize(array('report_pid' => $pid, 'pids' => $this->pids)), Auth::getUserID(), $this->id);

$this->assign('notify', "The duplicates report has been generated.");

// Set the next workflow for the (optional) chain state which will be following.
// get the workflow trigger for the Generate Duplicates Report
$wfls = WorkflowTrigger::getListByTriggerAndXDIS_ID(-1, 'Update', $xdis_id, true);
$this->assign('chain_wft_id', $wfls[0]['wft_id']);


?>