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
// | Authors: Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.background_process.php');
include_once(APP_INC_PATH . "class.filecache.php");

class BackgroundProcess_Embargo_Period_Complete_Check extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_embargo_period_complete_check.php';
    $this->name = 'Embargo Period Complete Check';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $log = FezLog::get();
    $db = DB_API::get();

    $res = [];
    $stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "datastream_info WHERE dsi_embargo_date < NOW() AND dsi_embargo_processed != 1";
    try {
      $res = $db->fetchAll($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      echo "Failed to retrieve embargo data. Error: " . $ex;
    }

    foreach ($res as $pidInfo) {
      $pidPermissions = Auth::getAuthPublic($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
      if (!($pidPermissions['lister'] && $pidPermissions['viewer'])) {

        if (APP_FEDORA_BYPASS == "ON") {
          $did = AuthNoFedoraDatastreams::getDid($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
          AuthNoFedoraDatastreams::setInherited($did);
          AuthNoFedoraDatastreams::recalculatePermissions($did);
        } else {
          Datastream::setfezACMLInherit($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
        }

        echo $pidInfo['dsi_pid']." ".$pidInfo['dsi_dsid']." \n";
        if (APP_FILECACHE == "ON") {
          $cache = new fileCache($pidInfo['dsi_pid'], 'pid='.$pidInfo['dsi_pid']);
          $cache->poisonCache();
        }
      }
      Datastream::setEmbargoProcessed($pidInfo['dsi_pid'], $pidInfo['dsi_dsid']);
      History::addHistory($pidInfo['dsi_pid'], null, '', '', true, 'Embargo was found to be complete for '.$pidInfo['dsi_dsid'].' and was made public via cron job');
    }

    $this->setState(BGP_FINISHED);
  }
}
