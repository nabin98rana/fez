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
// | Authors: Elvi Shu <e.shu@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle access to WoK service
 *
 * @version 1.0
 * @author Elvi Shu<e.shu@library.uq.edu.au>
 *
 */
class Wok {

    /**
     * Returns a list of WoK Document types
     * @return array|string
     */
    public function getAssocDocTypes()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        static $returns;

        if (!empty($returns)) {
          return $returns;
        }

        $stmt = "SELECT
                        wdt_code,
                        concat_ws(' - ',   wdt_code, wdt_description) as doctype
                     FROM
                        " . APP_TABLE_PREFIX . "wok_doctypes
                     ORDER BY
                        wdt_description";

        try {
            $res = $db->fetchPairs($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        if ($GLOBALS['app_cache']) {
            // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
            if (!is_array($returns) || count($returns) > 10) {
                $returns = array();
            }
            $returns = $res;
        }
        return $res;
    }

    /**
 * Returns the description of a WoK doc type code.
 *
 * @param int $wdt_code WoK Doc Type code
 * @return string The description of a Wok Doc Type code
 */
    public function getTitle($wdt_code)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    wdt_description
                 FROM
                    " . APP_TABLE_PREFIX . "wok_doctypes
                 WHERE
                    wdt_code=".$db->quote($wdt_code, 'STRING');
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }

    /**
     * Returns the WoK doc type code given a description.
     *
     * @param int $wdt_code WoK Doc Type code
     * @return string The description of a Wok Doc Type code
     *
     * This is not one to one so the results might be considered unknown
     */
    public function getDoctype($wdt_description)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    wdt_code
                 FROM
                    " . APP_TABLE_PREFIX . "wok_doctypes
                 WHERE
                    wdt_description=".$db->quote($wdt_description, 'STRING');
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }
}
