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

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.search_key.php");

if( $argc != 2 || !is_numeric($argv[1]) )
{
    usage();
    exit();
}

$limit = $argv[1];

$list = array();
$options = array();

$options["sort_order"] = "1";
$sort_by = "searchKey".Search_Key::getID("Created Date");
$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only

$list = Record::getListing($options, array("Lister"), 0, $limit, $sort_by, false, true);

foreach ( $list['list'] as $pidData )
{
    $pids[] = $pidData['rek_pid'];
}

if( count($pids) > 0 )
{
    Record::deleteRecentRecords();
    Record::insertRecentRecords($pids);
}


function usage()
{
    echo "\n\tUsage: path-to-php cache_recent_items.php [limit]\n";
    echo "\tWhere [limit] is the number of records to cache\n\n";
}

?>