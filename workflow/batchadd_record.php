<?php
include_once(APP_INC_PATH. 'class.batchadd.php');
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_batchadd_record.php");

$pid = $this->pid;
$xdis_id = $this->getXDIS_ID();
$wftpl = $this->getvar('template');
$temp_files = $this->getVar('files');
if (is_array($temp_files)) {
	$files = array();
	$username = Auth::getUsername();
	foreach ($temp_files as $t_file) {
		$t2_file = APP_SAN_IMPORT_DIR.$username."/".$t_file;
		if (is_file($t2_file)) {
			array_push($files, $t2_file);
		}
	}
	$bgp_batch = new BackgroundProcess_BatchAdd_Record;
	$inputs = compact('files', 'xdis_id', 'pid', 'wftpl');
	$inputs_str = serialize($inputs);
	$bgp_batch->register($inputs_str, Auth::getUserID());
}

?>
