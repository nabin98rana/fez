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

/**
 * Class designed to handle all business logic related to the re-indexing of records in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");


/**
  * Reindex
  */
class Reindex
{
    /**
     * Method used to get the list of PIDs in the Fez index.
     *
     * @access  public
     * @return  array The list of pids in the Fez index.
     */
    function getIndexPIDList()
    {
        $stmt = "SELECT
                    distinct rmf_rec_pid
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of PIDs in Fedora that are not in the Fez index.
     *
     * @access  public
     * @return  array The list.
     */
    function getMissingList()
    {	
		$fezIndexPIDs = Reindex::getIndexPIDList();
        $itql = "select \$title \$description \$object from <#ri>
                 where ((\$object <dc:title> \$title) and
                    (\$object <dc:description> \$description))
					order by \$object asc";

        $returnfields = array();
        array_push($returnfields, "pid");
        array_push($returnfields, "title");
        array_push($returnfields, "description");
        $details = Fedora_API::getITQLQuery($itql, $returnfields);
		$return = array();
		foreach ($details as $detail) {
			if (!in_array($detail['pid'], $fezIndexPIDs)) {
				array_push($return, $detail);
			}
		}
		return $return;		
	}

    /**
     * Method used to get the list of PIDs in Fedora that are not in the Fez index.
     *
     * @access  public
     * @return  array The list.
     */
    function getFullList()
    {	
		$fezIndexPIDs = Reindex::getIndexPIDList();
        $itql = "select \$title \$description \$object from <#ri>
                 where ((\$object <dc:title> \$title) and
                    (\$object <dc:description> \$description))
					order by \$object asc";

        $returnfields = array();
        array_push($returnfields, "pid");
        array_push($returnfields, "title");
        array_push($returnfields, "description");
        $details = Fedora_API::getITQLQuery($itql, $returnfields);

		$return = array();
		foreach ($details as $detail) {
			if (in_array($detail['pid'], $fezIndexPIDs)) {
				array_push($return, $detail);
			}
		}
		return $return; 
	}

    /**
     * Method used to reindex a batch of pids in fedora into Fez.
     *
     * @access  public
     * @return  boolean
     */
    function indexFedoraObjects()
    {
        global $HTTP_POST_VARS;

        $items = @$HTTP_POST_VARS["items"];
		$xdis_id = @$HTTP_POST_VARS["xdis_id"];
		$sta_id = @$HTTP_POST_VARS["sta_id"];		
		$community_pid = @$HTTP_POST_VARS["community_pid"];
		$collection_pid = @$HTTP_POST_VARS["collection_pid"];

		foreach ($items as $pid) {
			// even if the Fedora object has a RELS-EXT record replace it with a new one based on the chosen destination collection.
			$relsext = Reindex::buildRELSEXT($collection_pid, $pid);
			$fezmd = Reindex::buildFezMD($xdis_id);			
			if (Fedora_API::datastreamExists($pid, "RELS-EXT")) {
				Fedora_API::callModifyDatastreamByValue($pid, "RELS-EXT", "A", "Relationships to other objects", $rels-ext, "text/xml", true);
			} else {
				Fedora_API::callAddDatastream($pid, "RELS-EXT", $relsext, "Relationships to other objects", "A", "text/xml", "X");
			}
			if (Fedora_API::datastreamExists($pid, "FezMD")) {
				Fedora_API::callModifyDatastreamByValue($pid, "FezMD", "A", "Fez extension metadata", $fezmd, "text/xml", true);
			} else {
				Fedora_API::callAddDatastream($pid, "FezMD", $fezmd, "Fez extension metadata", "A", "text/xml", "X");
			}
			Record::removeIndexRecord($pid); // remove any existing index entry for that PID
			Record::setIndexMatchingFields($xdis_id, $pid);
		}
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true; 
        }
    }
	
    /**
     * Method used to reindex a batch of pids in fedora into Fez that appear to already by Fez objects
	 * eg 1. They have already got a RELS-EXT that points to an existing Fez collection
	 *    2. They have a FezMD datastream
     *
     * @access  public
     * @return  boolean
     */
    function indexFezFedoraObjects()
    {
        global $HTTP_POST_VARS;

        $items = @$HTTP_POST_VARS["items"];
		$override = @$HTTP_POST_VARS["override"];
		$xdis_id = @$HTTP_POST_VARS["xdis_id"];
		$sta_id = @$HTTP_POST_VARS["sta_id"];		
		$community_pid = @$HTTP_POST_VARS["community_pid"];
		$collection_pid = @$HTTP_POST_VARS["collection_pid"];

		foreach ($items as $pid) {
			// even if the Fedora object has a RELS-EXT record replace it with a new one based on the chosen destination collection.
			if (@$HTTP_POST_VARS["override"]) {
				$relsext = Reindex::buildRELSEXT($collection_pid, $pid);
				$fezmd = Reindex::buildFezMD($xdis_id);			
				if (Fedora_API::datastreamExists($pid, "RELS-EXT")) {
					Fedora_API::callModifyDatastreamByValue($pid, "RELS-EXT", "A", "Relationships to other objects", $rels-ext, "text/xml", true);
				} else {
					Fedora_API::callAddDatastream($pid, "RELS-EXT", $relsext, "Relationships to other objects", "A", "text/xml", "X");
				}
				if (Fedora_API::datastreamExists($pid, "FezMD")) {
					Fedora_API::callModifyDatastreamByValue($pid, "FezMD", "A", "Fez extension metadata", $fezmd, "text/xml", true);
				} else {
					Fedora_API::callAddDatastream($pid, "FezMD", $fezmd, "Fez extension metadata", "A", "text/xml", "X");
				}
			} else {
				$record = new RecordObject($pid);
				$xdis_id = $record->getXmlDisplayId();
				if (!is_numeric($xdis_id)) {
					$xdis_id = XSD_Display::getXDIS_IDByTitle('Generic Document');
				}
			}
			Record::removeIndexRecord($pid); // remove any existing index entry for that PID			
			Record::setIndexMatchingFields($xdis_id, $pid);
		}
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true; 
        }
    }	

	function buildRELSEXT($parent_pid, $pid) {
		$xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
					  xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
					  <rdf:description rdf:about="info:fedora/'.$pid.'">
						<rel:isMemberOf rdf:resource="info:fedora/'.$parent_pid.'"/>
					  </rdf:description>
					</rdf:RDF>';
		return $xml;
	}
	
	function buildFezMD($xdis_id, $sta_id) {
		$created_date = date("Y-m-d H:i:s");
		$updated_date = $created_date;
		$ret_id = 3; // standard record type id

		$xml = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
			    	<xdis_id>'.$xdis_id.'</xdis_id>
					<sta_id>'.$sta_id.'</sta_id>
					<ret_id>'.$ret_id.'</ret_id>
					<created_date>'.$created_date.'</created_date>					  
					<updated_date>'.$updated_date.'</updated_date>
				</FezMD>';
		return $xml;
	}	
}
// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Reindex Class');
}

?>
