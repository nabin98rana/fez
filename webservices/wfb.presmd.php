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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
// @@@ CK - 28/7/05
// Preservation Metadata automatic extraction webservice
// - Takes url parameters to convert an image file in the Fez temp directory into a image file of the given format and size

include_once("../config.inc.php");
$file = escapeshellcmd(urldecode($_GET['file']));
$file_dir = "";	

if (is_numeric(strpos($file, "/"))) {
	$file_dir = substr($file, 0, strrpos($file, "/"));
	$file = substr($file, strrpos($file, "/")+1);

}
if (trim($file_dir) == "") { $file_dir = APP_JHOVE_TEMP_DIR; }
if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
	$presmd_file = APP_JHOVE_TEMP_DIR.'presmd_'.str_replace(' ', '_', substr($file, 0, strrpos($file, '.'))).'.xml';
	if (is_file($presmd_file)) {
		$deleteCommand = APP_DELETE_CMD." ".$presmd_file;
		exec($deleteCommand);
	}
	$APP_JHOVE_CMD = APP_JHOVE_DIR.'/jhove -h xml -o '.$presmd_file;
	if (is_numeric(strpos($file, " "))) {
		$APP_JHOVE_CMD .= ' \"'.$file_dir.'/'.$file.'\"';
	} else {
		$APP_JHOVE_CMD .= ' '.$file_dir.'/'.$file;	
	}
	if(!$file) $error .= "<b>ERROR:</b> no file specified<br>";
	if(!is_file($file_dir.$file)) { $error .= "<b>ERROR:</b> given file filename not found or bad filename given<br>"; }
	$command = $APP_JHOVE_CMD;
	exec($command);
} 











?>