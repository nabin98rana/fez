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
            exit;
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
	function getObjectXML($pid) {

        $result = $this->dbh->getOne("SELECT path FROM objectPaths WHERE token = '".$pid."'");
        if (PEAR::isError($result)) {
                return "";
        }

		$xml = "";
		$xml = file_get_contents($result);
		$this->xml = $xml;
		echo $xml;
        return $xml;
    }

	function getDatastreamDissemination($pid, $dsID) {
		
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
        $dvs = $xpath->query("//foxml:datastream[@ID='".$dsID."']/datastreamVersion[@ID]");

/*        foreach ($dvs as $dv) {
            $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $dv); 
            if ($dv->firstChild->nodeValue == "[TBG]") {
                // Assemble $historyDetail
			}
		}
*/		
		
		
	}


}

?>
