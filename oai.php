<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
include_once('config.inc.php');
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.oai.php");
include_once(APP_INC_PATH . "class.record.php");

$tpl = new Template_API();
$verb = trim($_GET['verb']);
$metadataPrefix = trim($_GET['metadataPrefix']);
$originalIdentifier = trim($_GET['identifier']);
$identifier = str_replace("oai:".APP_HOSTNAME.":", "", $originalIdentifier);
$from = trim($_GET['from']);
$until = trim($_GET['until']);
$originalSet = trim($_GET['set']);
if (is_numeric(strpos($originalSet, ":cvo_id:"))) {
	$setType = "contvocab";
	$set = substr($originalSet, (strrpos($originalSet, ":")+1));	
} else {
	$setType = "isMemberOf";
	$set = str_replace("oai:".APP_HOSTNAME.":", "", $originalSet);	
	$setObject = new RecordObject($set);

}


$querystring = $_GET;
$acceptable_vars = array("verb", "metadataPrefix", "identifier", "from", "until", "resumptionToken", "set");

$identify_acceptable_vars = array("verb");
$list_metadata_formats_acceptable_vars = array("verb", "identifier");
$list_records_acceptable_vars = $acceptable_vars;
$list_identifiers_acceptable_vars = $acceptable_vars;
$get_record_acceptable_vars = array("verb", "metadataPrefix", "identifier");
$list_sets_acceptable_vars = array("verb", "metadataPrefix", "resumptionToken");

$errors = array();
$i_errors = array();
$querystring = Misc::parse_str_ext($_SERVER["QUERY_STRING"]);


$tpls = array(
    'ListRecords' => array('file' => 'oai/ListRecords.tpl.html', 'title' => 'ListRecords'),
    'ListMetadataFormats' => array('file' => 'oai/ListMetadataFormats.tpl.html', 'title' => 'ListMetadataFormats'),    
    'ListSets' => array('file' => 'oai/ListSets.tpl.html', 'title' => 'ListSets'),    
    'ListIdentifiers' => array('file' => 'oai/ListIdentifiers.tpl.html', 'title' => 'ListIdentifiers'),            
    'GetRecord' => array('file' => 'oai/GetRecord.tpl.html', 'title' => 'GetRecord'),                
    'Identify' => array('file' => 'oai/Identify.tpl.html', 'title' => 'Identify')    
);

if (array_key_exists($verb, $tpls)) {
	$tpl_file = $tpls[$verb]['file'];
} else {
	$tpl_file = $tpls["Identify"]['file'];	
}
$tpl->setTemplate($tpl_file);

$tpl->assign('tpl_list', array_map(create_function('$a','return $a[\'title\'];'), $tpls));
$responseDate = Date_API::getFedoraFormattedDateUTC();
$rows=100;
$pagerRow = $HTTP_GET_VARS["resumptionToken"] ? $HTTP_GET_VARS["resumptionToken"] : 0;
$resumptionToken = $pagerRow;
//echo $resumptionToken; exit;
if ($resumptionToken != "") {
	$matches = preg_match("/^(\d+)\/(\d+)\/(.*)?$/", $resumptionToken);
	if (!$matches) {
		$errors["code"][] = "badResumptionToken";
		$errors["message"][] = "Token is invalid (does not match regexp)";		
	} else {
		$metadataPrefix = substr($pagerRow, strrpos($pagerRow, "/")+1);
		$pagerRow = substr($pagerRow, 0, strpos($pagerRow, "/"));
	}
} elseif ($resumptionToken != 0) {
	$pagerRow = substr($pagerRow, 0, strpos($pagerRow, "/"));
}
$collection_pid = "";
$order_by = "";

