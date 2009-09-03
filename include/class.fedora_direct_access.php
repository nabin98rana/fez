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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.misc.php");

/**
 * Fedora_Direct_Access
 *
 * This class is to address some short-comings of the Fedora API, most notably, the extremely
 * high fetch times for simple operations like retrieving a comprehensive list of objects in the
 * repository. Until such time as performance of these functions can be improved, we are going
 * to connect directly to Fedora to pluck out what we want.
 */

class Fedora_Direct_Access {

	/**
	 * Fedora_Direct_Access
	 *
	 * This method sets up the database connection.
	 */
	function Fedora_Direct_Access() 
	{
		$this->pid = "";
		$this->xml = "";

		return;
	}



	/**
	 * fetchAllFedoraPIDs
	 *
	 * This method returns a list of all PIDs in Fedora (provided they are not deleted), along
	 * with each object's title.
	 */
	function fetchAllFedoraPIDs($terms = "", $object_state = 'A') 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		$terms = str_replace("*", "", $terms);  // Get the search terms ready for SQLage.
		$state_sql = "";
		if ($object_state != "") {
			$state_sql = " AND doState = ".$db->quote($object_state);
		}
		
		$no_result = false;
		try {
			$stmt = "SELECT doregistry.dopid AS pid, label AS title, doState as dostate FROM doregistry, dobj WHERE doregistry.doPID = dobj.doPID AND (doregistry.dopid LIKE ".$db->quote("%" . $terms . "%")." OR label LIKE ".$db->quote("%" . $terms . "%").") ".$state_sql;
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$no_result = true;
		}
		
		if($no_result) {
			try {
				"SELECT doRegistry.dopid AS pid, label AS title, doState AS dostate FROM doRegistry, dobj WHERE doRegistry.doPID = dobj.doPID AND (doRegistry.dopid LIKE ".$db->quote("%" . $terms . "%")." OR label LIKE ".$db->quote("%" . $terms . "%").") ".$state_sql;
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				
				$log->err($ex);
				return array();
			}
		}
		
