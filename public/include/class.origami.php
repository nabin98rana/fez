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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+


class Origami {

	function getTitleLocation($pid, $dsID)
	{

		$pidData    = explode(':', $pid);

		/*
		 * Create folders based on the PID
		 * demo:1940549 becomes folder structure /194/054/9/
		 */
		$folders    = str_split($pidData[1], 3);

		/*
		 * Create the folder location
		 */
		return implode("/", $folders) . "/".str_replace(':','_', $pid). "/".md5($dsID);
	}


	function createTitles($pid, $filename, $mimetype)
	{
		$log = FezLog::get();
		/*
		 * Origami can only process jpg or tif images
		 */
		if(!($mimetype == 'image/jpeg' ||
		$mimetype == 'image/jpg' ||
		$mimetype == 'image/tif' ||
		$mimetype == 'image/tiff')) {

			//Logger::debug($pid . " " . $filename ." didnt have correct mimetype - ". $mimetype);
			return;
		}

		/*
		 * Create the folder location
		 */
		$path = Origami::getTitleHome() . Origami::getTitleLocation($pid, $filename);

		if(!is_dir($path)) {

			$ret = mkdir($path, 0775, true);

			if(!$ret) {
				$log->err(array("Process Origami Images Failed - Could not create folder " . $path, __FILE__ , __LINE__ ));
				return;
			}
		}

		$fileUrl = APP_FEDORA_GET_URL . "/" . $pid . "/". $filename;
		$tmpFile = APP_TEMP_DIR.Foxml::makeNCName($filename);

		$return_status = 0;
		$return_array = array();

		if (!is_file($tmpFile)) {
			$fileHandle = fopen($tmpFile, 'w+');
			$ret = fwrite($fileHandle, file_get_contents($fileUrl));
			fclose($fileHandle);

			if(!$ret) {
				$log->err(array("Process Origami Images Failed - Could not write to tmp file " . $tmpFile, __FILE__ , __LINE__ ));
				return;
			}
			exec(Origami::getTitleAppPath() . " $tmpFile $path", $return_array, $return_status);
			if ($return_status <> 0) {
				$log->err(array("Origami Error: ".implode(",", $return_array).", return status = $return_status, for command $command \n", __FILE__,__LINE__));
			}
      if (is_file($tmpFile)) {
        unlink($tmpFile);
      }
		} else {
			exec(Origami::getTitleAppPath() . " $tmpFile $path", $return_array, $return_status);
			if ($return_status <> 0) {
				$log->err(array("Origami Error: ".implode(",", $return_array).", return status = $return_status, for command $command \n", __FILE__,__LINE__));
			}
		}
		//echo Origami::getTitleAppPath() . " $tmpFile $path\n";
	}

	function getTitleAppPath()
	{

		// We must cd in the origami directory because the tile_image script uses a relative path for
		// processing images. Basically its crashes without doing this..
		return APP_PY_EXEC . " ". APP_ORIGAMI_PATH . "/tile_image.py";
	}

	function getTitleHome() {
		return  APP_PATH . "flviewer/";
	}

}
