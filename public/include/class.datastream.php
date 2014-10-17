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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . 'class.record.php');
include_once(APP_INC_PATH . 'class.fezacml.php');

/**
 * Class to handle datastreams
 */

class Datastream
{

    //Any changes here must be mirrored in swfuploader.js and changes to closed access (Currently only option 5) must be changed in security changes functions applied off these and javascript in upload_files.tpl.html
    public static $file_options = array(0 => 'Please choose file type', 1 => 'Accepted version (author final draft  post-refereeing)', 2  => 'Submitted version (author version pre-refereeing)',
        3 => 'Publishers Copy (Open Access)', 4 => 'Working/Technical Paper', 5 => 'HERDC evidence (not open access- admin only)', 6 => 'Data Collection (Open Access)',
        8 =>'Non-traditional research output Research Statement (Admin only)', 7 => 'Other (any files not included in any of the above)'
    );

    //Add a datastream to a pid
    //$newFile is the file name in the temporary upload directory
    //$filesFezACMLNum the security template number applied to the datastream, if empty it defaults to default
    public static function addDatastreamToPid($pid, $newFile, $description, $fezACMLTemplateNum = null)
    {
        $log = FezLog::get();
        if (!empty($newFile)) {
            if (Fedora_API::datastreamExists($pid, $newFile) && APP_VERSION_UPLOADS_AND_LINKS!="ON") {
                $newFileName = $newFile.time();
                //Fedora_API::callPurgeDatastream($pid, $newFile);
            } else {
                $newFileName = $newFile;
            }
            $deleteFile = APP_TEMP_DIR.$newFile;
            $newFile = APP_TEMP_DIR.$newFile;
            if (file_exists($newFile)) {
                $mimetype = Misc::mime_content_type($newFile);
                $versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';
                Fedora_API::getUploadLocationByLocalRef($pid, $newFileName, $newFile, $description, $mimetype, 'M', null, $versionable);
                Exiftool::saveExif($pid, $newFileName);
                if (is_integer($fezACMLTemplateNum)) {
                    Datastream::setfezACML($pid, $newFileName, $fezACMLTemplateNum);
                }
                Workflow::processIngestTrigger($pid, $newFileName, $mimetype);
                Record::generatePresmd($pid, $newFileName);
                Record::setIndexMatchingFields($pid);
                if (is_file($newFile)) {
                    $deleteCommand = APP_DELETE_CMD." ".$deleteFile;
                    exec($deleteCommand);
                }
            } else {
                $log->err("File not created $newFile<br/>\n", __FILE__, __LINE__);
            }
        }
    }

    /**
     * Set the security on the datastream directly using fezACML xml.
     *
     * @param string $xml A valid fezACML xml document
     */
    static function setfezACMLXml($pid, $dsID, $xml)
    {
            $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
            if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
                Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
                                                    $xml, "text/xml", "true");
            } else {
            Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xml, "FezACML security for datastream - ".$dsID,
                    "text/xml", "X",null,"true");
            }
        }

    static function setfezACML($pid, $dsID, $fezACMLTemplateNum)
    {
        $xmlObj = FezACML::getQuickTemplateValue($fezACMLTemplateNum);
        if ($xmlObj != false) {
            return self::setfezACMLXml($pid, $dsID, $xmlObj);
        }

    }

    /**
     * Set the security on the datastream to inherit from parent.
     */
    static function setfezACMLInherit($pid, $dsID)
    {
        $xml = FezACML::makeQuickTemplateInherit();
        return self::setfezACMLXml($pid, $dsID, $xml);
    }

    //Removes permissions on datastream which makes it open access if the pid is accessible.
    static function removeFezACMLDatastream($pid, $dsID) {
        $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
        return Fedora_API::callPurgeDatastream($pid, $FezACML_dsID);
    }

    //Saves $permissions and $embargo date. Does nothing and returns with true if both are unchanged
    function saveDatastreamSelectedPermissions($pid, $dsId, $permissions, $embargoDate)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!empty($embargoDate)) {
            //Make sure the date is in English time -> mysql time
            $embargoDate = str_replace('/', '-', $embargoDate);
            $phpdate = strtotime( $embargoDate );
            $embargoDate = date( 'Y-m-d H:i:s', $phpdate );
        } else {
            $embargoDate = 'NULL';
        }

        $stmt = "
			SELECT * FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsId);

        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        if ($embargoDate != 'NULL') {
            $embargoDate = $db->quote($embargoDate);
        }
        if (is_array($res)) {
            if ($res['dsi_permissions'] == $permissions && $res['dsi_embargo_date'] == $embargoDate) {
                return true;
            } else {
                $stmt = "UPDATE " . APP_TABLE_PREFIX . "datastream_info SET
                    dsi_permissions = " . $db->quote($permissions) . ",
                    dsi_embargo_date = " . $embargoDate . "
                    WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsId);
                $historyDetail = 'Update '.$permissions.' to '.$dsId;

            }
        } else {
            $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "datastream_info (dsi_pid, dsi_dsid, dsi_permissions, dsi_embargo_date)
                    VALUES (". $db->quote($pid).",".$db->quote($dsId).",".$db->quote($permissions).",".$embargoDate. ")";
            $historyDetail = 'Add '.$permissions.' to '.$dsId;
        }

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        History::addHistory($pid, null, "", "", false, $historyDetail);
        return $res;
    }
    function getClassification($pid, $dsId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			SELECT dsi_permissions FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsId);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    static function getEmbargoDate($pid, $dsId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			SELECT dsi_embargo_date FROM " . APP_TABLE_PREFIX . "datastream_info
            WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsId);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    static function setEmbargoProcessed($pid, $dsId)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			UPDATE " . APP_TABLE_PREFIX . "datastream_info SET dsi_embargo_processed = 1
            WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsId);

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    static function embargoFileRename($pid, $dsIdOld, $dsIdNew)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			UPDATE " . APP_TABLE_PREFIX . "datastream_info SET dsi_dsid = ".$db->quote($dsIdNew)."
            WHERE dsi_pid = ".$db->quote($pid)." AND dsi_dsid = ".$db->quote($dsIdOld);

        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }


}