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
 * high fetch times for simple operations like retriving a comprehensive list of objects in the 
 * repository. Until such time as performance of these functions can be improved, we are going
 * to connect directly to Fedora to pluck out what we want.
 *
 * Note: This class is not currently being used, but may be used as-needed. config.inc.php
 * needs to be updated with the following declarations:

// Setup for direct Fedora access
//@define("FEDORA_DB_HOST", "dev-repo");
//@define("FEDORA_DB_TYPE", "mysql");         // mysql || postgres
//@define("FEDORA_DB_DATABASE_NAME", "dev_fedora");
//@define("FEDORA_DB_USERNAME", "setusernamehere");
//@define("FEDORA_DB_PASSWD", "setpasswdhere");

 *
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
            'password' => FEDORA_DB_PASSWD
        );

        $this->dbh = DB::connect($dsn);
        if (PEAR::isError($this->dbh)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $error_type = "db";
            //include_once(APP_PATH . "offline.php");
            exit;
        }
        return;
    }



    /**
     * fetchAllFedoraPIDs
     *
     * Returns a list of all PIDs in Fedora. These PIDs are reduced to integer form, with the 
     * leading namespace: component removed.
     *
     * Modification: The full PID is now returned; we no longer strip the namespace part.
     */
    function fetchAllFedoraPIDs() {

		$stmt = "SELECT DISTINCT(doPID) FROM do";
		$res = $this->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
	    if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);			
			return $res;
		}

        $fedoraPIDs = array();      // Array for storing the processed results.

        // Step through the results.
        foreach ($res as $PIDarray) {
            foreach ($PIDarray as $PIDarrayVal) {
                // Old way
                //$splitPID = explode(":", $PIDarrayVal);         // Extract the numerical component.
                //array_push($fedoraPIDs, $splitPID[1]);
                // New way
                array_push($fedoraPIDs, $PIDarrayVal);
            }            
        }
        return $fedoraPIDs;
    }

}

?>
