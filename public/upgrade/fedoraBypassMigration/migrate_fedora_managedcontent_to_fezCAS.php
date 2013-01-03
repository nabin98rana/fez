<?php
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
// | Author: Elvi Shu <e.shu@library.uq.edu.au>                           |
// +----------------------------------------------------------------------+

/**
 * The purpose of this script is to
 *   migrate Fedora managed contents (ie: PDFs, images, etc) to Fez CAS system.
 * Fez CAS system is storing file content in MD5 hash format and recording the file meta data in %TABLE_PREFIX%_file_attachments table.
 *
 * This is a one-off migration script as part of Fedora-less project.
 */
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.dsresource.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");
error_reporting(1);
set_time_limit(0);

// Get PIDs' Exif data from database table.
/*$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "exif where exif_pid = 'UQ:3718' ORDER BY exif_pid ";  //where exif_pid = 'UQ:21033'
try {
    $pid_exifs = $db->fetchAll($stmt);
} catch (Exception $ex) {
    $log->err($ex);
    echo "Failed to retrieve exif data. Error: " . $ex;
} */

$fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('','', true);
//$fedoraPids[] = array('pid' => 'UQ:17552');
$totalPids = count($fedoraPids);
$counter = 0;
foreach ($fedoraPids as $pid) {
    $pid = $pid['pid'];
    $counter++;
    $datastreams = Fedora_API::callGetDatastreams($pid);
    //$datastreams = Misc::cleanDatastreamListLite($datastreams, $pid);
    if (count($datastreams) > 0) {
      echo "\nDoing PID $counter/$totalPids ($pid)";
    }

    foreach ($datastreams as $datastream) {

        if ($datastream['controlGroup'] == 'M') {

            // Set datastream version based on current time.
            // This variable is used by DSResource class to apply version on the datastream.
//            Zend_Registry::set('version', date('Y-m-d H:i:s'));
            Zend_Registry::set('version', Date_API::getCurrentDateGMT());

            // Start looping the pid datastream and save to Fez CAS system.
            $migrationErrors = array();
            $migrationSuccessCount = 0;

             // Store the hash of the file using DSResource class.
             $fedoraFilePath = APP_FEDORA_GET_URL . "/" . $pid . "/" . $datastream['ID'];

            //does record inherity security from parent
            $acml = Record::getACML($pid, $datastream['ID']);
            $security_inherited = inheritesPermissions($acml);
            $datastreamData = Fedora_API::callGetDatastream($pid, $datastream['ID']);
            $label = $datastreamData['label'];

            $meta = array('mimetype' => $datastream['MIMEType'],
                'controlgroup' => 'M',
                'state' => 'A',
                'size' => $datastream['size'],
                'pid' => $pid,
                'security_inherited' =>  $security_inherited,
                'label' => $label);

            $dsresource = new DSResource(null, $fedoraFilePath, $meta);

            // A quick checking on the file's hash data before we proceed to save.
            $fileHash = $dsresource->getHash();
            if (empty($fileHash['rawHash'])){
                echo "ERROR: INVALID HASH data. " . $fedoraFilePath."\n";
                $migrationErrors[$datastream['ID']]['error'] = "INVALID HASH data. " . $fedoraFilePath;
                $migrationErrors[$datastream['ID']]['exif'] = $exif;
                continue;
            }

            // Save datastream
            $result = $dsresource->save(true);

            // Log save status
            if (!$result) {
                echo "ERROR: "." UNABLE TO SAVE $pid $dsid ".APP_DSTREE_PATH.$fileHash['hashPath'].$fileHash['rawHash']."\n";
                ob_flush();
                $migrationErrors[$datastream['ID']]['error'] = " UNABLE TO SAVE ";
                $migrationErrors[$datastream['ID']]['exif'] = $exif;
            } else {
                $migrationSuccessCount++;
            }

            //Setup security on Datastream
            $acmlBase = Record::getACML($pid, $datastream['ID']);
            if (!$acmlBase ) {
                $did = AuthNoFedoraDatastreams::getDid($pid, $datastream['ID']);
                AuthNoFedoraDatastreams::recalculatePermissions($did);
                echo $did ." ".$pid." ".$datastream['ID']." ".APP_DSTREE_PATH.$fileHash['hashPath'].$fileHash['rawHash']." (no DS ACML found) <br/>\n";
                ob_flush();
            } else
            {
                addDatastreamSecurity($acml, $pid, $datastream['ID']);
                $did = AuthNoFedoraDatastreams::getDid($pid, $dsID);
                echo $did ." ".$pid." ".$datastream['ID']." ".APP_DSTREE_PATH.$fileHash['hashPath'].$fileHash['rawHash']." (found DS ACML so adding) <br/>\n";
                ob_flush();
            }
        }
    }
}

// Print out migration results & any errors.
echo "<pre>Total managed-content successfully migrated = " . $migrationSuccessCount . "</pre>\n";
echo "<pre>Migration errors = " . print_r($migrationErrors, 1) . "</pre>\n";
ob_flush();
exit;

function inheritesPermissions ($acml) {

    if ($acml == false) {
        //if no acml then default is inherit
        $inherit = true;
    } else {
        $xpath = new DOMXPath($acml);
        $inheritSearch = $xpath->query('/FezACML[inherit_security="on"]');
        $inherit = false;
        if( $inheritSearch->length > 0 ) {
            $inherit = true;
        }
    }
    return $inherit;
}

function addDatastreamSecurity($acml, $pid, $dsID) {

    // loop through the ACML docs found for the current pid or in the ancestry
    $xpath = new DOMXPath($acml);
    $roleNodes = $xpath->query('/FezACML/rule/role');
    $did = AuthNoFedoraDatastreams::getDid($pid, $dsID);

    foreach ($roleNodes as $roleNode) {
        $role = $roleNode->getAttribute('name');
        // Use XPath to get the sub groups that have values
        $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode);

        /* todo
         * Empty rules override non-empty rules. Example:
         * If a pid belongs to 2 collections, 1 collection has lister restricted to fez users
         * and 1 collection has no restriction for lister, we want no restrictions for lister
         * for this pid.
         */

        foreach ($groupNodes as $groupNode) {
            $group_type = $groupNode->nodeName;
            $group_values = explode(',', $groupNode->nodeValue);
            foreach ($group_values as $group_value) {

                //off is the same as lack of, so should be the same
                if ($group_value != "off") {
                    $group_value = trim($group_value, ' ');

                    $arId = AuthRules::getOrCreateRule("!rule!role!".$group_type, $group_value);
                    AuthNoFedoraDatastreams::addSecurityPermissions($did, $role, $arId);
                }
            }
        }
    }
}