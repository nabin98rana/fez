<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.bgp_split_collection.php');

$collection_pid = $this->pid;

$bgp = new BackgroundProcess_Split_Collection();
$bgp->register(serialize(compact('collection_pid')), Auth::getUserID());

?>