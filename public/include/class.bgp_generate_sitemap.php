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
include_once(APP_INC_PATH . 'class.sitemap.php');

class BackgroundProcess_Generate_Sitemap extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_generate_sitemap.php';
    $this->name = 'Generate sitemap';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->generateSitemap();

    $this->setState(BGP_FINISHED);
  }

  function generateSitemap() {
    $db = DB_API::get();
    $log = FezLog::get();

    $approved_roles=array(9,10);
    // Get listing of all published pids, which CLI will be publicly viewable pids
    $stmt = "SELECT rek_pid, rek_updated_date FROM " . APP_TABLE_PREFIX . "record_search_key";
    $authArray = Collection::getAuthIndexStmt($approved_roles, "rek_pid", false);
    $stmt .= $authArray['authStmt'];
    $stmt .= " AND rek_status = '2' ORDER BY rek_updated_date DESC";
    $pidList = $db->fetchAll($stmt);

    $sitemap = new Sitemap(false);
    $sitemap->page('records');
    $this->setHeartbeat();
    foreach ($pidList as $pidDetails) {
      $pid = $pidDetails['rek_pid'];
      $updated = $pidDetails['rek_updated_date'];

      //We'll tell google to check on pids updated lately(Past 4 weeks) sooner rather than later
      $changeFrequency = ($updated > strftime("%Y-%m-%d", time() - 60*60*24*7*4)) ? 'daily' : 'monthly';
      $url = 'view/'.$pid;
      $sitemap->url($url, $updated, $changeFrequency);
    }

    //We'll also index user urls
    $stmt = "(SELECT aut_mypub_url AS url, MAX(rek_updated_date) AS recent_date FROM " . APP_TABLE_PREFIX . "author
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id = aut_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
            WHERE aut_mypub_url  IS NOT NULL AND aut_mypub_url != ''
            GROUP BY url)
            UNION
            (SELECT aut_org_username AS url, MAX(rek_updated_date) AS recent_date  FROM " . APP_TABLE_PREFIX . "author
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id = aut_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
            WHERE aut_org_username IS NOT NULL AND (aut_mypub_url IS  NULL OR aut_mypub_url = '')
            GROUP BY url)
            UNION
            (SELECT aut_student_username AS url, MAX(rek_updated_date) AS recent_date  FROM " . APP_TABLE_PREFIX . "author
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id = aut_id
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_author_id_pid
            WHERE aut_student_username IS NOT NULL AND (aut_mypub_url IS  NULL OR aut_mypub_url = '')
            GROUP BY url)
            ;";
    $userUrlList = $db->fetchAll($stmt);
    $this->setHeartbeat();
    foreach ($userUrlList as $userUrl) {
      $changeFrequency = 'weekly';
      $updated = $userUrl['recent_date'];
      $url = $userUrl['url'];
      $sitemap->url($url, $updated, $changeFrequency);
    }

    $sitemap->close();
  }
}
