<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2016 The University of Queensland,                     |
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
// | Authors: Christiaan Kortekaas <ck@uq.edu.au>                         |
// +----------------------------------------------------------------------+
//

/*
 * This script deletes a PID from the solr index
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.datastream.php");
include_once(APP_INC_PATH . "class.bookreaderpdfconverter.php");

if (php_sapi_name()==="cli")  {
  // for every datastream, check whether there are bookreader pid images for it, if so set dsi_bookreader to 1
  $datastreams = Datastream::getAllDatastreams(".pdf");
  $total = count($datastreams);
  echo "Count of datastreams to check: ".$total."\n";
  $inc = 0;
  foreach ($datastreams as $ds) {
    $inc++;
    echo "Progress - ".$inc."/".$total;
    if ($ds['dsi_bookreader'] == 0) {
      $pid = $ds['dsi_pid'];
      if (strstr($pid, ':')) {
        $pid = str_replace(':', '_', $pid);
      }
      $sourceFileStat = pathinfo($ds['dsi_dsid']);

      if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
        $bookreaderDataPath = APP_TEMP_DIR . $pid . '/' . $sourceFileStat['filename'];
        $s3Prefix = '';
        if (defined('AWS_S3_SRC_PREFIX') && !empty(AWS_S3_SRC_PREFIX)) {
          $s3Prefix = AWS_S3_SRC_PREFIX . '/';
        }
        $bookreaderDataPath = $s3Prefix . str_replace('../', '', BR_IMG_DIR) . $pid . '/' . $sourceFileStat['filename'];
        $bri = new bookReaderPDFConverter();
      } else {
        $bookreaderDataPath = APP_PATH . BR_IMG_DIR . $pid . '/' . $sourceFileStat['filename'];
      }
      echo $bookreaderDataPath."\n";
      if ($bri->resourceGenerated($bookreaderDataPath)) {
//        echo "setting for $pid".$ds['dsi_dsid'];
        Datastream::setBookreader($ds['dsi_pid'], $ds['dsi_dsid'], 1);
      } else {
//        echo "none found for $pid".$ds['dsi_dsid'];
      }
    }
  }
}