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


/**
 * Class to handle the business logic related to the administration
 * of authors in the system.
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */


class Reports
{
    function getReportList()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT * FROM
                    " . APP_TABLE_PREFIX . "reports";
        try {
            $res= $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;

    }

    function returnCSVFromId($queryID)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!is_numeric($queryID)) {
            return false;
        }

        $stmt = "SELECT * FROM
                    " . APP_TABLE_PREFIX . "reports
                 WHERE
                    sel_id=".$db->quote($queryID);
        try {
            $resQuery = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        try {
            $res = $db->fetchAll($resQuery['sel_query']);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        $this->outputCSV($res, str_replace(' ', '_', $resQuery['sel_title']));

    }

    function outputCSV(&$data, $filename = 'Report', $dateTime = true)
    {
        if ($dateTime) {
            $filename .= date('-Y-m-d-H.i.s');
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $title = $data[0];
        foreach ($title as $name => $value) {
            $titles[] = $name;
        }
        $outputBuffer = fopen("php://output", 'w');
        fputcsv($outputBuffer, $titles);
        foreach ($data as $val) {
            fputcsv($outputBuffer, $val);
        }
        fclose($outputBuffer);
    }
}