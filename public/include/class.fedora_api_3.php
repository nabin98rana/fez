<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
// FOR FEDORA 3.3 API (probably works with earlier other 3.x Fedoras)
/***************** Fedora API calls ******************/
/*

Using the new REST interface

 */
// which is included in top.php
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.misc.php");
require_once(APP_INC_PATH . "nusoap.php");
include_once(APP_PEAR_PATH . "/HTTP/Request.php");
require_once(APP_INC_PATH . "class.fedora_direct_access.php");

class Fedora_API {

	/**
	 * Check to be sure the Fedora server is up and running!
	 *
	 * Developer Note: This function is not actually used for anything just yet, but will be in future releases. Should be in Fez 1.2.
	 *
	 * @access  public
	 * @return  void (Logs an error message)
	 */
	function checkFedoraServer() 
	{
		$log = FezLog::get();
		
		$ch = curl_init(APP_BASE_FEDORA_APIA_DOMAIN."/describe");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		$results = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close ($ch);
		if ($info['http_code'] == '200') {
			//proceed!!
		} else {
			$errMsg = 'It appears that the content repository server is down, Please try again later.';
			$log->err(array($errMsg, $info, __FILE__, __LINE__));
		}
	}

	/**
	 * Opens a given URL and reads it into a variable and returns the string variable.
	 *
	 * @access  public
	 * @param string $ur1 The URL of the website to read
	 * @return  string $result The URL in text
	 */
	function URLopen($url)
	{
		// Fake the browser type
		ini_set('user_agent','MSIE 4\.0b2;');
		$i = 0;
		do {
			$dh = fopen("$url",'r');
			$i++;
		} while ($i < 2 && $dh !== FALSE); // RCA - give up after three attempts
		if (!$dh) return;
		$result = "";
		$temp_result = "";
		while ($temp_result = fread($dh,8192)) {
			$result .= $temp_result;
		}
		fclose($dh);
		return $result;
	}

	/**
	 * Gets the next available persistent identifier from the Fedora PID Handler webservice.
	 *
	 * @access  public
	 * @return  string $pid The next avaiable PID in from the Fedora PID handler
	 */
	function getNextPID() 
	{
		$log = FezLog::get();
		
		$pid = false;
		$getString = APP_SIMPLE_FEDORA_APIM_DOMAIN."/objects/nextPID?format=xml";
		$ch = curl_init($getString);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		curl_setopt($ch, CURLOPT_USERPWD, APP_FEDORA_USERNAME.":".APP_FEDORA_PWD); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, array('format' => "xml")); 
		$results = curl_exec($ch);
		
		if ($results) {
			$info = curl_getinfo($ch);
			curl_close ($ch);
			$xml = $results;
			$dom = @DomDocument::loadXML($xml);
			if (!$dom) {
				$log->err(array("Problem getting PID from fedora.",$xml,$info,__FILE__,__LINE__));
				return false;
			}
			$result = $dom->getElementsByTagName("pid");
			foreach($result as $item) {
				$pid = $item->nodeValue;
				break;
			}
		} else {
			$log->err(array(curl_error($ch),__FILE__,__LINE__));
			curl_close ($ch);
		}
		return $pid;
	}

	/**
	 * Gets the XML of a given object by PID.
	 *
	 * Developer Note: This function is not actually used for anything just yet, but might be in future releases.
	 *
	 * @access  public
	 * @param string $pid The persistent identifier
	 * @return  string $result The XML of the object
	 */
	function getObjectXMLByPID($pid) 
	{
		if (APP_FEDORA_APIA_DIRECT == "ON") {
			$fda = new Fedora_Direct_Access();
			return $fda->getObjectXML($pid);
		}

                $getString = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid."/objectXML";
                $ch = curl_init($getString);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
                }
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $results = curl_exec($ch);
                if ($results) {
                        curl_close ($ch);
		        return $results;
                } else {
                        $log->err(array(curl_error($ch),__FILE__,__LINE__));
                        curl_close ($ch);
                        return false;
                }
	}


	/**
	 * Gets the audit trail for an object.
	 *
	 * @access  public
	 * @param string $pid The persistent identifier
	 * @return  array of audit trail
	 */
	function getAuditTrail($pid) 
	{
		$auditTrail = array();

		$obj_xml = Fedora_API::getObjectXMLByPID($pid);

		$xmldoc= new DomDocument();
		$xmldoc->preserveWhiteSpace = false;
		$xmldoc->loadXML($obj_xml);

		$xpath = new DOMXPath($xmldoc);
		$dsStmt = "/foxml:digitalObject/foxml:datastream";
		$ds = $xpath->query($dsStmt); // returns nodeList
		foreach ($ds as $dsNode) {
			$ID = $dsNode->getAttribute('ID');
			if( $ID != 'AUDIT' )
			continue;
			 
			$dvStmt = "./foxml:datastreamVersion[@ID='AUDIT.0']/foxml:xmlContent/audit:auditTrail";
			$dv = $xpath->query($dvStmt, $dsNode);
			foreach ($dv as $dvNode) {
				$daStmt = "./audit:record";
				$da = $xpath->query($daStmt,$dvNode);
				foreach ($da as $daNode) {
					$auditID = $daNode->getAttribute('ID');

					$dpStmt = "./audit:process";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$process = $dpNode->getAttribute('type');
						break;
					}

					$dpStmt = "./audit:action";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$action = $dpNode->nodeValue;
						break;
					}

					$dpStmt = "./audit:componentID";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$componentID = $dpNode->nodeValue;
						break;
					}

					$dpStmt = "./audit:responsibility";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$responsibility = $dpNode->nodeValue;
						break;
					}

					$dpStmt = "./audit:date";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$actionDate = $dpNode->nodeValue;
						break;
					}

					$dpStmt = "./audit:justification";
					$dp = $xpath->query($dpStmt,$daNode);
					foreach ($dp as $dpNode) {
						$justification = $dpNode->nodeValue;
						break;
					}

					array_push($auditTrail, array("ID" => $auditID,
                                                  "process" => $process,
                                                  "action" => $action,
                                                  "componentID" => $componentID,
                                                  "responsibility" => $responsibility,
                                                  "date" => $actionDate,
                                                  "justification" => $justification));
				}
			}
			break;
		}
		return $auditTrail;
	}



	/**
	 * This function ingests a FOXML object and base64 encodes it
	 *
	 * @access  public
	 * @param string $foxml The XML object itself in FOXML format
	 * @return  void
	 */
	function callIngestObject($foxml, $pid="") 
	{
                $log = FezLog::get();

                $getString = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid."?format=info:fedora/fedora-system:FOXML-1.0";

                $tempFile = APP_TEMP_DIR.str_replace(":", "_", $pid).".xml";
//				$tempFile = "php://temp";

                $fp = fopen($tempFile, "w"); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name

				if (fwrite($fp, $foxml) === FALSE) {
				        echo "Cannot write to file ($tempFile)";
				        exit;
				}
                $ch = curl_init($getString);
				curl_setopt($ch, CURLOPT_HEADER, true);  
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
                }

                curl_setopt($ch, CURLOPT_POST, 1);

