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
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.dsresource.php");


// Get PIDs' Exif data from database table.
$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "exif ORDER BY exif_pid";
try {
    $pid_exifs = $db->fetchAll($stmt);
} catch (Exception $ex) {
    $log->err($ex);
    echo "Failed to retrieve exif data. Error: " . $ex;
}


// Set datastream version based on current time. 
// This variable is used by DSResource class to apply version on the datastream.
Zend_Registry::set('version', date('Y-m-d H:i:s'));

// Start looping the pid datastream and save to Fez CAS system.
$migrationErrors = array();
$migrationSuccessCount = 0;
foreach ($pid_exifs as $exif) {

    // Ignore records without PID and DSID
    if (empty($exif['exif_pid']) || empty($exif['exif_dsid'])) {
        $migrationErrors[$exif['exif_id']]['error'] = 'EMPTY PID or DSID ';
        $migrationErrors[$exif['exif_id']]['exif'] = $exif;
        continue;
    }


    // Store the hash of the file using DSResource class.
    $fedoraFilePath = APP_FEDORA_GET_URL . "/" . $exif['exif_pid'] . "/" . $exif['exif_dsid'];
    $meta = array('mimetype' => $exif['exif_mime_type'],
        'controlgroup' => 'M',
        'state' => 'A',
        'size' => $exif['exif_file_size'],
        'pid' => $exif['exif_pid']);
    $dsresource = new DSResource(null, $fedoraFilePath, $meta);
    
    // A quick checking on the file's hash data before we proceed to save.
    $fileHash = $dsresource->getHash();
    if (empty($fileHash['rawHash'])){
        $migrationErrors[$exif['exif_id']]['error'] = "INVALID HASH data. " . $fedoraFilePath;
        $migrationErrors[$exif['exif_id']]['exif'] = $exif;
        continue;
    }
    
    // Save datastream
    $result = $dsresource->save();

    // Log save status
    if (!$result) {
        $migrationErrors[$exif['exif_id']]['error'] = " UNABLE TO SAVE ";
        $migrationErrors[$exif['exif_id']]['exif'] = $exif;
    } else {
        $migrationSuccessCount++;
    }
}

// Print out migration results & any errors.
echo "<pre>Total managed-content successfully migrated = " . $migrationSuccessCount . "</pre>";
echo "<pre>Migration errors = " . print_r($migrationErrors, 1) . "</pre>";
exit;

