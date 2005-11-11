<?php
include_once(APP_INC_PATH. 'class.batchimport.php');
$pid = $this->pid;
$xdis_id = $this->getXDIS_ID();
$wftpl = $this->getvar('template');
$objectimport = $this->getVar('batch_objectimport');
$directory = $this->getVar('batch_directory');
BatchImport::insert($objectimport, $directory, $xdis_id, $pid, $wftpl);

?>
