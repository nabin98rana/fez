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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.wok_queue.php");
include_once(APP_INC_PATH . 'class.links_amr_queue.php');

$filter = array();

// Get records ..
$filter["searchKey".Search_Key::getID("Object Type")] = 3;
// .. which are either journal articles and conference papers ..
$filter["searchKey".Search_Key::getID("Display Type")] = array();
$filter["searchKey".Search_Key::getID("Display Type")]['override_op'] = 'OR';
$filter["searchKey".Search_Key::getID("Display Type")][] = 
    XSD_Display::getXDIS_IDByTitleVersion('Journal Article', 'MODS 1.0');
$filter["searchKey".Search_Key::getID("Display Type")][] = 
    XSD_Display::getXDIS_IDByTitleVersion('Conference Paper', 'MODS 1.0');
// .. which were created in the last month ..
$filter["searchKey".Search_Key::getID("Created Date")] = array();
$filter["searchKey".Search_Key::getID("Created Date")]["filter_type"] = "greater";
$filter["searchKey".Search_Key::getID("Created Date")]["filter_enabled"] = 1;
$filter["searchKey".Search_Key::getID("Created Date")]["start_date"] = 
    Date_API::getFedoraFormattedDateUTC(strtotime("-36 months"));
// .. without a UT ..
$filter["manualFilter"] = " -isi_loc_t_s:[* TO *] AND ";
// ..and optionally enforce published records only
//$filter["searchKey".Search_Key::getID("Status")] = 2;
$laq = LinksAmrQueue::get();
$max = 10;
$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

//Clean out any remaining Researcher ID - UT to Author id (aut_id) relationships before proceeding or it will treat these like RID downloads
$queue = WokQueue::get();
$queue->deleteAllAutIds();


for ($i=0; $i<((int)$listing['info']['total_pages']); $i++) {
  // Skip first loop - we have called getListing once already
  if ($i>0) {
    $listing = Record::getListing(
        array(), array(9,10), ($i), $max, 'Created Date', false, false, $filter
    );
  }
  
  if (is_array($listing['list'])) {
    for ($j=0; $j<count($listing['list']); $j++) {      
//      LinksAmrQueue::get()->add($listing['list'][$j]['rek_pid']);
      $laq->add($listing['list'][$j]['rek_pid']);
    }
  }
}

$log = FezLog::get();
$log->close();
