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
	public function getUploadedFilePath($wflId)
	{
		return APP_TEMP_DIR."uploader/{$wflId}";
	}
	
	/**
	 * Generates the files array based on the files in the uploader directory for this workflow
	 *
	 * @param string $wflId Workflow id to base files on
	 * @return array
	 * 
	 **/
	public function generateFilesArray($wflId, $xsdmfId)
	{
	    $uploadDir = self::getUploadedFilePath($wflId);
		$returnArray = array();
		
		// if the directory doesn't exist, return an empty array
		if (!file_exists($uploadDir)) {
			return $returnArray;
		}

		// for every file in the directory
		$scandirList = scandir($uploadDir);
		$counter = 0;
		
		foreach ($scandirList as $file) {
			if (is_file("{$uploadDir}/{$file}")) {
	
				// strip characters up to first period (this is the number prepended to the filename so that we get the files in order)
				$start = strpos($file, '.');
				$newFilename = substr($file, $start+1, strlen($file));
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
				$counter++;
			}
		}
		return $returnArray;
	}
} // END class Uploader