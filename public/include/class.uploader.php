<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2009 The University of Queensland,   |
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
// | Authors: Marko Tsoi <m.tsoi@library.uq.edu.au>                       |
// +----------------------------------------------------------------------+

/**
 * Class to help dealing with the flash uploader
 *
 **/
class Uploader
{

    /**
     * Generates the file path given a $wflId
     *
     * @return string
     **/
    public static function getUploadedFilePath($wflId, $forceLocal = false)
    {
        $subDir = "uploader/{$wflId}";

        if (APP_FEDORA_BYPASS == 'ON' && !$forceLocal) {
            $aws = new AWS(AWS_S3_CACHE_BUCKET);
            return $aws->createPath($subDir, '');
        }
        return APP_TEMP_DIR . $subDir;
    }

    /**
     * Generates the files array based on the files in the uploader directory for this workflow
     *
     * @param string $wflId Workflow id to base files on
     * @return array
     *
     **/
    public static function generateFilesArray($wflId, $xsdmfId)
    {
        $uploadDir = self::getUploadedFilePath($wflId);
        $returnArray = array();

        if (APP_FEDORA_BYPASS == 'ON') {
            $uploadDirLocal = APP_TEMP_DIR . $wflId;
            // Create the directory (and any missing directories) for this workflow
            if (!is_dir($uploadDirLocal)) {
                $directory_path = "";
                $directories = explode("/", $uploadDirLocal);
                foreach ($directories as $directory) {
                    $directory_path .= $directory . "/";
                    if (!is_dir($directory_path)) {
                        @mkdir($directory_path);
                        chmod($directory_path, 0777);
                    }
                }
            }

            $aws = new AWS(AWS_S3_CACHE_BUCKET);
            $objects = $aws->listObjects($uploadDir);
            if (!$objects) {
                return $returnArray;
            }
            foreach ($objects as $obj) {
                if ($obj['Size'] > 0) {
                    $params = ['SaveAs' => $uploadDirLocal . '/' . basename($obj['Key'])];
                    $aws->getFileContent($obj['Key'], '', $params);
                }
            }
            //$aws->deleteMatchingObjects($uploadDir);
            $uploadDir = $uploadDirLocal;
        } // if the directory doesn't exist, return an empty array
        else if (!file_exists($uploadDir)) {
            return $returnArray;
        }

        // for every file in the directory
        $scandirList = scandir($uploadDir);
        $counter = 0;

        $returnArray['files'] = array();
        foreach ($scandirList as $file) {
            if (is_file("{$uploadDir}/{$file}")) {
                $pathstuff = pathinfo("{$uploadDir}/{$file}");
                $newFilename = basename($pathstuff['basename'], "." . $pathstuff['extension']);
                $start = strpos($newFilename, '.');
                if ($start) {
                    $newFilename = substr($newFilename, $start + 1, strlen($newFilename));
                }
                $newFilename .= "." . $pathstuff['extension'];
                rename("{$uploadDir}/{$file}", "{$uploadDir}/{$newFilename}"); // move into new filename (so we don't get confused later)

                // determine the file size and mime type
                $fileSize = sprintf("%u", filesize("{$uploadDir}/{$newFilename}"));
                $mimeType = Misc::mime_content_type("{$uploadDir}/{$newFilename}");

                // now set up the return array (to look like the $_FILES array)
                $returnArray['xsd_display_fields']['name'][$xsdmfId][$counter] = $newFilename;
                $returnArray['xsd_display_fields']['type'][$xsdmfId][$counter] = $mimeType;
                $returnArray['xsd_display_fields']['tmp_name'][$xsdmfId][$counter] = 'ALREADYMOVED';
                $returnArray['xsd_display_fields']['new_file_location'][$xsdmfId][$counter] = "{$uploadDir}/{$newFilename}";
                $returnArray['xsd_display_fields']['error'][$xsdmfId][$counter] = 0;
                $returnArray['xsd_display_fields']['size'][$xsdmfId][$counter] = $fileSize;
                $returnArray['_files'][] = "{$uploadDir}/{$newFilename}";
                $counter++;
            }
        }
        return $returnArray;
    }
} // END class Uploader