//				curl_setopt($ch, CURLOPT_INFILE, $fp);  
//				curl_setopt($ch, CURLOPT_INFILESIZE, strlen($foxml));
                curl_setopt($ch, CURLOPT_POSTFIELDS, array("file" => "@".$tempFile.";type=text/xml", "format" => "info:fedora/fedora-system:FOXML-1.0"));

                $results = curl_exec($ch);
                fclose($fp);
                if ($results) {

                        $info = curl_getinfo($ch);
						if ($info['http_code'] != '200' && $info['http_code'] != '201') {
	                        $log->err(array($info, $results),__FILE__,__LINE__);
							curl_close($ch);
							return false;
						}
                        curl_close ($ch);
						unlink($tempFile);
                        return true;
                } else {
                        $log->err(array(curl_error($ch),__FILE__,__LINE__));
                        curl_close ($ch);
                        return false;
                } 
	}

	function export($pid, $format="info:fedora/fedora-system:FOXML-1.0", $context="migrate") 
	{
		$parms = compact('pid','form at','context');
		$result = Fedora_API::openSoapCall('export', $parms);
		return $result;
	}

	/**
	 * Returns an associative array
	 * listSession - can be used to get the next page of results (see listSession => token, completeListSize, cursor)
	 * The session seems to have a five minute timeout
	 * resultList - the list of items found
	 */
	function callFindObjects($resultFields = array('pid', 'title', 'identifier', 'description', 'state'),
	$maxResults = 10, $query_terms="")
	{
		return Fedora_API::openSoapCallAccess('findObjects', array(
            'resultFields' => $resultFields,
		new soapval('maxResults','nonNegativeInteger', $maxResults),
		new soapval('query','FieldSearchQuery',
		array('terms' => $query_terms),
		false,'http://www.fedora.info/definitions/1/0/types/')
		));

		//$name='soapval',$type=false,$value=-1,$element_ns=false,$type_ns=false,$attributes=false
	}

	/**
	 * NOTE: This method doesn't work.  Use  search() instead.
	 * @param array $conditions - of form
	 * 					array(
	 *						array('property' => 'pid','operator' => '=','value' => 'UQ:12345'),
	 *						array('property' => 'title','operator' => '=','value' => 'Hello World'), ...)
	 */
	function callFindObjectsQuery($conditions,
	$resultFields = array('pid', 'title', 'identifier', 'description', 'state'),
	$maxResults = 10)
	{
		// NOTE: This method / function doesn't work. Use  search() instead.
		$conditions_soap = array();
		foreach ($conditions as $condition) {
			$conditions_soap[] = new soapval('fedora-types:property', 'string', $condition['property']);
			$conditions_soap[] = new soapval('fedora-types:operator', 'fedora-types:ComparisonOperator', $condition['operator']);
			$conditions_soap[] = new soapval('fedora-types:value', 'string', $condition['value']);
		}
		$conditions_array_soap = new soapval('fedora-types:conditions','ArrayOfCondition',$conditions_soap /*, false, false, array('SOAP-ENC:arrayType'=>'fedora-types:Condition[]') */);
		return Fedora_API::openSoapCallAccess('findObjects', array(
            'fedora-types:resultFields' => $resultFields,
		new soapval('fedora-types:maxResults','nonNegativeInteger', $maxResults),
		new soapval('fedora-types:query','fedora-types:FieldSearchQuery',array('fedora-types:conditions' => $conditions_array_soap))

		));
	}


	function callResumeFindObjects($token)
	{
		return Fedora_API::openSoapCallAccess('resumeFindObjects', array('sessionToken' => $token));
	}

	/*
	 * This function uses Fedora's simple search service which only really works against Dublin Core records.
	 * @param string $query The query by which the search will be carried out.
	 *		See http://www.fedora.info/wiki/index.php/API-A-Lite_findObjects#Parameters: for
	 *		documentation of the syntax of the query.
	 * @param array $fields The list of DC and Fedora basic fields to search against.
	 * @return  array $resultList The search results.
	 */
	function searchQuery($query, $fields = array('pid', 'title'))
	{
		$fieldstr = '';
		foreach ($fields as $field) {
			$fieldstr .= '&'.$field.'=true';
		}
		$url = APP_FEDORA_SEARCH_URL.'?query='.urlencode($query).'&xml=true'.$fieldstr;
		list($xml,$info) = Misc::processURL($url);
		return self::resultListXMLtoArray($xml, $fields);
	}

	/**
	 * This function uses Fedora's simple search service which only really works against Dublin Core records,
	 * so is not heavily used. Searches are mostly carried out against Fez's own (much more powerful) index.
	 *
	 * @access  public
	 * @param string $searchTerms The terms by which the search will be carried out.
	 * @param integer $maxResults The maximum amount of results that will be returned.
	 * @param array $searchTerms The list of DC and Fedora basic fields to search against.
	 * @return  array $resultList The search results.
	 */
	function getListObjectsXML($searchTerms, $maxResults=2147483647, $returnfields=null) 
	{
		$searchTerms = urlencode("*".$searchTerms."*"); // encode it for url parsing
		if (empty($returnfields)) {
			$returnfields = array('pid', 'title', 'identifier', 'description', 'type');
		}
		$fieldPhrase = '';
		foreach ($returnfields as $rField) {
			$fieldPhrase .= "&".$rField."=true";
		}
		$searchPhrase = "?xml=true".$fieldPhrase."&terms=".$searchTerms;
		if (is_numeric($maxResults)) {
			$searchPhrase .= "&maxResults=".$maxResults;
		}
		$filename = APP_FEDORA_SEARCH_URL.$searchPhrase;

		//		$xml = file_get_contents($filename);
		list($xml,$info) = Misc::processURL($filename);
		$xml = preg_replace("'<object uri\=\"info\:fedora\/(.*)\"\/>'", "<pid>\\1</pid>", $xml); // fix the pid tags
		return self::resultListXMLtoArray($xml,$returnfields);
	}

	function resultListXMLtoArray($xml,$returnfields)
	{
		$resultlist = array();
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


	/**
	 * This function removes an object and all its datastreams from Fedora
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @return  integer
	 */
	function callPurgeObject($pid) 
	{
		$logmsg = 'Fedora Object Purged using Fez';
		/*$parms=array('PID' => $pid, 'logMessage' => $logmsg, 'force' => false);
		Fedora_API::openSoapCall('purgeObject', $parms);
		return 1;
*/

                $log = FezLog::get();

                $getString = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid;

                $ch = curl_init($getString);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
                }
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('force' => "false", "logMessage" => $logmsg));
                $results = curl_exec($ch);
                if ($results) {
                        //$info = curl_getinfo($ch);
                        curl_close ($ch);
                        return true;
                } else {
                        $log->err(array(curl_error($ch),__FILE__,__LINE__));
                        curl_close ($ch);
                        return false;
                }

	}

	/**
	 * This function uses curl to upload a file into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsIDName The datastream name
	 * @param string $dsLabel The datastream label
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param string $dsID The ID of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @return  integer
	 */
	function getUploadLocation ($pid, $dsIDName, $file, $dsLabel, $mimetype='text/xml', $controlGroup='M', $dsID=NULL,$versionable='false') 
	{
		$log = FezLog::get();
		if (!is_numeric(strpos($dsIDName, "/"))) {
			$loc_dir = APP_TEMP_DIR;
		}

		if (!empty($file) && (trim($file) != "")) {
			$file_full = $loc_dir.str_replace(":", "_", $pid)."_".$dsIDName.".xml";
			$fp = fopen($file_full, "w"); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
			fwrite($fp, $file);
			fclose($fp);
//			echo "-".$loc_dir.str_replace(":", "_", $pid)."_"$dsIDName."-".$file; exit;
//			echo $file; exit;
		}

/*		
		if (empty($dsIDName)) {
			$log->err(array("Blank dsIDName",__FILE__,__LINE__));
			return false;
		}
		$loc_dir = "";
		if (!is_numeric(strpos($dsIDName, "/"))) {
			$loc_dir = APP_TEMP_DIR;
		}
		if ($mimetype == 'text/xml') {
			$config = array(
                'indent'        => true,
                'input-xml'     => true,
                'output-xml'    => true,
                'wrap'          => 0
			);

			if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
				$tidy = new tidy;
				$tidy->parseString($file, $config, 'utf8');
				$tidy->cleanRepair();
				$file = $tidy;
			}
		}
		if (!empty($file) && (trim($file) != "")) {
			$fp = fopen($loc_dir.$dsIDName, "w"); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
			fwrite($fp, $file);
			fclose($fp);
			$ch = curl_init(APP_FEDORA_UPLOAD_URL);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_HEADER, 0);
	  		curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => "@".$loc_dir.$dsIDName)); //@@@ CK - 28/7/2005 - Trying to make the file name in /tmp the uploaded file name
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}
			$uploadLocation = curl_exec($ch);

			if ($uploadLocation) {
				$uploadLocation = trim(str_replace("\n", "", $uploadLocation));
				$dsIDNameOld = $dsIDName;
				if (is_numeric(strpos($dsIDName, chr(92)))) {
					$dsIDName = substr($dsIDName, strrpos($dsIDName, chr(92))+1);
					if ($dsLabel == $dsIDNameOld) {
						$dsLabel = $dsIDName;
					}
				}*/
				$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
				$dsExists = Fedora_API::datastreamExists($pid, $dsIDName, true);
				if ($dsExists !== true) {
					//Call callAddDatastream
					$dsID = Fedora_API::callAddDatastream($pid, $dsIDName, $file_full, $dsLabel, "A", $mimetype, $controlGroup, $versionable, '');
					unlink($file_full);
					return $dsID;
				} elseif (!empty($dsIDName)) {
					// Let fedora handle versioning
					//Fedora_API::callModifyDatastreamByReference ($pid, $dsIDName, $dsLabel, $uploadLocation, $mimetype, $versionable);
					Fedora_API::callModifyDatastream($pid, $dsIDName, $file_full, $dsLabel, "A", $mimetype, $versionable, '');
					unlink($file_full);
					return $dsIDName;
				}

/*				curl_close ($ch);
			} else {
				$log->err(array(curl_error($ch),__FILE__,__LINE__));
				curl_close ($ch);
			}
		} */
	}

	/**
	 * This function uses curl to geta file from a local file location and upload it into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
	 *
	 * Developer Note: Mainly used by batch import of a SAN directory
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsIDName The datastream name
	 * @param string $local_file_location The location of the file on a local server directory
	 * @param string $dsLabel The datastream label
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param string $dsID The ID of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @return  integer
	 */
	function getUploadLocationByLocalRef ($pid, $dsIDName, $local_file_location, $dsLabel, $mimetype, $controlGroup='M', $dsID=NULL,$versionable='false') 
	{
		$log = FezLog::get();
		/*
		// take out any nasty slashes from the ds name itself
		$dsIDNameOld = $dsIDName;
		if (is_numeric(strpos($dsIDName, "/"))) {
			$dsIDName = substr($dsIDName, strrpos($dsIDName, "/")+1);
		}
		if (is_numeric(strpos($dsIDName, chr(92)))) {
			$dsIDName = substr($dsIDName, strrpos($dsIDName, chr(92))+1);
			if ($dsLabel == $dsIDNameOld) {
				$dsLabel = $dsIDName;
			}
		}
		// fix path if local filename has no path already
		//        if (!is_numeric(strpos($local_file_location,'/'))) {
		if (!is_numeric(strpos($local_file_location,"/"))) {
			$local_file_location = APP_TEMP_DIR.$local_file_location;
		}
		// get mimetype
		if ($mimetype == "") {
			$mimetype = Misc::mime_content_type($local_file_location);
		}
		// convert extension to lowercase on dsIDName
		$dsIDName = str_replace(" ", "_", $dsIDName);
		if (is_numeric(strpos($dsIDName, "."))) {
			$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
			$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
		}
		if ($controlGroup == 'X') { //If the file is xml, Tidy it up. You can't trust your xml creation tools sometimes, although now JHOVE is M content instead of X so no need to worry about jhove
			$xml = file_get_contents($local_file_location);
			$config = array(
                'indent'        => true,
                'input-xml'     => true,
                'output-xml'    => true,
                'wrap'          => 0
			);
			if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
				$tidy = new tidy;
				$tidy->parseString($xml, $config, 'utf8');
				$tidy->cleanRepair();
				$xml = "$tidy";
				file_put_contents($local_file_location, $xml);
			}
		}
		if (!empty($local_file_location) && (trim($local_file_location) != "")) {
			//Send multipart/form-data via curl
			$ch = curl_init(APP_FEDORA_UPLOAD_URL);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => "@".$local_file_location));
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}
			$uploadLocation = curl_exec($ch);
			if ($uploadLocation) { */
//				curl_close ($ch);
				
				
				if (!is_numeric(strpos($local_file_location,"/"))) {
					$local_file_location = APP_TEMP_DIR.$local_file_location;
				}
				if ($mimetype == "") {
					$mimetype = Misc::mime_content_type($local_file_location);
				}

				
				$local_file_location = trim(str_replace("\n", "", $local_file_location));
				$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
				$dsExists = Fedora_API::datastreamExists($pid, $dsIDName);
				if ($dsExists !== true) {
					//Call callAddDatastream
//					$dsID = Fedora_API::callCreateDatastream ($pid, $dsIDName, $uploadLocation, $dsLabel, $mimetype, $controlGroup, $versionable);
					$dsID = Fedora_API::callAddDatastream($pid, $dsIDName, $local_file_location, $dsLabel, "A", $mimetype, $controlGroup, $versionable);
					return $dsID;
				} elseif (!empty($dsIDName)) {
					// Let fedora handle versioning
					//Fedora_API::callModifyDatastreamByReference ($pid, $dsIDName, $dsLabel, $uploadLocation, $mimetype, $versionable);
					Fedora_API::callModifyDatastream($pid, $dsIDName, $local_file_location, $dsLabel, "A", $mimetype, $versionable);
					return $dsIDName;
				}
/*			} else {
				$log->err(array(curl_error($ch),__FILE__,__LINE__));
				curl_close ($ch);
			} 
		} */
	}

	function tidyXML($xml) {
		if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
			$config = array(
                'indent'        => true,
                'input-xml'     => true,
                'output-xml'    => true,
                'wrap'          => 0
			);
			$tidy = new tidy;
			$tidy->parseString($xml, $config, 'utf8');
			$tidy->cleanRepair();
			$xml = "$tidy";
		}
		return $xml;
	}

	/**
	 * This function adds datastreams to object $pid.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsID The ID of the datastream
	 * @param string $uploadLocation The location of the file to add
	 * @param string $dsLabel The datastream label
	 * @param string $dsState The datastream state
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @param boolean $xmlContent If it an X based xml content file then it uses a var rather than a file location 
	 * @return void
	 */
	function callAddDatastream ($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $controlGroup='M',$versionable='false', $xmlContent="") 
	{
		if ($mimetype == "") {
			$mimetype = "text/xml";
		}
        if (is_numeric(strpos($mimetype, " "))) {
            $mimetype = substr($mimetype, 0, strpos($mimetype, " "));
        }
		$dsIDOld = $dsID;
		if (is_numeric(strpos($dsID, chr(92)))) {
			$dsID = substr($dsID, strrpos($dsID, chr(92))+1);
			if ($dsLabel == $dsIDOld) {
				$dsLabel = $dsID;
			}
		}
		$dsIDName = $dsID;
		if (is_numeric(strpos($dsIDName, "/"))) {
			$dsIDName = substr($dsIDName, strrpos($dsIDName, "/")+1);
		}

//		$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
        if ($versionable != 'true' && $versionable != 'false') {
            $versionable = 'false';
        }

        $log = FezLog::get();
		$getString = APP_SIMPLE_FEDORA_APIM_DOMAIN."/objects/".$pid."/datastreams/".$dsIDName."?dsLabel=".urlencode($dsLabel)."&versionable=".$versionable."&mimeType=".$mimetype.
			          "&controlGroup=".$controlGroup."&dsState=A&logMessage=Added%20Datastream";


		if ($dsLocation != "" && $controlGroup == "X") {
			$xmlContent = file_get_contents($dsLocation);
		}
		if ($dsLocation != "" && $controlGroup == "R") {
			$getString .= "&dsLocation=".$dsLocation;
			$ch = curl_init($getString);
 		 	curl_setopt($ch, CURLOPT_POST, 1);

			curl_setopt($ch, CURLOPT_POSTFIELDS, array("dsLocation" => $dsLocation, 
														"dsLabel" => urlencode($dsLabel),
														"versionable" => $versionable,
														"mimeType" => $mimetype,
														"controlGroup" => $controlGroup,
														"dsState" => "A", 
														"logMessage" => "Added Link"
														));


		} elseif ($xmlContent != "") {
			$ch = curl_init($getString);
 		 	curl_setopt($ch, CURLOPT_POST, 1);
			if ($controlGroup == 'X') {
				$xmlContent = Fedora_API::tidyXML($xmlContent);
				$tempFile = APP_TEMP_DIR.str_replace(":", "_", $pid)."_".$dsID.".xml";			
			} else {
				$tempFile = APP_TEMP_DIR.$dsID;
			}
			$fp = fopen($tempFile, "w");
			if (fwrite($fp, $xmlContent) === FALSE) {
			        echo "Cannot write to file ($tempFile)";
			        exit;
			}
			fclose($fp);
//			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file[]" => "@".$tempFile.";type=".$mimetype));
			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file[]" => "@".$tempFile.";type=".$mimetype, 
											"dsLabel" => urlencode($dsLabel),
											"versionable" => $versionable,
											"mimeType" => $mimetype,
											"controlGroup" => $controlGroup,
											"dsState" => "A",
											"logMessage" => "Added Datastream"
											));

		} elseif ($dsLocation != "" && $controlGroup == "M") {
			$ch = curl_init($getString);
	 		 curl_setopt($ch, CURLOPT_POST, 1);
//			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file[]" => "@".$dsLocation.";type=".$mimetype));
//$log->err("OMG: $dsLocation"); 
//			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file[]" => "@".$dsLocation.";type=".$mimetype, 
			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file_name" => "@".$dsLocation.";type=".$mimetype, 
//			curl_setopt($ch, CURLOPT_POSTFIELDS, array("file_name" => "@".$dsLocation, 
														"dsLabel" => urlencode($dsLabel),
														"versionable" => $versionable,
														"mimeType" => $mimeType,
														"controlGroup" => $controlGroup,
														"dsState" => "A", 
														"logMessage" => "Added Datastream",
														"submit" => "UPLOAD"
														));
		}
		 curl_setopt($ch, CURLOPT_USERPWD, APP_FEDORA_USERNAME.":".APP_FEDORA_PWD); 

		 curl_setopt($ch, CURLOPT_VERBOSE, 1);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
		         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		 }
		
		 $results = curl_exec($ch);
		 if ($results) {
		         //$info = curl_getinfo($ch);
		         curl_close ($ch);
						 unlink($tempFile);
		         return true;
		 } else {
//		         $log->err(array(print_r($results, true).print_r(curl_error($ch), true).print_r(curl_getinfo($ch), true),__FILE__,__LINE__).$getString.print_r(debug_backtrace(),true));
		         $log->err(print_r(array(print_r($results, true).print_r(curl_error($ch), true).print_r(curl_getinfo($ch), true),__FILE__,__LINE__), true).$getString.$tempFile.$xmlContent.", dsID was $dsID, dsIDName was $dsIDName");
//				exit;
		         curl_close ($ch);
		         return false;
		 }

	}


		function callModifyDatastream ($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $versionable='false', $xmlContent="") 
		{
      $tempFile = "";
			if ($mimetype == "") {
				$mimetype = "text/xml";
			}
            if (is_numeric(strpos($mimetype, " "))) {
                $mimetype = substr($mimetype, 0, strpos($mimetype, " "));
            }

			$dsIDOld = $dsID;
			if (is_numeric(strpos($dsID, chr(92)))) {
				$dsID = substr($dsID, strrpos($dsID, chr(92))+1);
				if ($dsLabel == $dsIDOld) {
					$dsLabel = $dsID;
				}
			}
//echo "OMG WHAT THE? ".$dsLocation; print_r(debug_backtrace()); exit;
//			$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : 'false';
            if ($versionable != 'true' && $versionable != 'false') {
                $versionable = 'false';
            }
	        $log = FezLog::get();
//			$getString = APP_SIMPLE_FEDORA_APIM_DOMAIN."/objects/".$pid."/datastreams/".$dsID."?dsLabel=".urlencode($dsLabel)."&mimeType=".$mimeType."&formatURI="
//				          .$formatURI."&dsState=A&logMessage=".urlencode("Modified Datastream");

			$getString = APP_SIMPLE_FEDORA_APIM_DOMAIN."/objects/".$pid."/datastreams/".$dsID."?versionable=".$versionable;

			$ch = curl_init($getString);
//			curl_setopt($ch, CURLOPT_PUT, true);
//			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
//			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");



			curl_setopt($ch, CURLOPT_USERPWD, APP_FEDORA_USERNAME.":".APP_FEDORA_PWD); 

			$isLink = false;

			if (is_numeric(strpos($dsID, "Link"))) {
			  $isLink = true;
			}
			if ($dsLocation != "" && $isLink != true) {
				$xmlContent = file_get_contents($dsLocation);
			}

			if ($dsLocation != "" && $isLink == true) {
				$log->err("sending this as a link => got a location of ".$dsLocation);
				exit;
				$getString .= "&dsLocation=".$dsLocation;
				curl_setopt($ch, CURLOPT_POSTFIELDS, array("dsLocation" => $dsLocation,
															"dsLabel" => urlencode($dsLabel),
															"versionable" => $versionable,
															"mimeType" => $mimetype,
															"formatURI" => $formatURI,
//															"controlGroup" => $controlGroup,
															"dsState" => "A",
															"logMessage" => "Modified Datastream"
				
				));
      } elseif ($dsLocation != "") {
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("file" => "@".$dsLocation.";type=".$mimetype,
                              "dsLabel" => urlencode($dsLabel),
                              "versionable" => $versionable,
                              "mimeType" => $mimetype,
                              "formatURI" => $formatURI,
//															"controlGroup" => $controlGroup,
                              "dsState" => "A",
                              "logMessage" => "Modified Datastream"
        ));
        $log->err("sending this as a file => got a location of ".$dsLocation);
			} elseif ($xmlContent != "") {
				$xmlContent = Fedora_API::tidyXML($xmlContent);
				$tempFile = APP_TEMP_DIR.str_replace(":", "_", $pid)."_".$dsID.".xml";
				$fp = fopen($tempFile, "w");
				if (fwrite($fp, $xmlContent) === FALSE) {
				        $err = "Cannot write to file ($tempFile)";
                $log->err(array($err, __FILE__,__LINE__));
				        exit;
				}
				fclose($fp);

        $params = array("file" => "@".$tempFile.";type=".$mimetype,
															"dsLabel" => urlencode($dsLabel),
															"versionable" => $versionable,
															"mimeType" => $mimetype,
															"formatURI" => null,
//															"controlGroup" => $controlGroup,
															"dsState" => "A",
															"logMessage" => "Modified Datastream"
				);


				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}

			 if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
			         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			 }
			 $results = curl_exec($ch);

			 if ($results) {
			        $info = curl_getinfo($ch);
					if ($info['http_code'] != '200' && $info['http_code'] != '201') {
                        // If it is a "PID is currently being modified by another thread" 409 error then wait 5 seconds and try again..
                        if ($info['http_code'] == '409') {
                            sleep(5);
                            $results = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            if ($info['http_code'] != '200' && $info['http_code'] != '201') {
                                $log->err(array(print_r($results, true).print_r($params, true).curl_error($ch), $info,__FILE__,__LINE__));
                                curl_close($ch);
                                return false;
                            }
                        } else {
                            $log->err(array(print_r($results, true).print_r($params, true).curl_error($ch), $info,__FILE__,__LINE__));
       						curl_close($ch);
       						return false;
                        }
					}
                    if (file_exists($tempFile)) {
                      unlink($tempFile);
                    }
			    curl_close ($ch);
			    return true;
			 } else {
			         $info = curl_getinfo($ch);
			         $log->err(array($tempFile." ".curl_error($ch), $info,__FILE__,__LINE__));
			         curl_close ($ch);
			         return false;
			 }

		}

	/**
	 * This function adds datastreams to object $pid.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsIDName The name of the datastream
	 * @param string $uploadLocation The location of the file to add
	 * @param string $dsLabel The datastream label
	 * @param string $dsState The datastream state
	 * @param string $mimetype The mimetype of the datastream
	 * @param string $controlGroup The control group of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @return void
	 */
	function callCreateDatastream($pid, $dsIDName, $uploadLocation, $dsLabel, $mimetype, $controlGroup='M',$versionable='false') 
	{
/*		$dsIDNameOld = $dsIDName;
		if (is_numeric(strpos($dsIDName, chr(92)))) {
			$dsIDName = substr($dsIDName, strrpos($dsIDName, chr(92))+1);
			if ($dsLabel == $dsIDNameOld) {
				$dsLabel = $dsIDName;
			}
		}
		$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
		$parms=array(
    	   'PID'           => $pid, 
    	   'dsID'          => $dsIDName, 
    	   'altIDs'        => array(), 
    	   'dsLabel'       => $dsLabel, 
		new soapval('versionable', 'boolean', $versionable),
    	   'MIMEType'      => $mimetype, 
    	   'formatURI'     => 'unknown', 
    	   'dsLocation'    => $uploadLocation, 
    	   'controlGroup'  => $controlGroup, 
    	   'dsState'       => 'A', 
    	   'logMessage'    => 'Added new datastream from Fez'
    	   );
    	   $dsID = Fedora_API::openSoapCall('addDatastream', $parms);
    	   return $dsID;*/
           return Fedora_API::callAddDatastream($pid, $dsIDName, $uploadLocation, $dsLabel, 'A', $mimetype, $controlGroup, $versionable);
	}

	/**
	 *This function creates an array of all the datastreams for a specific object.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $createdDT (optional) Fedora timestamp of version to retrieve
	 * @return array $dsIDListArray The list of datastreams in an array.
	 */
	function callGetDatastreams($pid, $createdDT=NULL, $dsState='A') 
	{
		if (!is_numeric($pid)) {

			if (APP_FEDORA_APIA_DIRECT == "ON") {
				$fda = new Fedora_Direct_Access();
				$datastreams = $fda->getDatastreams($pid);
				return $datastreams;
			}

			$parms=array('pid' => $pid, 'asOfDateTime' => $createdDT, 'dsState' => $dsState);
			$dsIDListArray = Fedora_API::openSoapCall('getDatastreams', $parms);
			//			print_r($dsIDListArray);
			if (empty($dsIDListArray) || (is_array($dsIDListArray) && isset($dsIDListArray['faultcode']))) {
				return false;
			}
			if (!is_array($dsIDListArray[0])){
				// when only one datastream, it returns as a datastream instead of
				// array of datastreams so rewrite as array of datastreams to match
				// multiple datastreams format
				$ds = array();
				$ds[controlGroup] = $dsIDListArray[controlGroup];
				$ds[ID]           = $dsIDListArray[ID];
				$ds[versionID]    = $dsIDListArray[versionID];
				$ds[altIDs]       = $dsIDListArray[altIDs];
				$ds[label]        = $dsIDListArray[label];
				$ds[versionable]  = $dsIDListArray[versionable];
				$ds[MIMEType]     = $dsIDListArray[MIMEType];
				$ds[formatURI]    = $dsIDListArray[formatURI];
				$ds[createDate]   = $dsIDListArray[createDate];
				$ds[size]         = $dsIDListArray[size];
				$ds[state]        = $dsIDListArray[state];
				$ds[location]     = $dsIDListArray[location];
				$ds[checksumType] = $dsIDListArray[checksumType];
				$ds[checksum]     = $dsIDListArray[checksum];

				$dsIDListArray = array();
				$dsIDListArray[0] = $ds;
			}
			sort($dsIDListArray);
			reset($dsIDListArray);
			return $dsIDListArray;
		} else {
			return array();
		} 
/*		$getString = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid."/objectXML";
        $ch = curl_init($getString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $results = curl_exec($ch);
        if ($results) {
                curl_close ($ch);
        return $results;
        } else {
                $log->err(array(curl_error($ch),__FILE__,__LINE__));
                curl_close ($ch);
                return false;
        }
        
*/		
		
	}

	/**
	 *This function creates an array of all the datastreams for a specific object.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @return array $dsIDListArray The list of datastreams in an array.
	 */
	function callListDatastreams($pid) 
	{
		if (!is_numeric($pid)) {
			if (APP_FEDORA_APIA_DIRECT == "ON") {
				$fda = new Fedora_Direct_Access();
				$dsIDListArray = $fda->listDatastreams($pid);
				return $dsIDListArray;
			}
			$parms=array('pid' => $pid, 'asOfDateTime' => NULL);
			$dsIDListArray = Fedora_API::openSoapCallAccess('listDatastreams', $parms);
			sort($dsIDListArray);
			reset($dsIDListArray);
			return $dsIDListArray;
		} else {
			return array();
		}
	}

	/**
	 *This function creates an array of all the datastreams for a specific object using the API-A-LITE rather than soap
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @return array $dsIDListArray The list of datastreams in an array.
	 */
	function callListDatastreamsLite($pid, $refresh=false, $current_tries = 0)
	{
		$log = FezLog::get();
		
		static $returns;
		if (!is_array($returns)) {
			$returns = array();
		}
		if (!is_numeric($pid)) {
			if ($refresh == false && isset($returns[$pid]) && is_array($returns[$pid])) {
				return $returns[$pid];
			}
			if (APP_FEDORA_APIA_DIRECT == "ON") {
				$fda = new Fedora_Direct_Access();
				$dsIDListArray = $fda->listDatastreams($pid);
				return $dsIDListArray;
			}
			$getString = APP_BASE_FEDORA_APIA_DOMAIN."/listDatastreams/".$pid."?xml=true";
//			$ch = curl_init($getString);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//			if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
//				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
//				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
//			}
//			$results = curl_exec($ch);
//			$info = curl_getinfo($ch);
//			curl_close ($ch);
//			$xml = $results;
            list($xml, $info) = Misc::processURL($getString);
            if ($xml == '' || $xml == false) {
                $current_tries++;
                $fedoraError = "Error when calling ".__FUNCTION__." :".print_r($info,true)."\n\n \n\n FOR THE $current_tries time REQUEST: $pid "."\n\n RESPONSE: \n\n ".$xml;
                $log->err(array($fedoraError, __FILE__,__LINE__));
                if ($current_tries < 5) {
                  sleep(5); // sleep for a bit so the object can get unlocked before trying again
                  return Fedora_API::callListDatastreamsLite($pid, $refresh, $current_tries);
                } else {
                  return false;
                }
            }



			$dom = @DomDocument::loadXML($xml);
			if (!$dom) {
				$log->err(array("Couldn't parse datastream XML",$info,$xml));
				return false;
			}
			$xpath = new DOMXPath($dom);
			$fieldNodeList = $xpath->query("/*/*");
			$counter = 0;
			foreach ($fieldNodeList as $fieldNode) {
				$fieldAttList = $xpath->query("@*",$fieldNode);
				foreach ($fieldAttList as $fieldAtt) {
					$resultlist[$counter][$fieldAtt->nodeName] = trim($fieldAtt->nodeValue);
				}
				$counter++;
			}
			//print_r($resultlist);
			if ($GLOBALS['app_cache']) {
				if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
					$returns = array();
				}
				$returns[$pid] = $resultlist;
			}
			return $resultlist;
		} else {
			return array();
		}
	}



	function objectExists($pid, $refresh = false) 
	{
		
		static $exists;
		if (!empty($exists)) {
			return $exists;
		}
		
		if (!is_array($exists)) {
			$exists = array();
		}
		if (!is_numeric($pid)) {
			if ($refresh == false && isset($exists[$pid]) && is_array($exists[$pid])) {
				return $exists[$pid];
			}
		}
		
		if (Misc::isPid($pid) != true) {
			$exists[$pid] = false;
			return $exists[$pid];
		}

		if (APP_FEDORA_APIA_DIRECT == "ON") {
			$fda = new Fedora_Direct_Access();
			$exists[$pid] = $fda->objectExists($pid);
			return $exists[$pid];
		}
		//Just send a curl request to REST - faster than soap, just as good response
		$getString = APP_FEDORA_GET_URL."/".$pid."?xml=true";
		$ch = curl_init($getString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://')  {
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		$results = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close ($ch);
		$xml = $results;
		if (is_numeric(strpos($xml, "objCreateDate"))) {
			$exists[$pid] = true;
		} else {
			$exists[$pid] = false;
		}
		return $exists[$pid];
		
		// Old slow soap way - commented out for later removal - CK 17/7/2009 
		/*
		$parms = array('pid' => $pid);
		$result = Fedora_API::openSoapCall('getObjectXML', $parms, false);
		if (is_array($result) && isset($result['faultcode'])) {
			return false;
		} else {
			return true;
		} */
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @return array $dsIDListArray The requested of datastream in an array.
	 */
	function callGetDatastream($pid, $dsID, $createdDT=NULL)
	{
//		$parms=array('pid' => $pid, 'dsID' => $dsID);
    $parms=array('pid' => $pid, 'dsID' => $dsID, 'asOfDateTime' => $createdDT);
		$dsIDListArray = Fedora_API::openSoapCall('getDatastream', $parms);
		return $dsIDListArray;
	}

	/**
	 * Does a datastream with a given ID already exist in an object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream to be checked
	 * @param string $pattern a regex pattern to search against if given instead of ==/equivalence
	 * @return boolean
	 */
	function datastreamExists ($pid, $dsID, $refresh=false, $pattern=false) 
	{
		if (Misc::isPid($pid) != true) {
			return false;
		}

		$dsExists = false;
		$rs = Fedora_API::callListDatastreamsLite($pid, $refresh);
		if (is_array($rs)) {
			foreach ($rs as $row) {
				if ($pattern != false) {
                    $ds_matches = array();
					if (isset($row['dsid']) && preg_match($pattern, $row['dsid'], $ds_matches)) {
                        $dsExists = true;
						return $ds_matches[0];
					}
				} else {
					if (isset($row['dsid']) && ($row['dsid'] == $dsID)) {
						$dsExists = true;
					}
				}
			}
		}
		return $dsExists;
	}

	/**
	 * Does a datastream with a given ID already exist in existing list array of datastreams
	 *
	 * @access  public
	 * @param string $existing_list The existing list of datastreams
	 * @param string $dsID The ID of the datastream to be checked
	 * @return boolean
	 */
	function datastreamExistsInArray ($existing_list, $dsID) 
	{
		$dsExists = false;
		$rs = $existing_list;
		foreach ($rs as $row) {
			if (isset($row['ID']) && $row['ID'] == $dsID) {
				$dsExists = true;
			}
		}
		return $dsExists;
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream to be checked
	 * @param string $asofDateTime Optional Gets a specified version at a datetime stamp
	 * @return array $dsIDListArray The datastream returned in an array
	 */
	function callGetDatastreamDissemination($pid, $dsID, $asofDateTime="") 
	{
		$log = FezLog::get();
		// Redirect all calls to the REST Version for now - CK added 17/7/2009
		return Fedora_API::callGetDatastreamDisseminationLite($pid, $dsID, $asofDateTime);
		/*
		

		if (APP_FEDORA_APIA_DIRECT == "ON") {
			$fda = new Fedora_Direct_Access();
			return $fda->getDatastreamDissemination($pid, $dsID);
		}

		if ($asofDateTime == "") {
			$parms=array('pid' => $pid, 'dsID' => $dsID);
		} else {
			$parms=array('pid' => $pid, 'dsID' => $dsID, 'asofDateTime' => $asofDateTime);
		}
		$dsIDListArray = Fedora_API::openSoapCallAccess('getDatastreamDissemination', $parms);
		$dsIDListArray['stream'] = base64_decode($dsIDListArray['stream']);

		return $dsIDListArray; */
	}

	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream to be checked
	 * @param string $asofDateTime Optional Gets a specified version at a datetime stamp
	 * @return array $dsIDListArray The datastream returned in an array
	 */
	function callGetDatastreamDisseminationLite($pid, $dsID, $asofDateTime="", $current_tries = 0)
	{
		
		$log = FezLog::get();
		$dsIDListArray = array();
/*		if (APP_FEDORA_APIA_DIRECT == "ON" && $asofDateTime == "") {
			$fda = new Fedora_Direct_Access();
			return $fda->getDatastreamDissemination($pid, $dsID);
		}
*/
		if ($asofDateTime == "") {
//			$urldata = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid."/datastreams/".$dsID."/content";
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID;
		} else {
//			$urldata = APP_BASE_FEDORA_APIM_DOMAIN."/objects/".$pid."/datastreams/".$dsID."/content&asOfDateTime=".$asofDateTime;
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID."/".$asofDateTime;
		}
		list($dsIDListArray['stream'],$info) = Misc::processURL($urldata);

        if ($dsIDListArray['stream'] == '' || $dsIDListArray['stream'] == false) {
            $current_tries++;
            $fedoraError = "Error when calling ".__FUNCTION__." :".print_r($info,true)."\n\n \n\n FOR THE $current_tries time REQUEST: $pid $dsID "."\n\n RESPONSE: \n\n ".$dsIDListArray['stream'];
            $log->err(array($fedoraError, __FILE__,__LINE__));
            if ($current_tries < 5) {
              sleep(5); // sleep for a bit so the object can get unlocked before trying again
              return Fedora_API::callGetDatastreamDisseminationLite($pid, $dsID, $asofDateTime, $current_tries);
            } else {
              return false;
            }
        }

    if ($asofDateTime != "") {
      $config = array(
                'indent'        => true,
                'input-xml'     => true,
                'output-xml'    => true,
                'wrap'          => 0
      );

      if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
        $tidy = new tidy;
        $tidy->parseString($dsIDListArray['stream'], $config, 'utf8');
        $tidy->cleanRepair();
        $dsIDListArray['stream'] = $tidy;
      }
    }

		return $dsIDListArray;
	}




	/**
	 * This function creates an array of a specific datastream of a specific object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @param boolean $getxml Get as xml
	 * @return array $resultlist The requested of datastream in an array.
	 */
	function callGetDatastreamContents($pid, $dsID, $getraw = false, $filehandle = null, $current_tries = 0)
	{
        $log = FezLog::get();
		$resultlist = array();
		$dsExists = Fedora_API::datastreamExists($pid, $dsID);
		if ($dsExists === true) {

			$filename = APP_FEDORA_GET_URL."/".$pid."/".$dsID;

			if($filehandle != null) {
				$ret = Misc::processURL($filename, false, $filehandle);
				return $ret;
			} else {
				list($blob,$info) = Misc::processURL($filename);

                if ($blob == '' || $blob == false) {
                    $current_tries++;
                    $fedoraError = "Error when calling ".__FUNCTION__." :".print_r($info,true)."\n\n \n\n FOR THE $current_tries time REQUEST: $pid $dsID "."\n\n RESPONSE: \n\n ".$blob;
                    $log->err(array($fedoraError, __FILE__,__LINE__));
                    if ($current_tries < 5) {
                      sleep(5); // sleep for a bit so the object can get unlocked before trying again
                      return Fedora_API::callGetDatastreamContents($pid, $dsID, $getraw, $filehandle, $current_tries);
                    } else {
                      return false;
                    }
                }


			}
			// check if this is even XML, it might be binary, in which case we'll just return it.
			if ($info['content_type'] != 'text/xml' || $getraw) {
				return $blob;
			}
			// We've checked the mimetype is XML so lets parse it and make a simple array
			if (!empty($blob) && $blob != false) {
				$doc = DOMDocument::loadXML($blob);
				$xpath = new DOMXPath($doc);
				$fieldNodeList = $xpath->query("/*/*");
				foreach ($fieldNodeList as $fieldNode) {
					$resultlist[$fieldNode->nodeName][] = trim($fieldNode->nodeValue);
					// get attributes
					$fieldAttList = $xpath->query("@*",$fieldNode);
					foreach ($fieldAttList as $fieldAtt) {
						$resultlist[$fieldAtt->nodeName][] = trim($fieldAtt->nodeValue);
					}
				}
			}
		}
		return $resultlist;
	}

	/**
	 * This function creates an array of specific fields from a specific datastream of a specific object
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @param array $returnfields
	 * @return array $dsIDListArray The requested of datastream in an array.
	 */
	function callGetDatastreamContentsField($pid, $dsID, $returnfields, $asOfDateTime="") 
	{
		static $counter;
		if (!isset($counter)) {
			$counter = 0;

		}
		$counter++;
		$resultlist = array();
		$dsExists = Fedora_API::datastreamExists($pid, $dsID);
		if ($dsExists == true) {
			$xml = Fedora_API::callGetDatastreamDissemination($pid, $dsID, $asOfDateTime);
			//echo $pid."-".$dsID;
			$xml = $xml['stream'];
			if (!empty($xml) && $xml != false) {
				$doc = DOMDocument::loadXML($xml);
				$xpath = new DOMXPath($doc);
				$fieldNodeList = $xpath->query("/$dsID/*");
				foreach ($fieldNodeList as $fieldNode) {
					if (in_array($fieldNode->nodeName, $returnfields)) {
						$resultlist[$fieldNode->nodeName][] = trim($fieldNode->nodeValue);
					}
				}
			}
		}
		return $resultlist;
	}

	/**
	 * This function modifies inline xml datastreams (ByValue)
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The name of the datastream
	 * @param string $state The datastream state
	 * @param string $label The datastream label
	 * @param string $dsContent The datastream content
	 * @param string $mimetype The mimetype of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @return void
	 */
	function callModifyDatastreamByValue ($pid, $dsID, $state, $label, $dsContent, $mimetype='text/xml', $versionable='inherit') 
	{
//		if ($dsID == "DC") { echo "HERE"; exit; }
		
		Fedora_API::callModifyDatastream($pid, $dsID, "", $label, "A", $mimetype, $versionable, $dsContent);
		
/*		$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
		if( strcasecmp($versionable,'inherit') != 0 ){
			// if 'inherit' then versionable is not being changed
			$logmsg = 'Update versionable';
			$parms= array(
		       'pid'           => $pid, 
		       'dsID'  => $dsID, 
			new soapval('versionable', 'boolean', $versionable),
		       'logMessage'    => $logmsg, 
			);
			Fedora_API::openSoapCall('setDatastreamVersionable', $parms);
		}
	  
		if ($mimetype == 'text/xml') {
			$config = array(
			  'indent'      => true,
			  'input-xml'   => true,
			  'output-xml'  => true,
			  'wrap'        => 0
			);
			if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
				$tidy = new tidy;
				$tidy->parseString($dsContent, $config, 'utf8');
				$tidy->cleanRepair();
				$dsContent = $tidy;
			}
		}


		$dsContent = base64_encode(trim($dsContent));
		$logmsg = 'Modifying datastream from Fez';
		$parms= array(
            'pid'           => $pid, 
            'dsID'  => $dsID, 
            'altIDs'        => array(), 
            'dsLabel'       => $label, 
            'MIMEType'      => $mimetype, 
            'formatURI'     => 'unknown', 
		new soapval("dsContent","base64Binary",$dsContent),
            'logMessage'    => $logmsg, 
		new soapval('force', 'boolean', 'true')
		);
		//		echo "\n\n before open soap call,after tidy and base64encode for modify ".$dsID." "; echo date("l dS of F Y h:i:s A");
		Fedora_API::openSoapCall('modifyDatastreamByValue', $parms); */
		//		echo "\n\n after open soal call for modify ".$dsID." "; echo date("l dS of F Y h:i:s A");
	}

	/**
	 * This function modifies non-in-line datastreams, either a chunk o'text, a url, or a file.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The name of the datastream
	 * @param string $dsLabel The datastream label
	 * @param string $dsLocation The location of the datastream
	 * @param boolean $versionable Whether to version control this datastream or not
	 * @return void
	 */
	function callModifyDatastreamByReference($pid, $dsID, $dsLabel, $dsLocation=NULL, $mimetype,$versionable='inherit')
	{
		// force state to 'A'; otherwise, if the dsID is the same
		// as a DS that was deleted, then the modify will fail
		Fedora_API::callSetDatastreamState($pid,$dsID,'A');
		

		
		$versionable = $versionable === true ? 'true' : $versionable === false ? 'false' : $versionable;
		if( strcasecmp($versionable,'inherit') != 0 ){
			// if 'inherit' then versionable is not being changed
			$logmsg = 'Update versionable';
			$parms= array(
		       'pid'           => $pid, 
		       'dsID'  => $dsID, 
		       'versionable'  => $versionable,
		       'logMessage'    => $logmsg, 
			);
      //			new soapval('versionable', 'boolean', $versionable),
			Fedora_API::openSoapCall('setDatastreamVersionable', $parms);
		}
//		Fedora_API::callModifyDatastream($pid, $dsID, $dsLocation, $dsLabel, "A", $mimetype, $versionable);	  
		$logmsg = 'Modifying datastream by reference';
		$parms= array(
	       'pid'           => $pid, 
	       'dsID'  => $dsID, 
	       'altIDs'        => array(), 
	       'dsLabel'       => $dsLabel, 
	       'MIMEType'      => $mimetype, 
	       'formatURI'     => 'unknown', 
	       'dsLocation'    => $dsLocation,
         'checksumType'  => 'DISABLED',
         'checksum'      => 'none',
	       'logMessage'    => $logmsg, 
	       'force'         => true
		);
		Fedora_API::openSoapCall('modifyDatastreamByReference', $parms); 
	}

	/**
	 * Changes the state and/or label of the object.
	 * @param string $pid - the pid of the object.
	 * @param string $state - the new state, A, I or D. Null means leave unchanged.
	 * @param string $label - the new label. Null means leave unchanged.
	 * @param string $logMessage - a log message.
	 */
	function callModifyObject($pid, $state, $label, $logMessage = 'Deleted by Fez')
	{
		$ownerId = null; // Fez doesn't use this
		$parms= compact('pid','state','label','ownerId','logMessage');
		return Fedora_API::openSoapCall('modifyObject', $parms);
	}


	/**
	 * This function marks a datastream as deleted by setting the state.
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object
	 * @param string $dsID The ID of the datastream
	 * @return boolean
	 */
	function deleteDatastream($pid, $dsID) 
	{
		return Fedora_API::callSetDatastreamState($pid,$dsID,'D',"Changed Datastream State to Deleted from Fez");
	}

	/**
	 * This function sets the state flag on a datastream
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsID The name of the datastream
	 * @param string $state The new state of the datastream
	 * @param string $logMessage
	 * @return boolean
	 */
	function callSetDatastreamState ($pid, $dsID, $state='A', $logMessage="Changed Datastream State from Fez") 
	{
		$parms= array(
			'pid' => $pid, 
			'dsID' => $dsID, 
			'dsState' => $state, 
			'logMessage' => $logMessage);
		return Fedora_API::openSoapCall('setDatastreamState', $parms);
	}

	/**
	 * This function deletes a datastream
	 *
	 * @access  public
	 * @param string $pid The persistant identifier of the object to be purged
	 * @param string $dsID The name of the datastream
	 * @param string $endDT The end datetime of the purge
	 * @param string $logMessage
	 * @param boolean $force
	 * @return boolean
	 */
	function callPurgeDatastream ($pid, $dsID, $startDT=NULL, $endDT=NULL, $logMessage="Purged Datastream from Fez", $force=false) 
	{
		$parms= array('pid' => $pid, 'dsID' => $dsID, 'startDT' => $startDT, 'endDT' => $endDT, 'logMessage' => $logMessage, 'force' => $force);
		return Fedora_API::openSoapCall('purgeDatastream', $parms);
	}

	// This function is not used, as disseminators are not used in Fez, but it will be left in in for now
	function callAddDisseminator ($pid, $dsID, $bDefPID, $bMechPID, $dissLabel, $key) 
	{
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

	// This function is not used, as disseminators are not used in Fez, but it will be left in in for now
	function callGetDisseminators($pid) 
	{
		/********************************************
		 * This call gets a list of disseminators per
		 * pid.
		 ********************************************/
		$parms=array('PID' => $pid);
		return $dissArray = Fedora_API::openSoapCall('getDisseminators', $parms);
	}

	/**
	 * Passes as Soap call to the NUSOAP engine through to the Fedora Management webservice API-M
	 *
	 * @access  public
	 * @param array $call The name of the fedora web service to call
	 * @param array $parms The parameters
	 * @return array $result
	 */
	function openSoapCall ($call, $parms, $debug_error=true, $current_tries=0)
	{
		$log = FezLog::get();
		
	    $setDateTimeTo = array('getDatastream');
	    
	    //if(!array_key_exists('asOfDateTime', $parms) && in_array($call, $setDateTimeTo))
	    
		/********************************************
		 * This is a primary function called by all of
		 * the preceding functions.
		 * $call is the api call to the fedora api-m.
		 ********************************************/
//If using PHP4, or if you prefer to use nusoap over php soap you could uncomment the below and comment out the SoapClient code.		
/*		if (version_compare(phpversion(), "5.0.0", "<")) {
*//*			$client = new soapclient_internal(APP_FEDORA_MANAGEMENT_API);
			$client->setCredentials(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
			$result = $client->call($call, $parms);
			print_r($result);*/
			/*
			if ($debug_error && is_array($result) && isset($result['faultcode'])) {
				$fedoraError = "Error when calling ".$call." :".$result['faultstring'];
				$log->err(array($fedoraError,$client->request, __FILE__,__LINE__));
				return false;
			}	
			
		} else { */
//			echo APP_FEDORA_MANAGEMENT_WSDL_API;
//			print_r($parms);
			try {
				$client = new SoapClient(APP_FEDORA_MANAGEMENT_WSDL_API, array("login" => APP_FEDORA_USERNAME, "password" => APP_FEDORA_PWD, 'trace' => 1));
				$result = $client->__soapCall($call, array('parameters' => $parms));
				$result = array_values(Misc::obj2array($result));
				$result = $result[0];
			} catch (SoapFault $fault) { 
//				$fedoraError = "Error when calling ".$call." :".$fault->faultstring;
        $current_tries++;
				$fedoraError = "Error when calling ".$call." :".$fault->getMessage()."\n\n \n\n FOR THE $current_tries time REQUEST: \n\n".$client->__getLastRequest()."\n\n RESPONSE: \n\n ".$client->__getLastResponse();
				$log->err(array($fedoraError, __FILE__,__LINE__));
        // If it's an object locked exception.. some other thread is trying to modify it, so wait and try again as long as it hasn't been tried at least 4 times already
        if (is_numeric(strpos($fault->getMessage(), "fedora.server.errors.ObjectLockedException")) && $current_tries < 5) {
          sleep(5); // sleep for a bit so the object can get unlocked before trying again
          return Fedora_API::openSoapCall($call, $parms, $debug_error, $current_tries);
        } else {
          return false;
        }
			}	
		return $result;
	}

	/**
	 * Passes as Soap call to the NUSOAP engine through to the Fedora Access webservice API-A
	 *
	 * @access  public
	 * @param array $call The name of the fedora web service to call
	 * @param array $parms The parameters
	 * @return array $result
	 */
	function openSoapCallAccess ($call, $parms, $current_tries=0)
	{
		$log = FezLog::get();
		
		/********************************************
		 * This is a primary function called by all of
		 * the preceding functions.
		 * $call is the api call to the fedora api-a.
		 ********************************************/
/*		$client = new soapclient_internal(APP_FEDORA_ACCESS_API);
		$client->setCredentials(APP_FEDORA_USERNAME, APP_FEDORA_PWD);
		$client->namespaces['fedora-types'] = 'http://www.fedora.info/definitions/1/0/types/';
		$result = $client->call($call, $parms, 'http://www.fedora.info/definitions/1/0/types/');
		//Fedora_API::debugInfo($client);
		if (is_array($result) && isset($result['faultcode'])) {
			$fedoraError = "Error when calling ".$call." :".$result['faultstring'];
			$log->err(array($fedoraError,$client->request, __FILE__,__LINE__));
		}return $result;
*/
		try {
//			$client = new SoapClient(APP_FEDORA_ACCESS_WSDL_API, array("login" => APP_FEDORA_USERNAME, "password" => APP_FEDORA_PWD));
			$client = new SoapClient(APP_FEDORA_ACCESS_WSDL_API, array("login" => APP_FEDORA_USERNAME, "password" => APP_FEDORA_PWD, 'trace' => 1));
			$result = $client->__soapCall($call, array('parameters' => $parms));
			$result = array_values(Misc::obj2array($result));
			$result = $result[0];
		} catch (SoapFault $fault) {
      $current_tries++;
			$fedoraError = "Error when calling ".$call." :".$fault->getMessage()."\n\n FOR THE $current_tries time  \n\n REQUEST: \n\n".$client->__getLastRequest()."\n\n RESPONSE: \n\n ".$client->__getLastResponse();
      $log->err(array($fedoraError, __FILE__,__LINE__));
      // If it's an object locked exception.. some other thread is trying to modify it, so wait and try again as long as it hasn't been tried at least 4 times already
      if (is_numeric(strpos($fault->getMessage(), "fedora.server.errors.ObjectLockedException")) && $current_tries < 5) {
        sleep(5); // sleep for a bit so the object can get unlocked before trying again
        return Fedora_API::openSoapCallAccess($call, $parms, $current_tries);
      } else {
        return false;
      }
		}
    return $result;
	}

	/**
	 * This function provides SOAP debug statements.
	 *
	 * @access  public
	 * @param array $client The soap object
	 * @return void (Outputs debug info to screen).
	 */
	function debugInfo($client, $asString=false) 
	{
		$str =
            '<hr /><b>Debug Information</b><br /><br />'
            .'Request: <xmp>'.$client->request.'</xmp>'
            .'Response: <xmp>'.$client->response.'</xmp>'
            .'Debug log: <pre>'.$client->debug_str.'</pre>';
            if ($asString) {
            	return $str;
            } else {
            	echo $str;
            }
	}
} // end of Fedora_API Class
