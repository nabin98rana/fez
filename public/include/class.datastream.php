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

    //Add a datastream to a pid
    //$newFile is the file name in the temporary upload directory
    //$filesFezACMLNum the security template number applied to the datastream, if empty it defaults to default
    public static function addDatastreamToPid($pid, $newFile, $fezACMLTemplateNum = null)
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
                Fedora_API::getUploadLocationByLocalRef($pid, $newFileName, $newFile, $newFileName, $mimetype, 'M', null, $versionable);
                Exiftool::saveExif($pid, $newFileName);
                if (is_file($newFile)) {
                    $deleteCommand = APP_DELETE_CMD." ".$deleteFile;
                    exec($deleteCommand);
                }
                if (is_integer($fezACMLTemplateNum)) {
                    Datastream::setfezACML($pid, $newFileName, $fezACMLTemplateNum);
                }
                Workflow::processIngestTrigger($pid, $newFileName, $mimetype);
                Record::setIndexMatchingFields($pid);
            } else {
                $log->err("File not created $newFile<br/>\n", __FILE__, __LINE__);
            }
        }
    }

    function setfezACML($pid, $dsID, $fezACMLTemplateNum) {
        $xmlObj = FezACML::getQuickTemplateValue($fezACMLTemplateNum);
        if ($xmlObj != false) {
            $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
            if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
                Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
                    $xmlObj, "text/xml", "true");
            } else {
                Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID,
                    "text/xml", "X",null,"true");
            }
        }

    }
}