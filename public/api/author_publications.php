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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//  authi_arg_id = '11' is assumed to give all lister permissions for public access. It is not necessarily aways the the case it's this simple.
//


include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.flint.php");

$callback = $_REQUEST['callback'];
$callback = !empty($callback) ? preg_replace('/[^a-z0-9\.$_]/si', '', $callback) : false;
header('Access-Control-Allow-Origin: *');
header('Content-Type: ' . ($callback ? 'application/javascript' : 'application/json') . ';charset=UTF-8');

$author_id = $_REQUEST['author_id'];
$year = $_REQUEST['year'];

if((!is_numeric($year) && !empty($year)) || !is_numeric($author_id)) {   //is alphanumeric and not a student
    echo json_encode(array(), JSON_FORCE_OBJECT);
    exit();
}

if(!empty($_REQUEST['datacollections'])) {
    $return['data_collections'] = ApiResearchers::getDataCollections($author_id, $year);
    $return['pids_with_data_collections'] = ApiResearchers::getPidsWithDatacollections($author_id, $year);
    echo json_encode($return);
} else {
    echo json_encode(array(), JSON_FORCE_OBJECT);
}