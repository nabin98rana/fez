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
// | Authors: Bernadette Houghton <bhoughton@deakin.edu.au>               |
// +----------------------------------------------------------------------+
//
//
// set fezacml on object to a hardcoded id in the table fez_auth_quick_template


include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.fedora_api.php");

$this->getRecordObject();

$env = strtolower($_SERVER['APPLICATION_ENV']);
if ($env == 'staging' || $env == 'production' || empty($env)) {
	$datastream_policy = 'Fully Embargoed (system admins only)';
} else {
	$datastream_policy = 'Thesis officers only'; // for dev/testing
}
$id = FezACML::getQuickTemplateIdByTitle($datastream_policy);
if (empty($id)) {
	$log = FezLog::get();
	$log->err('Datastream policy template not found');
	return;
}

$xmlObj = FezACML::getQuickTemplateValue($id);
if ($xmlObj != false) {
  // check existing datastreams for any attachments
  $ds = Fedora_API::callGetDatastreams($this->pid);
  foreach ($ds as $dstream) {
    // file attachment datastreams will have controlGroup=M and will not begin with 'presmd_'
    if (($dstream['controlGroup'] == 'M') && (substr($dstream['ID'], 0, 7) != 'presmd_')) {
      $FezACML_dsID = FezACML::getFezACMLDSName($dstream['ID']);
      if (Fedora_API::datastreamExists($this->pid, $FezACML_dsID)) {   //modify an existing datastream
        Fedora_API::callModifyDatastreamByValue($this->pid, $FezACML_dsID, "A", "FezACML",
          $xmlObj, "text/xml", "inherit");

      } else {     //add a new datastream
        Fedora_API::getUploadLocation($this->pid, $FezACML_dsID, $xmlObj, "FezACML",
          "text/xml", "X",null,"true");
      }
    }
  }
}

