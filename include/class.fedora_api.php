<?php 

/***************** Fedora API calls ******************/
/*
This code has many functions that use the nusoap class files
and instantiate a new soapclient object for the Fedora API-A(access)
or API-M(management) of Fedora Objects. There is also a debugInfo($client)
function at the bottom of this page that can be used with any of the SOAP
Fedora API functions.

Written by Elly Cramer 2004 - elly@cs.cornell.edu
Modifiend into PHP 5 Class form for Fez by Christiaan Kortekaas 2005 - c.kortekaas@library.uq.edu.au
*/
//global vars for baseURL, xsl_path, and fedora are set in cms.inc
// which is included in top.php
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.misc.php");
require_once(APP_INC_PATH . "nusoap.php");
include_once(APP_INC_PATH . "class.foxml_template.php");
include_once(APP_PEAR_PATH . "/HTTP/Request.php");

//@define("APP_FEDORA_UPLOAD_URL", "http://".APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_BASE_FEDORA_DOMAIN."/management/upload");

class Fedora_API {

// Check to be sure the server is up and running!
function checkFedoraServer() {
//        global $sysEmail, $baseFedoraDomain;
        $ch = curl_init("http://".APP_BASE_FEDORA_DOMAIN."/search");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        if ($info['http_code'] == '200') {
            //proceed!!
        } else {
			$errMsg = 'It appears that the content repository server is down, Please try again later.';
			Error_Handler::logError(array($errMsg, $info), __FILE__, __LINE__);
        }
}

function URLopen($url)
{
       // Fake the browser type
       ini_set('user_agent','MSIE 4\.0b2;');
       $dh = fopen("$url",'r');
       $result = fread($dh,8192);                                                                                                                             
       return $result;
}


function getNextPID() {
   //creates a new pid for ingest, used by callIngestObject().
//   global $fedoraAdmin, $fedoraPwd, $xsl_path, $baseFedoraDomain;
	$pid = false;
//	$getString = "http://".APP_BASE_FEDORA_DOMAIN."/mgmt/getNextPID?xml=true";
//	$getString = "http://".APP_BASE_FEDORA_DOMAIN."/mgmt/getNextPID?xml=true";
//	$getString = "http://".APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_BASE_FEDORA_DOMAIN."/management/getNextPID?xml=true";
//	$getString = "http://".APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_BASE_FEDORA_DOMAIN."/mgmt/getNextPID?xml=true";
	$getString = "http://".APP_BASE_FEDORA_DOMAIN."/mgmt/getNextPID?xml=true";
	//: http://hostname:port/fedora/management/getNextPID
////	$xml = file_get_contents(urlencode($getString));
//	$getString = "http://www.library.uq.edu.au/";
//	$xml = Fedora_API::URLopen($getString);
	$http_req = new HTTP_Request($getString, array("http" => "1.0"));
	$http_req->setBasicAuth(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
	$http_req->setMethod("GET");
	$http_req->sendRequest();
//	$xml = $http_req->getResponseHeader();
//	$xml = $http_req->getResponseCode();
//	$xml = $http_req->_response;
	$xml = $http_req->getResponseBody();

//	$xml = file_get_contents($getString);
//	echo "getString -> ".$getString;
//	echo "end result xml -> "; print_r($xml);
   //$dom = domxml_open_mem($xml); // This might not work in php5, might need to replace with domdocument instead
	$dom = new DomDocument; 
	$dom->loadXML($xml); // Now this works with php5 - CK 7/4/2005
//echo "root element -> ".$dom->documentElement;
//   $node_array = $dom->getElementsByTagname("pid");
	$result = $dom->getElementsByTagName("pid");
//	echo "hmm"; print_r($result); 
//	echo "length -> ".$result->length;
	for ($i = 0; $i < $result->length; $i++) {
//	   echo $result->item($i)->nodeValue . "\n";    
	}
	$getString = "http://".APP_BASE_FEDORA_DOMAIN."/mgmt/getNextPID?xml=true";
	//: http://hostname:port/fedora/management/getNextPID
////	$xml = file_get_contents(urlencode($getString));
//	$getString = "http://www.library.uq.edu.au/";
//	$xml = Fedora_API::URLopen($getString);
	$http_req = new HTTP_Request($getString, array("http" => "1.0"));
	$http_req->setBasicAuth(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
	$http_req->setMethod("GET");
	$http_req->sendRequest();
//	$xml = $http_req->getResponseHeader();
//	$xml = $http_req->getResponseCode();
//	$xml = $http_req->_response;
	$xml = $http_req->getResponseBody();

	//echo count($result);
    //print_r($result->item(0));
	foreach($result as $item) {
//		echo "in";
//		print_r($item);
		//echo $item->nodeValue;
		$pid = $item->nodeValue;
		break;
	}



//   $node = $node_array['0'];
//   $pid = $node->saveXML();
   //$pid = ($doc->root->children['pid']->content);
   return $pid;
}


function getObjectXMLByPID($pid) {
    $parms = array('PID' => $pid);
    $result = Fedora_API::openSoapCall('getObjectXML', $parms);
    return $result;
}


function callIngestObject($foxml) {
   /*********************************************
   * This function ingests an object using getFoxml
   * a function in foxmlTemplate.php
   * (used to be metsxml_template.php)
   * and base64 encodes it.
   *********************************************/
//   global $_REQUEST, $baseURL;
      //Set Parm values.
//      $pid = Fedora_API::getNextPID();
//echo "AND THE NEXT PID IS -> ".$pid;
//      $foxml = Foxml_Template::getFoxml($_REQUEST, $pid);
      $foxml = base64_encode($foxml);
      $logmsg = 'Fedora Object ingested from php web form';
      $parms=array(new soapval("XML","base64Binary",$foxml), 'format' => 'foxml1.0', 'logMessage' => $logmsg);
      //Call ingestObject and set $pid and $file
      $pid = Fedora_API::openSoapCall('ingest', $parms);
//	  echo "PID RETURNED -> "; print_r($pid);
//      header("Location: ".APP_BASE_URL."/view.php?pid=$pid&message=1");

 
}

function callModifyObject ($pid, $label) {
   /********************************************
   * This function modifies the object
   * and the system DC and NSDL_DC datastreams.
   * It changes the object label or the state = A, I, or D.
   ********************************************/
//   global $_REQUEST, $baseURL;
   global $_REQUEST;
//   include('foxmlTemplate.php');
   $formFieldArray = $_REQUEST;
   if (empty($_REQUEST['label'])) {
      $desc = $_REQUEST['description'];
      header("Location: ".APP_BASE_URL."/packages.php?PID=$pid&mode=form&requiredTitle=warning&desc=$desc");
   } else {
      $logmsg = 'Modifying object info';
      $parms= array('PID' => $pid, 'state' => $_REQUEST['state'], 'label' => $label, 'logMessage' => $logmsg);
      Fedora_API::openSoapCall('modifyObject', $parms);
      $dsContentDC = Foxml_Template::getDC($formFieldArray, 'oai_dc', $pid);
      $dsContentNSDL_DC = Foxml_Template::getDC($formFieldArray, 'nsdl_dc', $pid);
      Fedora_API::callModifyDatastreamByValue($pid, 'DC', $dsContentDC);
      Fedora_API::callModifyDatastreamByValue($pid, 'NSDL_DC', $dsContentNSDL_DC);
      header("Location: ".APP_BASE_URL."/packages.php?PID=$pid&form=displayObj&message=2");
   }
}

// CK - 25/2/2005 - Added from index.php list records code
function getListObjectsXML($searchTerms, $maxResults, $returnfields=null) {
    $resultlist = array();

    $searchTerms = urlencode("*$searchTerms*"); // encode it for url parsing

    if (empty($returnfields)) {
        $returnfields = array('pid', 'title', 'identifier', 'description', 'type');
    }
    $fieldPhrase = '';
    foreach ($returnfields as $rField) {
        $fieldPhrase .= "&$rField=true";
    }
    
    $searchPhrase = "?xml=true$fieldPhrase&terms=$searchTerms";

    $filename = APP_FEDORA_SEARCH_URL.$searchPhrase;
    $xml = file_get_contents($filename);
    $xml = preg_replace("'<object uri\=\"info\:fedora\/(.*)\"\/>'", "<pid>\\1</pid>", $xml); // fix the pid tags

    $doc = DOMDocument::loadXML($xml);
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('r', 'http://www.fedora.info/definitions/1/0/types/');
    $objectFieldsNodeList = $xpath->query('/r:result/r:resultList/r:objectFields');
    // loop through the objectFields elements
    foreach ($objectFieldsNodeList as $objectFieldsNode) {
        // look for the return fields and group them in an array
        foreach ($returnfields as $rfield) {
            $rFieldNodeList = $objectFieldsNode->getElementsByTagName($rfield);
            if ($rFieldNodeList->length) {
                $rItem[$rfield] = trim($rFieldNodeList->item(0)->nodeValue);
            }
        }
        // add the return fields array to out list of results
        $resultlist[] = $rItem;
    }
    return $resultlist;
}

function getITQLQuery($itql, $returnfields) {

	$searchPhrase = "";

	$itql = urlencode($itql); // encode it for url parsing

        // create the fedora web service URL query string to run the ITQL
	$searchPhrase = "?type=tuples&lang=itql&format=Sparql&limit=1000&dt=on&query=".$itql;

        // format the return fields URL query string
    // Should abstract the below for into a function in here
    $stringfields = array();
    for($x=0;$x<count($returnfields);$x++) {
     $stringfields[$x] = $returnfields[$x] . "=true";
    }
    $stringfields = join("&", $stringfields);

    // do the query - we are querying the fedora web service here (need to be able to open an URL as a file)
    $filename = APP_FEDORA_RISEARCH_URL.$searchPhrase;
//	echo $filename;
    $xml = file_get_contents($filename);
	$xml = preg_replace("'<object uri\=\"info\:fedora\/(.*)\"\/>'", "<pid>\\1</pid>", $xml); // fix the pid tags

        // The query has returned XML. Parse the xml into a DOMDocument
    $doc = DOMDocument::loadXML($xml);

        $resultlist = array();

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('r', 'http://www.w3.org/2001/sw/DataAccess/rf1/result');
    $resultNodeList = $xpath->query('/r:sparql/r:results/r:result');
    // loop through results to assemble the result list array
    foreach ($resultNodeList as $resultNode) {
        // use first item in returnfield as key to resultlist
        // probably the pid
        $rkeyName = $returnfields[0];
        $rkeyValue = $resultNode->getElementsByTagName($rkeyName)->item(0)->nodeValue;
        $rItem = &$resultlist[$rkeyValue];
        // pick out the result fields we are interested in
        foreach ($returnfields as $returnField) {
            $returnFieldNodes = $resultNode->getElementsByTagName($returnField);
            if ($returnFieldNodes->length) {
                $rValue = trim($returnFieldNodes->item(0)->nodeValue);
                if (!empty($rValue)) {
                    // Where there are multiple results in the same field, merge them
                    if (isset($rItem[$returnField])) {
                        if (is_array($rItem[$returnField])) {
                            // If we already have arrayed results for this item, then just add to the array
                            $rItem[$returnField][] = $rValue;
                        } else {
                            // if we don't have arrayed results already, then decide whether to make them into an array
                            if ($rItem[$returnField] != $rValue) {
                                // if the value isn't the same as the one there, then make an array
                                $prevValue = $rItem[$returnField];
                                $rItem[$returnField] = array($prevValue, $rValue);
                            } 
                            // else do nothing (item is the same as already found)
                        }
                    } else {
                        // there is no previous value for this return field so just set it
                        $rItem[$returnField] = $rValue;
                    }
                }
            }
        }
    }
    // strip the keys out
    $resultlist = array_values($resultlist);

    return $resultlist;
}


//@@@ CK - 4/5/2005 - Added for Collection:getListingAssoc
function getListByTypeObjectsXMLAssoc($type) {
//    $username = 'example_username';
	$searchPhrase = "";
	// Type contains Fez_Collection
	$searchPhrase = "query=type~".$type;
    $returnfields = array();
    array_push($returnfields, "pid");
    array_push($returnfields, "title");
    // Should abstract the below for into a function in here
    $stringfields = array();
    for($x=0;$x<count($returnfields);$x++) {
     $stringfields[$x] = $returnfields[$x] . "=true";
    }
    $stringfields = join("&", $stringfields);

    $filename = APP_FEDORA_SEARCH_URL."?xml=true&".$stringfields."&".$searchPhrase;
//	echo $filename;
    $xml = file_get_contents($filename);
    $doc = DOMDocument::loadXML($xml);
    $dom_array = array();
    Misc::dom_to_simple_array($doc, $dom_array);

    $resultlist = array();
    for ($x=0;$x<count($dom_array['result'][0]['resultList'][0]['objectFields']);$x++) {
		foreach ($returnfields as $rfield) {
	    	$resultlist[$dom_array['result'][0]['resultList'][0]['objectFields'][$x][$returnfields[0]][0]['cdata']] = $dom_array['result'][0]['resultList'][0]['objectFields'][$x][$rfield][0]['cdata'];
		}
    }
    return ($resultlist);
}


//@@@ CK - 4/5/2005 - Added for Collection:getList
function getListByTypeObjectsXML($type) {
//    $username = 'example_username';
	$searchPhrase = "";
	// Type contains Fez_Collection
	$searchPhrase = "query=type~".$type;
    $returnfields = array();
    array_push($returnfields, "pid");
    array_push($returnfields, "title");
    // Should abstract the below for into a function in here
    $stringfields = array();
    for($x=0;$x<count($returnfields);$x++) {
     $stringfields[$x] = $returnfields[$x] . "=true";
    }
    $stringfields = join("&", $stringfields);

    $filename = APP_FEDORA_SEARCH_URL."?xml=true&".$stringfields."&".$searchPhrase;
//	echo $filename;
    $xml = file_get_contents($filename);
    $doc = DOMDocument::loadXML($xml);
    $dom_array = array();
    Misc::dom_to_simple_array($doc, $dom_array);

    $resultlist = array();
    for ($x=0;$x<count($dom_array['result'][0]['resultList'][0]['objectFields']);$x++) {
      foreach ($returnfields as $rfield) {
    	$resultlist[$x][$rfield] = $dom_array['result'][0]['resultList'][0]['objectFields'][$x][$rfield][0]['cdata'];
      }
    }
    return ($resultlist);
}


// CK - 3/3/2005 - Added for Record::getDetails 
function getObjectXML($pid) {
    $searchPhrase = "terms=".$pid;
//    $username = 'Wild Monkeyzor';
    $returnfields = array();
    array_push($returnfields, "pid");
    array_push($returnfields, "label");
    array_push($returnfields, "title");
    array_push($returnfields, "state");
    array_push($returnfields, "description");
    array_push($returnfields, "creator");
    array_push($returnfields, "date");
    array_push($returnfields, "identifier");
    array_push($returnfields, "subject");
    array_push($returnfields, "contributor");
    // Should abstract the below foreach into a function in here
    $stringfields = array();
    for($x=0;$x<count($returnfields);$x++) {
     $stringfields[$x] = $returnfields[$x] . "=true";
    }
    $stringfields = join("&", $stringfields);
    //$filename =  APP_FEDORA_SEARCH_URL."?xml=true&maxResults=1&terms=$pid&pid=true&cmodel=true&title=true&description=true&subject=true&label=true&date=true&state=true&contributor=true&subject=true&identifier=true";

    $filename = APP_FEDORA_SEARCH_URL."?xml=true&maxResults=1"."&".$stringfields."&".$searchPhrase;
    $xml = file_get_contents($filename);
    $doc = DOMDocument::loadXML($xml);
    $resultlist = array();
    // Find all the object Fields
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('r', 'http://www.fedora.info/definitions/1/0/types/');
    $objectFieldsNodeList = $xpath->query('/r:result/r:resultList/r:objectFields');
    // loop through the objectFields elements
    foreach ($objectFieldsNodeList as $objectFieldsNode) {
        // look for the return fields and group them in an array
        foreach ($returnfields as $rfield) {
            $rFieldNodeList = $objectFieldsNode->getElementsByTagName($rfield);
            if ($rFieldNodeList->length) {
                $rItem[$rfield] = trim($rFieldNodeList->item(0)->nodeValue);
            }
        }
        // add the return fields array to out list of results
        $resultlist[] = $rItem;
    }
    return $resultlist;
}


function searchObjectXML($pid, $cmodel=NULL, $mode, $desc=NULL) {
   /**********************************************
   * This function displays a web form via an xsl.
   * It displays the values with the xml results
   * if pid already exists or
   * form fields if new.
   *********************************************/
//    global $xsl_path, $baseFedoraDomain, $searchURL;
//    $realName = $_SESSION['nsdlDisplayName']; // CK - will have to replace this
	  $realName = "test";
//    if (authorizedUser($pid)) $authenticated = 'TRUE';
     $authenticated = 'TRUE';
    //calls the content form or the exhibit view form
    //call the display xsl or the form xsl.
    switch($mode) {
        case 'form':
           $xsl = 'editObj.xsl';
           break;
        case 'display':
           $xsl = 'displayObj.xsl';
           break;
        default:
           $xsl = 'displayObj.xsl';
           break;
    }
    //Need to call an empty xml doc for new entries.
    if ($pid == NULL) {
        $url = APP_FEDORA_SEARCH_URL."?xml=true";
    } else { //otherwise get the xml results for the pid's fedora object
        $url = APP_FEDORA_SEARCH_URL."?xml=true&maxResults=1&terms=$pid&pid=true&cmodel=true&title=true&description=true&subject=true&label=true&date=true&state=true&contributor=true&subject=true&identifier=true";
//        $url = "$searchURL?xml=true&maxResults=1&terms=$pid&pid=true&cmodel=true&title=true&description=true&subject=true&label=true&date=true&state=true&contributor=true";
    }
//      echo $url;
    //processing the xml results thru an xslt processor
    $xml=file_get_contents($url);

    //for local test purposes, remove next line
    $xml_groups=file_get_contents(APP_XSL_PATH."groups.xml"); // CK - might have to comment this out later..
    // Allocate a new XSLT processor
    $xh = Misc::xslt_create();
//    $arguments = array( '/_xml' => $xml, '/_XML_GROUPS' => getUserGroupsXML());
    $arguments = array( '/_xml' => $xml, '/_XML_GROUPS' => $xml_groups);
//      $arguments = "";

    $parms = array("XML_GROUPS" => 'arg:/_XML_GROUPS', "desc" => $desc, "baseFedoraDomain" => APP_BASE_FEDORA_DOMAIN, "realName" => $realName, 'authenticated' => $authenticated);

    if ($result = Misc::xslt_process($xh, 'arg:/_xml', APP_XSL_PATH.$xsl, NULL, $arguments, $parms)) {
//    if ($result = xslt_process($xh, 'arg:/_xml', "$xsl_path/$xsl", NULL, $arguments, $parms)) {
//    if ($result = xslt_process($xh, 'arg:/_xml', "$xsl_path/$xsl")) {
       echo $result;
    } else {
       $errMsg = "Sorry, the XSLT transformation failed. "
       . "The reason is that " . Misc::xslt_error($xh) . " and the "
       . "error code is " . Misc::xslt_errno($xh);
	   Error_Handler::logError(array($errMsg, ""), __FILE__, __LINE__);
    }
    Misc::xslt_free($xh);
}



function callPurgeObject($pid) {
   /********************************************
   * This function deletes the object and
   * all its datastreams.
   ********************************************/
//   global $_REQUEST, $baseURL;
   //purgeObject(PID, logmessage)
   $logmsg = 'Fedora Object Purged using Fez';
   $parms=array('PID' => $pid, 'logMessage' => $logmsg, 'force' => false);
   Fedora_API::openSoapCall('purgeObject', $parms);
   //return to index with pid status.
//   header("Location: $baseURL/index.php?PID=$pid&message=3&terms=$terms");
//   header("Location: http://130.102.44.8/cornell/index.php?PID=$pid&message=3&terms=$terms");
    return 1;
}

function getUploadLocation ($pid, $dsIDName, $file, $dsLabel, $mimetype='text/xml', $controlGroup='M', $dsID=NULL) {
   /********************************************
   * This function uses curl to upload a file into
   * the fedora upload manager and calls the
   * the addDatastream or modifyDatastream as needed.
   ********************************************/
//   global $_REQUEST, $uploadURL;

	$loc_dir = "";
    if (!is_numeric(strpos($dsIDName, "/"))) {
		$loc_dir = APP_TEMP_DIR;
	}
//	$mimetype = mime_content_type($loc_dir.$dsIDName);

	if ($mimetype == 'text/xml') {		
		$config = array(
          'indent'         => true,
          'input-xml'   => true,
          'output-xml'   => true,
          'wrap'           => 200);

		$tidy = new tidy;
		$tidy->parseString($file, $config, 'utf8');
		$tidy->cleanRepair();
		$file = $tidy;
	}


	if (!empty($file) && (trim($file) != "")) {
	//	if (is_string($file)) {
			$fp = fopen($loc_dir.$dsIDName, "w"); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
//			$fp = fopen(APP_TEMP_DIR."temp.txt", "w");
			fwrite($fp, $file);
			fclose($fp);
	//	}	
	   //Send multipart/form-data via curl
	//   $ch = curl_init("http://dev-fez.library.uq.edu.au/list.php");
	   $ch = curl_init(APP_FEDORA_UPLOAD_URL);
	   curl_setopt($ch, CURLOPT_VERBOSE, 1);
	   curl_setopt($ch, CURLOPT_HEADER, 0);
//	   curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => "@".APP_TEMP_DIR."temp.txt"));

   	   curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => "@".$loc_dir.$dsIDName)); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
	   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   $uploadLocation = curl_exec($ch);
	//   echo "\n\n".   curl_error($ch) . "\n\n";
	   curl_close ($ch);
	
	   $uploadLocation = trim(str_replace("\n", "", $uploadLocation));
	
	   if ($dsID == NULL) {
		  //Call callAddDatastream
		  $dsID = Fedora_API::callCreateDatastream ($pid, $dsIDName, $uploadLocation, $dsLabel, $mimetype, $controlGroup);
		  return $dsID;
		  exit;
	   } elseif ($dsID != NULL) {
		  //Call ModifyDatastreamByReference
	//	  Fedora_API::callModifyDatastreamByReference ($pid, $dsID, $dsLabel, $uploadLocation);
	   }
	}
}

function getUploadLocationByLocalRef ($pid, $dsIDName, $local_file_location, $dsLabel, $mimetype, $controlGroup='M', $dsID=NULL) {
   /********************************************
   * This function uses curl to upload a file into
   * the fedora upload manager and calls the
   * the addDatastream or modifyDatastream as needed.
   ********************************************/
//   global $_REQUEST, $uploadURL;

	$loc_dir = "";
//	echo "DS ID NAME = ".$dsIDName;
    if (is_numeric(strpos($dsIDName, "/"))) {
		$dsIDName = substr($dsIDName, strrpos($dsIDName, "/")+1); // take out any nasty slashes from the ds name itself
		if ($mimetype == "") {
			$mimetype = Misc::mime_content_type($local_file_location);
		}

	} else {
		$loc_dir = APP_TEMP_DIR;
		if ($mimetype == "") {
			$mimetype = Misc::mime_content_type($loc_dir.$dsIDName);
		}

	}
	$dsIDName = str_replace(" ", "_", $dsIDName);
	if (is_numeric(strpos($dsIDName, "."))) {
		$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
		$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
	}

//	echo "DS ID NAME = ".$dsIDName;
//	echo $loc_dir.$dsIDName;
/*	if ($mimetype == "") {
		$mimetype = Misc::mime_content_type($loc_dir.$dsIDName);
	}*/

	if (!empty($local_file_location) && (trim($local_file_location) != "")) {
	   //Send multipart/form-data via curl
	   $ch = curl_init(APP_FEDORA_UPLOAD_URL);
	   curl_setopt($ch, CURLOPT_VERBOSE, 1);
	   curl_setopt($ch, CURLOPT_HEADER, 0);
   	   curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => "@".$loc_dir.$local_file_location)); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
	   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   $uploadLocation = curl_exec($ch);
	//   echo "\n\n".   curl_error($ch) . "\n\n";
	   curl_close ($ch);
	
