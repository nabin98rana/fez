<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 28/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once(APP_INC_PATH. 'class.auth.php');
include_once(APP_INC_PATH . "class.bgp_index_object.php");

$pid = $this->pid;
$index_type = Reindex::INDEX_TYPE_REINDEX_OBJECTS;
$inputs = compact('pid','index_type');
$inputs_str = serialize($inputs);
$bgp = new BackgroundProcess_Index_Object;
$bgp->register($inputs_str, Auth::getUserID());

?>