if (!empty($verb)) {

		switch ($verb) {
			case "ListMetadataFormats":
				foreach ($querystring as $qname => $qvalue) {
					if (!in_array($qname, $list_metadata_formats_acceptable_vars)) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}	
				if ($identifier != "") {
					if (preg_match("/^oai:[a-zA-Z][a-zA-Z0-9\-]*(\.[a-zA-Z][a-zA-Z0-9\-]+)+:[a-zA-Z0-9\-_\.!~\*'\(\);\/\?:\@\&=\+\$,\%]+$/", $originalIdentifier)) {
						// then check the record exists
						$list = OAI::ListRecords($set, $identifier, $pagerRow, $rows, $order_by, $from, $until, $setType);
						$list = $list["list"];
						if (count($list) < 1) {
							$errors["code"][] = "idDoesNotExist";
							$errors["message"][] = "ID: ".$identifier." does not exist in this archive";
						}											
					} else {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "identifier does not match regexp: ".$originalIdentifier;							
					}					
				}
				// nothing needs to be done here as all only have standard oai_dc at the moment statically set in the smarty template, MODS later perhaps.
				break;		
			case "ListSets":
				foreach ($querystring as $qname => $qvalue) {
					if (!in_array($qname, $list_sets_acceptable_vars) || ($resumptionToken != "" && $qname == "metadataPrefix")) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}	
			
				// Need to list all collections and controlled vocabularies			
				if (!empty($metadataPrefix)) {
					
				}
				if (count($errors) > 0) {
					break;
				}
				$list = OAI::ListSets($pagerRow, $rows, $order_by, $from, $until);
				$list_info = $list["info"];
				$list = $list["list"];		
				$tpl->assign("list", $list);
				$tpl->assign("list_count", count($list));					
				$tpl->assign("resumptionToken", $resumptionToken);
				break;		
			case "GetRecord":
				foreach ($querystring as $qname => $qvalue) {
					if (!in_array($qname, $get_record_acceptable_vars)) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}				
				if ($metadataPrefix != "") {
					if ($metadataPrefix == "oai_dc") {
						if ($identifier != "") {
							if (preg_match("/^oai:[a-zA-Z][a-zA-Z0-9\-]*(\.[a-zA-Z][a-zA-Z0-9\-]+)+:[a-zA-Z0-9\-_\.!~\*'\(\);\/\?:\@\&=\+\$,\%]+$/", $originalIdentifier)) {
								$list = OAI::ListRecords($set, $identifier, $pagerRow, $rows, $order_by, $from, $until, $setType);
								$list_info = $list["info"];
								$list = $list["list"];
								$tpl->assign("list", $list);
								$tpl->assign("list_count", count($list));					
								$tpl->assign("resumptionToken", $resumptionToken);
								if (count($list) < 1) {
									$errors["code"][] = "idDoesNotExist";
									$errors["message"][] = "ID: ".$identifier." does not exist in this archive (at least at your security level).";
								}							
							} else {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "identifier does not match regexp: ".$originalIdentifier;							
							}
						} else {
							$errors["code"][] = "badArgument";
							$errors["message"][] = "Missing required argument: identifier";				
						}
					} else {
						$errors["code"][] = "cannotDisseminateFormat";
						$errors["message"][] = "Record not available as metadata type: ".$metadataPrefix;									
					}
				} else {
					$errors["code"][] = "badArgument";
					$errors["message"][] = "Missing required argument: metadataPrefix";				
				}			
				break;					
			case "Identify":
				foreach ($querystring as $qname => $qvalue) {
					if (!in_array($qname, $identify_acceptable_vars)) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}	

				// nothing needs to be done here
				break;
			case "ListIdentifiers":
				foreach ($querystring as $qname => $qvalue)  {
					if (!in_array($qname, $list_identifiers_acceptable_vars) || ($resumptionToken != "" && $qname == "metadataPrefix")) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}	
				if (count($errors) > 0) {
					break;
				}
				if ($metadataPrefix != "") {
					if ($metadataPrefix == "oai_dc") {
						if (!empty($set)) {
							if ((!Controlled_Vocab::exists($set) && $setType == "contvocab") || (($setType == "isMemberOf") && ($setObject->checkExists()))) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "Invalid set parameter; unknown key (".set.")";
								break;
							}
						}		
						if (!empty($from)) {
							if (!preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d(\.\d+)?)Z?$/", $from)) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "not valid datetime: ".$from;
							}
						}
						if (!empty($until)) {
							if (!preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d(\.\d+)?)Z?$/", $until)) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "not valid datetime: ".$until;
							}
						}					
						// probably first need to check that the set exists if not empty					
						$list = OAI::ListRecords($set, $identifier, $pagerRow, $rows, $order_by, $from, $until, $setType);
						$list_info = $list["info"];
						$list = $list["list"];		
						$tpl->assign("list", $list);
						$tpl->assign("list_count", count($list));					
						$tpl->assign("resumptionToken", $resumptionToken);
						if (count($list) == 0) {
							$errors["code"][] = "noRecordsMatch";
							$errors["message"][] = "No published items match (at least at your security level).";
						}												
					} else {
						$errors["code"][] = "cannotDisseminateFormat";
						$errors["message"][] = "Record not available as metadata type: ".$metadataPrefix;									
					}
				} else {
					$errors["code"][] = "badArgument";
					$errors["message"][] = "Missing required argument: metadataPrefix";				
				}			
				break;					
			case "ListRecords":
				foreach ($querystring as $qname => $qvalue) {
					if (!in_array($qname, $list_records_acceptable_vars) || ($resumptionToken != "" && $qname == "metadataPrefix")) {
						$errors["code"][] = "badArgument";
						$errors["message"][] = "Illegal argument: ".$qname;
					}
				}	
				if (count($errors) > 0) {
					break;
				}				
				if ($metadataPrefix != "") {
					if ($metadataPrefix == "oai_dc") {
						if (!empty($set)) {
							if ((!Controlled_Vocab::exists($set) && $setType == "contvocab") || (($setType == "isMemberOf") && (!$setObject->isCollection()))) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "Invalid set parameter; unknown key (".$set.")";
								break;
							}
						}
						if (!empty($from)) {
							if (!preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d(\.\d+)?)Z?$/", $from)) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "not valid datetime: ".$from;
							}
						}
						if (!empty($until)) {
							if (!preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d(\.\d+)?)Z?$/", $until)) {
								$errors["code"][] = "badArgument";
								$errors["message"][] = "not valid datetime: ".$until;
							}
						}					
						// probably first need to check that the set exists if not empty
						$list = OAI::ListRecords($set, $identifier, $pagerRow, $rows, $order_by, $from, $until, $setType);
						$list_info = $list["info"];
						$list = $list["list"];		
						$tpl->assign("list", $list);
						$tpl->assign("list_count", count($list));					
						$tpl->assign("resumptionToken", $resumptionToken);
						if (count($list) == 0) {
							$errors["code"][] = "noRecordsMatch";
							$errors["message"][] = "No published items match (at least at your security level).";
						}																	
					} else {
						$errors["code"][] = "cannotDisseminateFormat";
						$errors["message"][] = "Record not available as metadata type: ".$metadataPrefix;									
					}
				} else {
					$errors["code"][] = "badArgument";
					$errors["message"][] = "Missing required argument: metadataPrefix";				
				}
				break;		
			default:
				$errors = array();
				$errors["code"][] = "badVerb";
				$errors["message"][] = "Unknown verb: '".$verb."'";
				break;
			
		}
	
} else {
	$errors = array();
	$errors["code"][] = "badVerb";
	$errors["message"][] = "No verb was specified";
}
if (count($errors) == 0) {
	foreach ($querystring as $qname => $qvalue) {
		if (count($qvalue) > 1) {
			$errors["code"][] = "badArgument";
			$errors["message"][] = "Repeated argument: ".$qname;							
		} 
	}
}
$tpl->assign("pagerRow", $pagerRow);
$tpl->assign("rows", $rows);
$tpl->assign("verb", $verb);
$tpl->assign("app_pid_namespace", APP_PID_NAMESPACE);
$tpl->assign("app_admin_email", APP_ADMIN_EMAIL);
$tpl->assign("app_hostname", APP_HOSTNAME);
$tpl->assign("metadataPrefix", $metadataPrefix);
$tpl->assign("errorCount", count($errors["code"]));
$tpl->assign("errors", $errors);
$tpl->assign("responseDate", $responseDate);
header("Content-type: text/xml");
$tpl->displayTemplate();



?>
