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
    function Fedora_Direct_Access() {

		$dsn = array(
            'phptype'  => FEDORA_DB_TYPE,
            'hostspec' => FEDORA_DB_HOST,
            'database' => FEDORA_DB_DATABASE_NAME,
            'username' => FEDORA_DB_USERNAME,            
            'password' => FEDORA_DB_PASSWD,
            'port'     => FEDORA_DB_PORT
        );
		$options = array('persistent' => false);
		$this->pid = "";
		$this->xml = "";
        $this->dbh = DB::connect($dsn, options);
        if (PEAR::isError($this->dbh)) {
            Error_Handler::logError(array($this->dbh->getMessage(), $this->dbh->getDebugInfo()), __FILE__, __LINE__);
            $error_type = "db";
            //include_once(APP_PATH . "offline.php");
        }
        return;
    }



    /**
     * fetchAllFedoraPIDs
     *
     * This method returns a list of all PIDs in Fedora (provided they are not deleted), along 
     * with each object's title.
     */
	function fetchAllFedoraPIDs($terms = "", $object_state = 'A') {

        $terms = Misc::escapeString(str_replace("*", "", $terms));  // Get the search terms ready for SQLage.
		$state_sql = "";
		if ($object_state != "") {			
			$state_sql = " AND objectState = '".$object_state."'";
		}
        $result = $this->dbh->getAll("SELECT dopid AS pid, label AS title, objectstate FROM doregistry WHERE (dopid LIKE '%" . $terms . "%' OR label LIKE '%" . $terms . "%') ".$state_sql, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($result)) {
            // Attempt the same thing with the other known table spelling.
            $result = $this->dbh->getAll("SELECT dopid AS pid, label AS title, objectstate FROM doRegistry WHERE (dopid LIKE '%" . $terms . "%' OR label LIKE '%" . $terms . "%') ".$state_sql, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($result)) {
                Error_Handler::logError(array($result->getMessage(), $result->getDebugInfo()), __FILE__, __LINE__);			
                return array();
            }
        }
        return $result;
    }

    /**
     * getObjectXML
     *
     */
	function getObjectXML($pid, $refresh = false) {
        static $returns;
        if (!is_array($returns)) {
            $returns = array();
        }
        if (count($returns) > 10) {
           echo "here";
            $returns = array();
        }
        if ($refresh != true && isset($returns[$pid]) && ($returns[$pid] != "")) {
            $this->xml = $returns[$pid];
            return $returns[$pid];
        }

        $result = $this->dbh->getOne("SELECT path FROM objectPaths WHERE token = '".$pid."'");
        if (PEAR::isError($result)) {
                return "";
        }
		$xml = "";
		$xml = file_get_contents($result);
		$this->xml = $xml;
//		echo $xml;
        $returns[$pid] = $xml;
        return $xml;
    }

    function objectExists($pid) {
        $result = $this->dbh->getOne("SELECT path FROM objectPaths WHERE token = '".$pid."'");
        if (PEAR::isError($result)) {
                return "";
        }
        if ($result == "") {
           $xml = false;
        } else {
           $xml = true;
        }
        return $xml;
    }

    function isDeleted($pid) {
        $result = $this->dbh->getOne("SELECT dostate FROM dObj WHERE dopid = '".$pid."'");
        if (PEAR::isError($result)) {
                return "";
        }
        if ($result == "D") {
           $xml = true;
        } else {
           $xml = false;
        }
        return $xml;
    }


    function getDatastreamManagedContent($pid, $dsID, $dsVersionID) {

        $result = $this->dbh->getOne("SELECT path FROM datastreampaths WHERE token = '".$pid."+".$dsID."+".$dsID.".".$dsVersionID."'");
        if (PEAR::isError($result)) {
                return "";
        }
        if ($result == "") {
          return "";
        }
        $xml = "";
        $xml = file_get_contents($result);
//        $this->xml = $xml;
//      echo $xml;
        return $xml;
    }


	function getMaxDatastreamVersion($pid, $dsID) {
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

	function getDatastreamDissemination($pid, $dsID, $pmaxDV="") {
		
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
//		echo $dsID.$maxDV;
		$xmldoc= new DomDocument();
		$xmldoc->preserveWhiteSpace = false;
		$xmldoc->loadXML($this->xml);
		
        $xpath = new DOMXPath($xmldoc);
//		$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:xmlContent/".$dsID;

/*		$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:contentLocation[@TYPE='INTERNAL_ID']";		
        $dvs = $xpath->query($dvStmt); // returns nodeList

        foreach ($dvs as $dv) {
            $location = $dvNode->getAttribute('REF');
           
        }
*/
        $mContent = $this->getDatastreamManagedContent($pid, $dsID, $maxDV);
        if ($mContent != "") {
            return $mContent;
        }
//  echo $dsID." - ".$maxDV."\n";
        if ($maxDV != "1.0") {
		  $dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.".".$maxDV."']/foxml:xmlContent/*";
//echo $dvStmt."\n";
        } else {
//echo "hfdsere".$maxDV;
		  //$dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='".$dsID.$maxDV."']/foxml:xmlContent/*";
		  $dvStmt = "/foxml:digitalObject/foxml:datastream[@ID='".$dsID."']/foxml:datastreamVersion[@ID='"."Fez".$maxDV."']/foxml:xmlContent/*";
//		echo $dvStmt; exit;
        }		
        $dvs = $xpath->query($dvStmt); // returns nodeList
		$xmlContent = new DomDocument();
        $found = false;
        foreach ($dvs as $dv) {
            $found = true;
			$xmlContent->appendChild($xmlContent->importNode($dv,true));
		}
//		print "<pre>" . htmlspecialchars($xmlContent->saveXML()) . "</pre>";
        $xml = "";
        if ($found == true) {
//  echo $dsID." - ".$maxDV."\n";

          $xml =  $xmlContent->saveXML();
        } elseif ($pmaxDV == "") {
          $xml = $this->getDatastreamDissemination($pid, $dsID, "1.0");
        }
		return $xml;	
	}

    function listDatastreams($pid) {
        $dsList = array();
        $datastreams = $this->getDatastreams($pid);
        foreach ($datastreams as $ds) {
            array_push($dsList, array("dsid" => $ds["ID"], "label" => $ds["LABEL"], "mimeType" => $ds["MIMEType"]));
        }
        return $dsList;
    }


	function getDatastreams($pid, $maxDV="") {
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

?>