	   $uploadLocation = trim(str_replace("\n", "", $uploadLocation));

	   if (!Fedora_API::datastreamExists($pid, $dsIDName)) {
		  //Call callAddDatastream
		  $dsID = Fedora_API::callCreateDatastream ($pid, $dsIDName, $uploadLocation, $dsIDName, $mimetype, $controlGroup);
		  return $dsID;
		  exit;
	   } elseif ($dsIDName != NULL) {
		  //Call ModifyDatastreamByReference
		  Fedora_API::callModifyDatastreamByReference ($pid, $dsIDName, $dsLabel, $uploadLocation);
	   }
	}
}


function getUploadInfo ($externalURL) {
        $ch = curl_init("$externalURL");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        $mimetype = $info['content-type'];
        return $mimetype;
}

function callAddXMLDatastream ($pid, $dsID, $dsContent, $dsLabel, $dsState, $mimetype, $controlGroup='M') {
   /********************************************
   * This function adds datastreams to object $pid.
   ********************************************/
//   global $_REQUEST;
//	$controlGroup='R';
//    $parms=array('PID' => $pid, 'dsLabel' => $dsLabel, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', new soapval("dsContent","base64Binary",$dsContent), 'controlGroup' => $controlGroup, 'dsState' => $dsState);

//   $parms=array('PID' => $pid, 'dsLabel' => $dsLabel, 'versionable' => true, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', 'dsLocation' => 'http://eprint.uq.edu.au/perl/oai2/?verb=Identify', 'controlGroup' => $controlGroup, 'dsState' => $dsState);
//   $parms=array('pid' => $pid, 'dsID' => 'FezMD',   'dsState' => $dsState, 'MIMEType' => 'image/jpeg', 'controlGroup' => $controlGroup, 'logMessage' => 'test add of datatstream', 'logMessage' => 'test add of datatstream', 'dsLocation' => 'http://dev-espace.library.uq.edu.au/images/2003/uq_logo.gif');
//   $parms=array('pid' => $pid, 'altIDs' => array(), 'dsID' => $dsIDName, 'dsLabel' => $dsLabel, 'versionable' => true, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', new soapval('location', 'string', $uploadLocation), 'controlGroup' => $controlGroup, 'dsState' => 'A', 'logMessage' => 'test add of datatstream');
//   $parms=array('pid' => $pid, 'dsLabel' => $dsLabel, new soapval('versionable', 'boolean', 'TRUE'), 'MIMEType' => $mimetype, 'formatURI' => 'unknown', 'dsLocation' => 'http://eprint.uq.edu.au/perl/oai2/?verb=Identify', 'controlGroup' => $controlGroup, 'dsState' => $dsState);
//   $parms=array('pid' => $pid, 'dsLabel' => $dsLabel, new soapval('versionable', 'boolean', 'TRUE'), 'MIMEType' => $mimetype, 'controlGroup' => $controlGroup, 'dsState' => $dsState);
   $parms=array('PID' => $pid, 'altIDs' => array(), 'dsID' => $dsID, 'dsLabel' => $dsLabel, 'versionable' => true,  'MIMEType' => $mimetype, 'formatURI' => 'unknown', 'dsLocation' => APP_FEDORA_GET_URL."/".$pid."/".$dsID, 'controlGroup' => $controlGroup, 'dsState' => $dsState, 'logMessage' => 'Added new datastream from Fez');
//	echo "add xml ds params -> ";
//	print_r($parms);
   //Call addDatastream
   $result = Fedora_API::openSoapCall('addDatastream', $parms);
//   echo "add xml ds result -> "; print_r($result);
//   Fedora_API::callModif
}


