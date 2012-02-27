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
// |          Peter Newman <Peter.Newman@cdu.edu.au>                      |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.error_handler.php");

class HandleRequestor {
	var $batchUpdateXMLDoc;
	var $batchCreateXMLDoc;
	var $batchDeleteXMLDoc;
	var $batchUpdateElement;
	var $batchCreateElement;
	var $batchDeleteElement;
	var $updateNumber;
	var $createNumber;
	var $deleteNumber;
	var $handlePrefix;
	var $processed;

	function initBaseXML($batchType)
	{

		$baseXMLDoc = new DomDocument();

		//Create pilin-request root node
		$root = $baseXMLDoc->createElement("pilin-request");
		$attr1 = $baseXMLDoc->createAttribute("xmlns:xsi");
		$attr1value = $baseXMLDoc->createTextNode("http://www.w3.org/2001/XMLSchema-instance");
		$attr1->appendChild($attr1value);
		$attr2 = $baseXMLDoc->createAttribute("xsi:noNamespaceSchemaLocation");
		$attr2value =  $baseXMLDoc->createTextNode("schema/pilin_request.xsd");
		$attr2->appendChild($attr2value);
		$root->appendChild($attr1);
		$root->appendChild($attr2);
		$baseXMLDoc->appendChild($root);

		//Create pilin version node
		$versionNode = $baseXMLDoc->createElement("version");
		$attr1 = $baseXMLDoc->createAttribute("id");
		$attr1value = $baseXMLDoc->createTextNode("0.1");
		$attr1->appendChild($attr1value);
		$versionNode->appendChild($attr1);
		$root->appendChild($versionNode);

		//Create handle namespace node
		$handleNamespaceNode = $baseXMLDoc->createElement("handle-namespace");
		$attr1 = $baseXMLDoc->createAttribute("namespace");
		$attr1value = $baseXMLDoc->createTextNode($this->handlePrefix);
		$attr1->appendChild($attr1value);
		$handleNamespaceNode->appendChild($attr1);
		$root->appendChild($handleNamespaceNode);

		$element = $baseXMLDoc->createElement($batchType);
		$root->appendChild($element);

		return array($element, $baseXMLDoc);
	}

	function __construct($handleNamespace, $handleDerivative = "") 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$this->handlePrefix = $handleNamespace.$handleDerivative;

		$stmt = "SELECT count(rek_handle) FROM ". APP_TABLE_PREFIX ."record_search_key_handle ";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->notice(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			$log->err($ex);
		}

