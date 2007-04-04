<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 27/03/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */

$pid = $this->pid;
$new_xdis_id =  $this->getvar('new_xdis_id');
$is_succession =  $this->getvar('is_succession');
$clone_attached_datastreams = $this->getvar('clone_attached_datastreams');

$record = new RecordGeneral($pid);
$new_pid = $record->copyToNewPID($new_xdis_id,$is_succession,$clone_attached_datastreams);
if (!empty($new_pid)) {
    $this->setCreatedPid($new_pid);
    $this->assign('outcome', "Success");
    $this->assign('outcome_details', "Cloned from $pid to $new_pid");
}

    
?>