function callAddDatastream ($pid, $dsID, $uploadLocation, $dsLabel, $dsState, $mimetype, $controlGroup='M') {
   /********************************************
   * This function adds datastreams to object $pid.
   ********************************************/
//   global $_REQUEST;
	if ($mimetype == "") {
		$mimetype = "text/xml";
	}
//	echo "upload Loc -> ";
//	print_r($uploadLocation); echo "<-";
//   $parms=array('PID' => $pid, 'dsLabel' => $dsLabel, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', new soapval('location', 'string', $uploadLocation), 'controlGroup' => $controlGroup, 'dsState' => 'A');
//   $parms=array('PID' => $pid, 'dsID' => 'Link_ChangeMe', 'altIDs' => array(), 'dsLabel' => $dsLabel, 'versionable' => false, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', new soapval('location', 'string', $uploadLocation), 'controlGroup' => $controlGroup, 'dsState' => 'A', 'logMessage' => 'Added Datastream');
   $parms=array('PID' => $pid, 'dsID' => $dsID, 'altIDs' => array(), 'dsLabel' => $dsLabel, 'versionable' => false, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', new soapval('location', 'string', $uploadLocation), 'controlGroup' => $controlGroup, 'dsState' => 'A', 'logMessage' => 'Added Datastream');//
//   $parms= array('pid' => $pid, 'dsID' => $dsID, 'altIDs' => array(), 'dsLabel' => $label, 'versionable' => $versionable, 'MIMEType' => $mimetype, 'formatURI' => 'unknown',  new soapval("dsContent","base64Binary",$dsContent), 'dsState' => $state, 'logMessage' => $logmsg, 'force' => true);
   //Call addDatastream
   Fedora_API::openSoapCall('addDatastream', $parms);
//   print_r($parms);
}

function callCreateDatastream ($pid, $dsIDName, $uploadLocation, $dsLabel, $mimetype, $controlGroup='M') {
   /********************************************
   * This function adds datastreams to object $pid.
   ********************************************/
//   global $_REQUEST;
//new soapval('dsLocation', 'string', $uploadLocation)
   $parms=array('PID' => $pid, 'dsID' => $dsIDName, 'altIDs' => array(), 'dsLabel' => $dsLabel, 'versionable' => true, 'MIMEType' => $mimetype, 'formatURI' => 'unknown', 'dsLocation' => $uploadLocation, 'controlGroup' => $controlGroup, 'dsState' => 'A', 'logMessage' => 'Added new datastream from Fez');


//   echo "callCreateDS params -> ";
//   print_r($parms);
   //Call createDatastream
   $dsID = Fedora_API::openSoapCall('addDatastream', $parms);
//   $dsID = Fedora_API::openSoapCall('createDatastream', $parms);
   // COMMENT for generic interface!!  -elly
   // Currently working on this -elly
   switch ($mimetype) {
      case 'application/msword':
         if ($dissListArray = Fedora_API::callGetDisseminators($pid)) {
            while (list($arrayID, $dissFieldArray) = each($dissListArray)) {
               reset($dissFieldArray);
               if (!in_array('DocConverter', $dissFieldArray)) {
                  Fedora_API::callAddDisseminator($pid, $dsID, 'nsdl:998', 'nsdl:999', 'DocConverter', 'DC');
               }
            }
         }
         break;
      //add more mimetype cases as needed.
   }
   return $dsID;
}

function callGetDatastreams ($pid) {
   /********************************************
   * This function creates an array of all
   * the datastreams for a specific object.
   ********************************************/
//   $parms=array('PID' => $pid);
   $parms=array('pid' => $pid);
   //Call addDatastream
   $dsIDListArray = Fedora_API::openSoapCall('getDatastreams', $parms);
   sort($dsIDListArray);
   reset($dsIDListArray);
   return $dsIDListArray;
}

function callGetDatastream ($pid, $dsID) {
   /********************************************
   * This function creates an array of all
   * the datastreams for a specific object.
   ********************************************/
//   $parms=array('PID' => $pid);
   $parms=array('pid' => $pid, 'dsID' => $dsID);
   //Call addDatastream
   $dsIDListArray = Fedora_API::openSoapCall('getDatastream', $parms);
//   sort($dsIDListArray);
//   reset($dsIDListArray);
   return $dsIDListArray;
}

function datastreamExists ($pid, $dsID) {
	$dsExists = false;
	$rs = Fedora_API::callGetDatastreams($pid, $dsID);
	foreach ($rs as $row) {
		if (isset($row['ID']) && $row['ID'] == $dsID) {
			$dsExists = true;
		}
	} 
	return $dsExists;
}

function callGetDatastreamDissemination($pid, $dsID, $asofDateTime="") {
   /********************************************
   * This function creates an array of all
   * the datastreams for a specific object.
   ********************************************/
//   $parms=array('PID' => $pid);
//	   $parms=array('pid' => $pid);
   if ($asofDateTime == "") {
	   $parms=array('pid' => $pid, 'dsID' => $dsID);
	} else {
	   $parms=array('pid' => $pid, 'dsID' => $dsID, 'asofDateTime' => $asofDateTime);
	} 
   //Call addDatastream
   $dsIDListArray = Fedora_API::openSoapCallAccess('getDatastreamDissemination', $parms);

//   sort($dsIDListArray);
//   reset($dsIDListArray);
   return $dsIDListArray;
}


function callGetDatastreamContents($pid, $dsID) {
    $resultlist = array();

    $filename = APP_FEDORA_GET_URL."/".$pid."/".$dsID;
    $xml = @file_get_contents($filename);
    if (!empty($xml)) {
        $doc = DOMDocument::loadXML($xml);
        $xpath = new DOMXPath($doc);
        $fieldNodeList = $xpath->query("/$dsID/*");
        foreach ($fieldNodeList as $fieldNode) {
	        $resultlist[$fieldNode->nodeName][] = trim($fieldNode->nodeValue);
        }
    }
    return $resultlist;
}


function callGetDatastreamContentsField($pid, $dsID, $returnfields) {
//    array_push($returnfields, "pid"); 
    $resultlist = array();

    $filename = APP_FEDORA_GET_URL."/".$pid."/".$dsID;
    $xml = @file_get_contents($filename);
    if (!empty($xml)) {
        $doc = DOMDocument::loadXML($xml);
        $xpath = new DOMXPath($doc);
        $fieldNodeList = $xpath->query("/$dsID/*");
        foreach ($fieldNodeList as $fieldNode) {
            if (in_array($fieldNode->nodeName, $returnfields)) {
                $resultlist[$fieldNode->nodeName][] = trim($fieldNode->nodeValue);
            }
        }
    }
    return $resultlist;
}

function callGetDatastreamField ($pid, $dsID, $field) {
   /********************************************
   * This function gets a value for specific datastream
   * fields - dsLabel, dsState, mimetype.
   ********************************************/
//   global $_REQUEST;
   $parms= array('PID' => $pid, 'dsID' => $dsID, 'asOfDateTime' => NULL);

   //Call getDatastream by reference of value.
   $dsIDFieldArray = Fedora_API::openSoapCall('getDatastream', $parms);
   reset ($dsIDFieldArray);

   $dsLabel = $dsIDFieldArray['label'];
   $mimetype = $dsIDFieldArray['MIMEType'];
   $dsState = $dsIDFieldArray['state'];
   $controlGroup = $dsIDFieldArray['controlGroup'];
   $dsLocation = $dsIDFieldArray['location'];
   if ($field == 'dsLabel') return $dsLabel;
   if ($field == 'mimetype') return $mimetype;
   if ($field == 'dsState') return $dsState;
   if ($field == 'controlGroup') return $controlGroup;
   if ($field == 'dsLocation') return $dsLocation;
}

function callModifyDatastreamByValue ($pid, $dsID, $state, $label, $dsContent, $mimetype='text/xml', $versionable=true) {
   /********************************************
   * This function modifies inline xml datastreams (ByValue),
   * specifically the system DC and NSDL_DC.
   ********************************************/
//   global $_REQUEST;

	if ($mimetype == 'text/xml') {		
		$config = array(
          'indent'         => true,
          'input-xml'   => true,
          'output-xml'   => true,
          'wrap'           => 200);

		$tidy = new tidy;
		$tidy->parseString($dsContent, $config, 'utf8');
		$tidy->cleanRepair();
		$dsContent = $tidy;
	}

   $dsContent = base64_encode(trim($dsContent));
//   $dsContent = base64_encode($dsContent);

//   $dsContent = urlencode(trim($dsContent));
   $logmsg = 'Modifying datastream from Fez';

	if (empty($versionable)) {
		$versionable = true;
	}
	if ($versionable == "true") {
		$versionable = true;
	} elseif ($versionable == "false") {
		$versionable = false;
	}

//   $dsLabel = 'Empty';
//   if ($dsID == 'DC') $dsLabel = 'Dublin Core Metadata';
//   if ($dsID == 'NSDL_DC') $dsLabel = 'NSDL_DC Metadata';
//   $parms= array('PID' => $pid, 'datastreamID' => $dsID, 'dsLabel' => $label, 'logMessage' => $logmsg, new soapval("dsContent","base64Binary",$dsContent), 'dsState' => $state);
   $parms= array('pid' => $pid, 'dsID' => $dsID, 'altIDs' => array(), 'dsLabel' => $label, 'versionable' => $versionable, 'MIMEType' => $mimetype, 'formatURI' => 'unknown',  new soapval("dsContent","base64Binary",$dsContent), 'dsState' => $state, 'logMessage' => $logmsg, 'force' => true);
//	print_r($parms);
//   $parms= array('pid' => $pid, 'dsID' => $dsID,  new soapval("dsContent","base64Binary",$dsContent));
   //Call modifyDatastream by reference or value.
//   print_r($parms);
   Fedora_API::openSoapCall('modifyDatastreamByValue', $parms);
}

function callModifyDatastreamByReference ($pid, $dsID, $dsLabel, $dsLocation=NULL) {
   /********************************************
   * This function modifies non-in-line datastreams,
   * either a chunk o'text, a url, or a file.
   ********************************************/
   global $_REQUEST;
   $logmsg = 'Modifying datastream by reference';
   $parms= array('PID' => $pid, 'datastreamID' => $dsID, 'versionable' => true, 'dsLabel' => $dsLabel, 'logMessage' => $logmsg, 'dsLocation' => $dsLocation, 'dsState' => 'A');
   //Call modifyDatastream by reference or value.
   Fedora_API::openSoapCall('modifyDatastreamByReference', $parms);
}

function callPurgeDatastream ($pid, $dsID, $endDT=NULL, $logMessage="Purged Datastream from Fez", $force=false) {
   /********************************************
   * This function deletes a datastream
   ********************************************/
//   global $_REQUEST, $baseURL;

   $parms= array('PID' => $pid, 'dsID' => $dsID, 'endDT' => $endDT, 'logMessage' => $logMessage, 'force' => $force);
   return Fedora_API::openSoapCall('purgeDatastream', $parms);
//   header("Location: $baseURL/packages.php?PID=$pid&form=displayObj&message=6");
}

function callAddDisseminator ($pid, $dsID, $bDefPID, $bMechPID, $dissLabel, $key) {
   /********************************************
   * This function adds a disseminator
   * exmple function parameters:
   * callAddDisseminator ('nsdl:1031', 'DC', 'nsdl:998', 'nsdl:999', 'DocConverter', 'DC')
   ********************************************/
   global $_REQUEST;
   //Builds a four level namespaced typed array.
   //$dsBindings[] is used by $bindingMap,
   //which is used by the soap call $parms.
   $dsBindings[0] = array('bindKeyName' => $key,
                'bindLabel' => $dissLabel,
                'datastreamID' => $dsID,
                'seqNo' => '0');
   $bindingMap = array('dsBindMapID'  => 'foo',
                'dsBindMechanismPID' => $bMechPID,
                'dsBindMapLabel' => 'Label for dsBindMapLabel',
                'state' => 'A',
                new soapval('dsBindings', 'DatastreamBinding', $dsBindings[0], '', 'http://www.fedora.info/definitions/1/0/types/'));
   // soap call parms.
   $parms=array('pid' => $pid,
                'bDefPid' => $bDefPID,
                'bMechPid' => $bMechPID,
                'dissLabel' => $dissLabel,
                'bDefLabel' => 'Label for bDef',
                'bMechLabel' => 'Label for bMech',
                new soapval('bindingMap', 'DatastreamBindingMap', $bindingMap, '', 'http://www.fedora.info/definitions/1/0/types/'),
                'dissState' => 'A');
   //Call createDatastream
   $dissID = Fedora_API::openSoapCall('addDisseminator', $parms);
   return $dissID;
}

function callGetDisseminators($pid) {
   /********************************************
   * This call gets a list of disseminators per
   * pid.
   ********************************************/
   $parms=array('PID' => $pid);
   return $dissArray = Fedora_API::openSoapCall('getDisseminators', $parms);
}

function openSoapCall ($call, $parms) {
   /********************************************
   * This is a primary function called by all of
   * the preceding functions.
   * $call is the api call to the fedora api-m.
   ********************************************/
//   global $_REQUEST, $baseURL, $m_url, $fedoraAdmin, $fedoraPwd;
//	echo APP_FEDORA_MANAGEMENT_API;
   $client = new soapclient_internal(APP_FEDORA_MANAGEMENT_API);
   $client->setCredentials(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
   $result = $client->call($call, $parms);
   //comment the return and uncomment the echo and debugInfo
   //to see debug statements.

//   echo $result;
//   Fedora_API::debugInfo($client);
   return $result;

}

function openSoapCallAccess ($call, $parms) {
   /********************************************
   * This is a primary function called by all of
   * the preceding functions.
   * $call is the api call to the fedora api-a.
   ********************************************/
//   global $_REQUEST, $baseURL, $m_url, $fedoraAdmin, $fedoraPwd;
//	echo APP_FEDORA_MANAGEMENT_API;
   $client = new soapclient_internal(APP_FEDORA_ACCESS_API);
   $client->setCredentials(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
   $result = $client->call($call, $parms);
   //comment the return and uncomment the echo and debugInfo
   //to see debug statements.

//	 echo $result;
//	 Fedora_API::debugInfo($client);
   return $result;

}

function debugInfo($client) {
   /********************************************
   * This function provides debug statements.
   ********************************************/
   echo '<hr /><b>Debug Information</b><br /><br />';
   echo 'Request: <xmp>'.$client->request.'</xmp>';
   echo 'Response: <xmp>'.$client->response.'</xmp>';
   echo 'Debug log: <pre>'.$client->debug_str.'</pre>';
}





} // end of Fedora_API Class


?>
