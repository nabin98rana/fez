<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 20/06/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
$collection_pid = $this->pid;
$col_record = new RecordGeneral($collection_pid);
$pids = $this->pids;
if (empty($pids) || !is_array($pids)) {
    if (!empty($collection_pid)) {
        if ($col_record->isCollection()) {
            $this->pids = $col_record->getChildrenPids();
        }
    }
}
 
?>