		return $res;
	}

	/**
	 * getObjectXML
	 *
	 */
	function getObjectXML($pid, $refresh = false) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		static $returns;
		if (!is_array($returns)) {
			$returns = array();
		}
		if (count($returns) > 10) {
			$returns = array();
		}
		if ($refresh != true && isset($returns[$pid]) && ($returns[$pid] != "")) {
			$this->xml = $returns[$pid];
			return $returns[$pid];
		}
		
		try {
			$stmt = "SELECT path FROM objectPaths WHERE token = ".$db->quote($pid);
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		$xml = "";
		$xml = file_get_contents($res);
		$this->xml = $xml;
		$returns[$pid] = $xml;
		return $xml;
	}

	function objectExists($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		try {
			$stmt = "SELECT path FROM objectPaths WHERE token = ?";
			$res = $db->fetchOne($stmt, $pid);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res == "") {
			$xml = false;
		} else {
			$xml = true;
		}
		return $xml;
	}

	function isDeleted($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		try {
			$stmt = "SELECT dostate FROM dObj WHERE dopid = ?";
			$res = $db->fetchOne($stmt, $pid);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res == "D") {
			$xml = true;
		} else {
			$xml = false;
		}
		return $xml;
	}


	function getDatastreamManagedContent($pid, $dsID, $dsVersionID) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		try {
			$stmt = "SELECT path FROM datastreampaths WHERE token = ".$db->quote($pid."+".$dsID."+".$dsID.".".$dsVersionID);
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		if ($res == "") {
			return "";
		}
		$xml = "";
		$xml = file_get_contents($res);
		return $xml;
	}


	function getDatastreamManagedContentPath($pid, $dsID, $dsVersionID) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		try {
			$stmt = "SELECT path FROM datastreampaths WHERE token = ".$db->quote($pid."+".$dsID."+".$dsID.".".$dsVersionID);
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res == '') {
			return '';
		}
		return $res;
	}

	// like many of these functions requires the fedora path to be available to the apache/php webserver
	function getDatastreamManagedContentStream($pid, $dsID, $dsVersionID, $seekPos) 
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');

		try {
			$stmt = "SELECT path FROM datastreampaths WHERE token = ".$db->quote($pid."+".$dsID."+".$dsID.".".$dsVersionID);
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res == '') {
			return '';
		}
		$fh = fopen($res, 'rb');
		$size = filesize($res);
		# output file
		echo stream_get_contents($fh, $size, $seekPos);
			
	}


	function getMaxDatastreamVersion($pid, $dsID) 
	{
		if ($this->pid != $pid || $this->xml == "") {
			$this->getObjectXML($pid);
		}
		if ($this->xml == "") {
			return false;
		}
		$xmldoc= new DomDocument();
		$xmldoc->preserveWhiteSpace = false;
		$xmldoc->loadXML($this->xml);

		$xpath = new DOMXPath($xmldoc);
		$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion/@ID";
		$dvs = $xpath->query($dvStmt);
		$maxVersion = 0;
		foreach ($dvs as $dv) {

			$tempNum = substr($dv->nodeValue, (strpos($dv->nodeValue, $dsID.".") + strlen($dsID.".")));
			//			echo "MAX HERE-".$dsID."-".$dv->nodeValue."-".$tempNum."-\n";
			if (is_numeric($tempNum)) {
				if ($tempNum > $maxVersion) {
					$maxVersion = $tempNum;
				}
			}
		}
		return $maxVersion;
	}

	function getDatastreamDissemination($pid, $dsID, $pmaxDV="") 
	{

		if ($this->pid != $pid || $this->xml == "") {
			$this->getObjectXML($pid);
		}

		if ($this->xml == "") {
			return false;
		}

		if ($pmaxDV == "") {
			$maxDV = $this->getMaxDatastreamVersion($pid, $dsID);
		} else {
			$maxDV = $pmaxDV;
		}

		$xmldoc= new DomDocument();
		$xmldoc->preserveWhiteSpace = false;
		$xmldoc->loadXML($this->xml);

		$xpath = new DOMXPath($xmldoc);

		$mContent = $this->getDatastreamManagedContent($pid, $dsID, $maxDV);
		if ($mContent != "") {
			return array(
                'MIMEType'  =>  'raw',
                'stream'    =>  $mContent,
			);
		}
		if ($maxDV !== "1.0") {
			$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:xmlContent/*";
		} elseif ($maxDV === "1.0" && $dsID == 'FezACML') {
			$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='FezACML".$maxDV."']/foxml:xmlContent/*";
		} else {
			//$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.$maxDV."']/foxml:xmlContent/*";
			$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='Fez".$maxDV."']/foxml:xmlContent/*";
		}
		$dvs = $xpath->query($dvStmt); // returns nodeList
		$xmlContent = new DomDocument();
		$found = false;
		foreach ($dvs as $dv) {
			$found = true;
			$xmlContent->appendChild($xmlContent->importNode($dv,true));
		}

		$xml = "";
		if ($found == true) {

			$xml =  $xmlContent->saveXML();
			return array(
                'MIMEType'  =>  'text/xml',
                'stream'    =>  $xml,
			);

		} elseif ($pmaxDV == "") {

			return $this->getDatastreamDissemination($pid, $dsID, "1.0");

		}
	}

	function listDatastreams($pid) 
	{
		$dsList = array();
		$datastreams = $this->getDatastreams($pid);
		foreach ($datastreams as $ds) {
			array_push($dsList, array("dsid" => $ds["ID"], "label" => $ds["LABEL"], "mimeType" => $ds["MIMEType"]));
		}
		return $dsList;
	}


	function getDatastreams($pid, $maxDV="") 
	{
		if ($this->pid != $pid || $this->xml == "") {
			$this->getObjectXML($pid);
		}
		if ($this->xml == "") {
			return false;
		}

		$xmldoc= new DomDocument();
		$xmldoc->preserveWhiteSpace = false;
		$xmldoc->loadXML($this->xml);

		$xpath = new DOMXPath($xmldoc);
		//		$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:xmlContent/".$dsID;
		//$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:xmlContent/*";
		//$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".0']/*";
		$dvStmt = "/foxml:digitalObject/foxml:datastream";
		//		echo $dvStmt;
		$ds = $xpath->query($dvStmt); // returns nodeList
		//				$xmlContent = new DomDocument();
		$datastreams = array();
		foreach ($ds as $dsNode) {
			$controlGroup = $dsNode->getAttribute('CONTROL_GROUP');
			$ID = $dsNode->getAttribute('ID');
			$state = $dsNode->getAttribute('STATE');
			$versionable = $dsNode->getAttribute('VERSIONABLE');
			//echo $ID."\n";
			$maxDV = $this->getMaxDatastreamVersion($pid, $ID);
			$dvStmt = "./foxml:datastreamVersion[@ID='".$ID.".".$maxDV."']";
			$dv = $xpath->query($dvStmt, $dsNode); // returns nodeList
			foreach ($dv as $dvNode) {
				//$versionID = $dvNode->getAttribute('versionID');
				$versionID = $dvNode->getAttribute('ID');
				//                   echo $versionID;
				$altIDs = $dvNode->getAttribute('ALT_IDS');
				$label = $dvNode->getAttribute('LABEL');
				$MIMEType = $dvNode->getAttribute('MIMETYPE');
				$formatURI = $dvNode->getAttribute('FORMAT_URI');
				$createDate = $dvNode->getAttribute('CREATED');
				$size = $dvNode->getAttribute('SIZE');
				$checksumType = $dvNode->getAttribute('CHECKSUM_TYPE');
				$checksum = $dvNode->getAttribute('CHECKSUM');
				if ($controlGroup == 'R') {
					$lcStmt = "./foxml:contentLocation";
					$lc = $xpath->query($lcStmt, $dvNode); // returns nodeList
					foreach ($lc as $lcNode) {
						$location = $lcNode->getAttribute('REF');
					}
				} else {
					$location = "";
				}
			}

			array_push($datastreams, array("controlGroup" => $controlGroup,
                                                   "ID" => $ID,
                                                   "versionID" => $versionID,
                                                   "altIDs" => $altIDs,
                                                   "label" => $label,
                                                   "versionable" => $versionable,
                                                   "MIMEType" => $MIMEType,
                                                   "formatURI" => $formatURI,
                                                   "createDate" => $createDate,
                                                   "size" => $size,
                                                   "state" => $state,
                                                   "location" => $location,
                                                   "checksumType" => $checksumType,
                                                   "checksum" => $checksum));


			//					$xmlContent->appendChild($xmlContent->importNode($dv,true));
		}
		//		print "<pre>" . htmlspecialchars($xmlContent->saveXML()) . "</pre>";
		//				return $xmlContent->saveXML();
		return $datastreams;
	}
}
