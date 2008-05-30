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
 
if( APP_ORIGAMI_SWITCH == "OFF" ) {
    return;
}


//Array
//(
//    [ID] => coffee.jpg
//    [MIMETYPE] => image/jpeg
//)


$tileApp    = APP_ORIGAMI_PATH . "/tile_image.py";
$tileHome   = APP_PATH . "flviewer/";

$pid        = $this->pid;
$filename   = $this->dsInfo['ID'];

$fileData   = explode('.', $filename);
$fileExt    = array_pop($fileData);

/*
 * Origami can only process jpg or tif images
 */
if(!($fileExt == 'jpg' || $fileExt == 'tif')) {
    return;
}

$pidData    = explode(':', $pid);
$folder     = $pidData[1] % 1000;

/*
 * Create the folder location
 *
 * Mod 1000 on the pid helps breakdown the folders, so 
 * we dont have all folders in the 1 directory
 */
$path = $tileHome.$folder."/".str_replace(':','_', $pid)."/".md5($filename);

if(!is_dir($path)) {
    
    $ret = mkdir($path, 0775, true);
    
    if(!$ret) {
        Error_Handler::logError("Process Origami Images Failed - Could not create folder " . $path, __FILE__ , __LINE__ );
        return;
    }
}

$fileUrl = APP_FEDORA_GET_URL . "/" . $pid . "/". $filename;
$tmpFile = "/tmp/" . Foxml::makeNCName($filename);

$fileHandle = fopen($tmpFile, 'w+');
fputs($fileHandle, file_get_contents($fileUrl));
fclose($fileHandle);

//if(Fedora_API::datastreamExists($namespace , str_ireplace("_jpg ", ".tif", $ds)) ) {
//    
//    // we have a TIF master
//    $pid = str_ireplace("_jpg", ".tif", $pid);
//    
//} elseif(Fedora_API::datastreamExists($namespace , str_ireplace("_jpg ", ".jpg", $ds)) ) {
//    
//    // we have the JPG master
//    $pid = str_ireplace("_jpg ", ".jpg", $pid);
//    
//} else {
//    
//    // we don 't have a recognizable master ! //
//    $pid = false;
//    Error_Handler::logError("Problem : neither a TIF or JPG datastream exists for $namespace /$ds generate tiles from , tiling didn 't complete .", __FILE__ , __LINE__ );
//    
//}

// run the origami tiler
//if ($pid) {
    exec(APP_PY_EXEC . " $tileApp $tmpFile $path" , $output);
//}

unlink($tmpFile);




?>