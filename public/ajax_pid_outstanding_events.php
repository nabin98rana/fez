<?php

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

// determine if there are items currently working on this pid and let the user know if there are
$pid = $_GET['pid'];
$type = $_GET['type'];

header("Content-Type: text/javascript");

switch ($type) {

	case "BACKGROUND":
		$details = BackgroundProcessPids::getForPid($pid);

		foreach ($details as $index => $item) {
			if (strlen($item['statusMessage']) > 35) {
				$details[$index]['statusMessage'] = substr($item['statusMessage'], 0, 35) . "...";
			}
			$details[$index]['dateStarted'] = date('jS M \a\t H:i', strtotime($item['dateStarted']));
		}

		echo Zend_Json::encode($details);
		break;

	case "WORKFLOW":
		$details = WorkflowStatusStatic::getWorkflowDetailsForPid($pid);
		foreach ($details as $index => $item) {
			if (strlen($item['workflowTitle']) > 35) {
				$details[$index]['workflowTitle'] = substr($item['workflowTitle'], 0, 35) . "...";
			}
			$details[$index]['dateStarted'] = date('jS M \a\t H:i', strtotime($item['dateStarted']));
			$details[$index]['sessionLastUpdated'] = date('jS M \a\t H:i', strtotime($item['sessionLastUpdated']));
		}
		echo Zend_Json::encode($details);
		break;

	case "COUNT":
	default:
	

		$workflowsCount = WorkflowStatusStatic::getCountForPid($pid);
		$bgpsCount = BackgroundProcessPids::getCountForPid($pid);
		$fulltextQueueDetails = FulltextQueue::getDetailsForPid($pid);
		$statusString = Misc::generateOutstandingStatusString($pid);
		
		foreach ($fulltextQueueDetails as $index => $row) {
			$fulltextQueueDetails[$index]['operation'] = $row['operation'] == FulltextQueue::ACTION_INSERT ? "Insert" : "Delete";
		}
		
		$returnArray = array('outstandingWorkflows' => $workflowsCount, 'backgroundJobs' => $bgpsCount, 'fulltextQueue'=>$fulltextQueueDetails, "statusString"=>$statusString);
		echo Zend_Json::encode($returnArray);
		break;
}
