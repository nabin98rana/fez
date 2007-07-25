<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 10/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */


$report_pid = $this->pid;

// The actual report is generated as a background process.
$bgp = new BackgroundProcess_DuplicatesReportMergeIsiLoc;
$bgp->register(serialize(array('report_pid' => $report_pid)), Auth::getUserID(), $this->id);

?>