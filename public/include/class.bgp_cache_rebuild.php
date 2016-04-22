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

include_once(APP_INC_PATH . 'class.cloud_tag.php');
include_once(APP_INC_PATH . 'class.record.php');
include_once(APP_INC_PATH . 'class.search_key.php');
include_once(APP_INC_PATH . 'class.statistics.php');
include_once(APP_INC_PATH . 'class.background_process.php');

class BackgroundProcess_Cache_Rebuild extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_cache_rebuild.php';
    $this->name = 'Runs cache rebuild';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    // Cache cloud tags
    if (in_array('CloudTags', $runRebuild)) {
      $tags = Cloud_Tag::getTags();
      if (count($tags) > 0) {
        Cloud_Tag::deleteSavedTags();
        Cloud_Tag::saveTags($tags);
      }
    }

    // Cache recent items
    if (in_array('RecentItems', $runRebuild)) {
      $options = array();
      $options["sort_order"] = "1";
      $sort_by = "searchKey" . Search_Key::getID("Created Date");
      $options["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
      $list = Record::getListing($options, array("Lister"), 0, $recentItemsLimit, $sort_by, FALSE, TRUE);

      foreach ($list['list'] as $pidData) {
        $pids[] = $pidData['rek_pid'];
      }
      if (count($pids) > 0) {
        Record::deleteRecentRecords();
        Record::insertRecentRecords($pids);
      }
    }

    // Cache recent downloads
    if (in_array('RecentDownloads', $runRebuild)) {
      $list = Statistics::getRecentPopularItems($recentDownloadsLimit);

      if (!empty($list)) {
        Record::deleteRecentDLRecords();
        Record::insertRecentDLRecords($list);
      }
    }

    $this->setState(BGP_FINISHED);
  }
}