		if ($handleNamespace == 0) {
			$log->err(array("Handle name space not defined: ", __FILE__, __LINE__));
		} else {
			list ($this->batchUpdateElement, $this->batchUpdateXMLDoc) = $this->initBaseXML( "batch-update-handle" );
			list ($this->batchCreateElement, $this->batchCreateXMLDoc) = $this->initBaseXML( "batch-create-handle" );
			list ($this->batchDeleteElement, $this->batchDeleteXMLDoc) = $this->initBaseXML( "batch-delete-handle" );
			$this->updateNumber = 0;
			$this->createNumber = 0;
			$this->deleteNumber = 0;
			$this->processed = false;
		}
	}

	function isJAHDLPresent() 
	{
		$log = FezLog::get();
		
		$command = "java -jar ".APP_JAHDL_DIR."lib/jahdl_1.0.jar";
		$return_status = 0;
		$return_array = array();
		$result = exec($command." 2>&1", $result_array, $return_status);
		if ( ($return_status == 255) && (substr_compare($result_array[0], "usage", 0, 5) == 0) ) {
			return true;
		}

		$log->err(array("JAHDL Setup Error: is the path to the JAHDL root directory setup properly?\n Return status = ".$return_status.
				", for command ".$command." with result:\n".$result."\n", __FILE__,__LINE__));
		return false;
	}

	function validHandlePrefix($handle) 
	{
		if ( substr_compare($handle, $this->handlePrefix, 0, strlen($this->handlePrefix)) == 0 ) {
			return true;
		}
		return false;
	}

	function getHandleSuffix($handle) 
	{
		return substr( $handle, strlen($this->handlePrefix."/") );
	}

	function getUpdateHandleRequestXML() 
	{
		return $this->batchUpdateXMLDoc->saveXML();
	}

	function getCreateHandleRequestXML() 
	{
		return $this->batchCreateXMLDoc->saveXML();
	}

	function getDeleteHandleRequestXML() 
	{
		return $this->batchDeleteXMLDoc->saveXML();
	}

	function newHandleElement($dom, $handleSuffix, $URL="") 
	{

		$handleElement = $dom->createElement("handle");
		$attr1 = $dom->createAttribute("suffix");
		$attr1value = $dom->createTextNode($handleSuffix);
		$attr1->appendChild($attr1value);
		$handleElement->appendChild($attr1);
		 
		if ($URL != "") {
			$handleValueElement = $dom->createElement("handleValue");
			$attr1 = $dom->createAttribute("type");
			$attr1value = $dom->createTextNode("URL");
			$attr1->appendChild($attr1value);
			$handleValueElement->appendChild($attr1);
			$attr2 = $dom->createAttribute("value");
			$attr1value = $dom->createTextNode($URL);
			$attr2->appendChild($attr1value);
			$handleValueElement->appendChild($attr2);
			 
			$handleElement->appendChild($handleValueElement);
		}

		return $handleElement;
	}

	function newHandleDeleteElement($dom, $handle) 
	{

		$handleDeleteElement = $dom->createElement("deleteHandle");
		$attr1 = $dom->createAttribute("suffix");
		$attr1value = $dom->createTextNode($handle);
		$attr1->appendChild($attr1value);
		$handleDeleteElement->appendChild($attr1);
		return $handleDeleteElement;
	}

	function addUpdateHandleRequest($handle, $URL) 
	{
		//  This function changes the URL for a handle
		if ( ! $this->validHandlePrefix($handle)) {
			return false;
		}
		$handleSuffix = $this->getHandleSuffix($handle);
		echo "<p> HS: $handleSuffix </p>\n";
		$this->batchUpdateElement->appendChild($this->newHandleElement($this->batchUpdateXMLDoc, $handleSuffix, $URL));
		if ($this->batchUpdateElement == null) { return false; }
		$this->updateNumber++;
		return true;
	}

	function addCreateHandleRequest($handle, $URL) 
	{
		if ( ! $this->validHandlePrefix($handle)) {
			return false;
		}
		$handleSuffix = $this->getHandleSuffix($handle);
		$this->batchCreateElement->appendChild($this->newHandleElement($this->batchCreateXMLDoc, $handleSuffix, $URL));
		if ($this->batchCreateElement == null) { return false; }
		$this->createNumber++;
		return true;
	}

	function addDeleteHandleRequest($handle) 
	{

		// Note that Deleted are cached by proxy handle servers so will appear not to be deleted for a while

		if ( ! $this->validHandlePrefix($handle)) {
			return false;
		}
		$handleSuffix = $this->getHandleSuffix($handle);
		$this->batchDeleteElement->appendChild($this->newHandleDeleteElement($this->batchDeleteXMLDoc, $handleSuffix));
		if ($this->batchDeleteElement == null) { return false; }
		$this->deleteNumber++;
		return true;
	}

	function getHandleForPID($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT rek_handle FROM ". APP_TABLE_PREFIX ."record_search_key_handle WHERE rek_handle_pid = ".$db->quote($pid);
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return null;
		}
		if (sizeof($res) == 0) {
			$log->err(array("No PID found for Handle", __FILE__, __LINE__));
			return null;
		} else if (sizeof($res) > 1) {
			$log->err(array("Duplicate PIDs found in Handle search key table - BAD", __FILE__, __LINE__));
			return null;
		}
		return $res[0]['rek_handle'];
	}

	function isHandleSet($handle) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// This should really query the handle directly as this is flawed as handles could already have been created externally to this fez instance

		$stmt = "SELECT count(rek_handle_pid) as count FROM ". APP_TABLE_PREFIX ."record_search_key_handle WHERE rek_handle = ".$db->quote($handle);
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return null;
		}
		
		if (sizeof($res) == 0) {
			return false;
		} else if (sizeof($res) > 1) {
			$log->err(array("Duplicate PIDs found in Handle search key table - BAD", __FILE__, __LINE__));
			return null;
		}
		return ( $res[0]['count'] > 0 );
	}

	function changeHandle($oldHandle, $newHandle, $URL) 
	{
		if ( $this->isHandleSet($oldHandle) ) {
			$this->addDeleteHandleRequest($oldHandle);
		}
		$this->addCreateHandleRequest($newHandle, $URL);
	}

	function processHandleRequests() 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if ( $this->processed ) {
			$log->err(array("This Handle object has already been processed!", __FILE__, __LINE__));
			return false;
		}

		$baseCommand = "java -jar ".APP_JAHDL_DIR."lib/jahdl_1.0.jar";

		$updateTmpFile = APP_TEMP_DIR . "fez-handle-batch-update.xml";
		$createTmpFile = APP_TEMP_DIR . "fez-handle-batch-create.xml";
		$deleteTmpFile = APP_TEMP_DIR . "fez-handle-batch-delete.xml";
		$updateTmpLog = APP_TEMP_DIR . "fez-handle-batch-update.log";
		$createTmpLog = APP_TEMP_DIR . "fez-handle-batch-create.log";
		$deleteTmpLog = APP_TEMP_DIR . "fez-handle-batch-delete.log";
		$xml = "";

		if ($this->updateNumber > 0) {
			if ( file_exists( $updateTmpFile ) ) {
				$log->err(array($updateTmpFile." already exists - possible Handle process in progress or old tmp file not deleted!", __FILE__, __LINE__));
				return false;
			} else {
				$fileHandle = fopen($updateTmpFile, "w");
				fwrite( $fileHandle, $this->batchUpdateXMLDoc->saveXML() );
				fclose($fileHandle);
				$return_status = 0;
				$return_array = array();
				$command = join(" ", array($baseCommand, HANDLE_ADMPRIV_KEY_FILE , HANDLE_ADMPRIV_KEY_PASSPHRASE , $updateTmpFile , "batch-update-handle", $updateTmpLog) );
				$result = exec($command." 2>&1", $result_array, $return_status);
				if ( substr_compare($result, "Batch handle operation successfully executed", 0, 44) != 0 ) {
					if (substr_compare($result, "Saving global values to:", 0, 24) == 0 ) {
						$message = "\nNeed to make this directory writable by web server user: \n";
					} else {
		    $message = " with result:\n";
					}
					$log_message = file_get_contents($updateTmpLog);
					$log->err(array("JAHDL Test results: \n Return status = ".$return_status.", for command ".$command.$message.$result.
					  "\n and log file: \n".$log_message, __FILE__,__LINE__));
				}
			}
		}
		if ($this->createNumber > 0) {
			if ( file_exists( $createTmpFile ) ) {
				$log->err(array($createTmpFile." already exists - possible Handle process in progress or old tmp file not deleted!", __FILE__, __LINE__));
				return false;
			} else {
				$fileHandle = fopen($createTmpFile, "w");
				fwrite( $fileHandle, $this->batchCreateXMLDoc->saveXML() );
				fclose($fileHandle);
				$return_status = 0;
				$return_array = array();
				$command = join(" ", array($baseCommand, HANDLE_ADMPRIV_KEY_FILE , HANDLE_ADMPRIV_KEY_PASSPHRASE , $createTmpFile , "batch-create-handle", $createTmpLog) );
				$result = exec($command." 2>&1", $result_array, $return_status);
				if ( substr_compare($result, "Batch handle operation successfully executed", 0, 44) != 0 ) {
					if (substr_compare($result, "Saving global values to:", 0, 24) == 0 ) {
						$message = "\nNeed to make this directory writable by web server user: \n";
					} else {
		    $message = " with result:\n";
					}
					$log_message = file_get_contents($createTmpLog);
					$log->err(array("JAHDL Test results: \n Return status = ".$return_status.", for command ".$command.$message.$result.
					  "\n and log file: \n".$log_message, __FILE__,__LINE__));
				}
			}
		}
		if ($this->deleteNumber > 0) {
			if ( file_exists( $deleteTmpFile ) ) {
				$log->err(array($deleteTmpFile." already exists - possible Handle process in progress or old tmp file not deleted!: ", null, __FILE__, __LINE__));
				return false;
			} else {
				$fileHandle = fopen($deleteTmpFile, "w");
				fwrite( $fileHandle, $this->batchDeleteXMLDoc->saveXML() );
				fclose($fileHandle);
				$return_status = 0;
				$return_array = array();
				$command = join(" ", array($baseCommand, HANDLE_ADMPRIV_KEY_FILE , HANDLE_ADMPRIV_KEY_PASSPHRASE , $deleteTmpFile , "batch-delete-handle", $deleteTmpLog) );
				$result = exec($command." 2>&1", $result_array, $return_status);
				if ( substr_compare($result, "Batch handle operation successfully executed", 0, 44) != 0 ) {
					if (substr_compare($result, "Saving global values to:", 0, 24) == 0 ) {
						$message = "\nNeed to make this directory writable by web server user: \n";
					} else {
						$message = " with result:\n";
					}
					$log_message = file_get_contents($deleteTmpLog);
					$log->err(array("JAHDL Test results: \n Return status = ".$return_status.", for command ".$command.$message.$result.
					    "\n and log file: \n".$log_message, __FILE__,__LINE__));
				}
			}
		}

		if (file_exists($updateTmpFile)) { copy($updateTmpFile, "/tmp/saved-update.xml"); }
		if (file_exists($deleteTmpFile)) { copy($deleteTmpFile, "/tmp/saved-delete.xml"); }
		if (file_exists($createTmpFile)) { copy($createTmpFile, "/tmp/saved-create.xml"); }
		@unlink($updateTmpFile);
		@unlink($createTmpFile);
		@unlink($deleteTmpFile);

		$this->processed = true;
		return true;
	}

}
