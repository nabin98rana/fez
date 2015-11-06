<?php
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

include_once('../config.inc.php');
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.api_researchers.php");

$callback = $_GET['callback'];
$callback = !empty($callback) ? preg_replace('/[^a-z0-9\.$_]/si', '', $callback) : false;
header('Access-Control-Allow-Origin: *');
header('Content-Type: ' . ($callback ? 'application/javascript' : 'application/json') . ';charset=UTF-8');

$author_username  = trim($_GET['author_username']);
$id  = trim($_GET['id']);
$id_type  = trim($_GET['id_type']);
$list = trim($_GET['list']);

//ORCID grant info
$value = trim($_GET['value']);
$name = trim($_GET['name']);
$expires = trim($_GET['expires']);
$status = trim($_GET['status']);
$grant = trim($_GET['grant']);

$securityToken = $_SERVER['HTTP_X_API_TOKEN'];

echo($callback ? '/**/' . $callback . '(' : '');

if ($securityToken != APP_API_IDS_TOKEN) {
    http_response_code(401);
    echo json_encode("Not authorized");
    echo $callback ? ');' : '';
    exit();
} else if (!ctype_alnum($author_username) || substr(strtolower($author_username), 0, 1) === "s" || empty($author_username)) {   //is alphanumeric and not a student, has permissions and required data
    http_response_code(404);
    echo json_encode(array(), JSON_FORCE_OBJECT);
    echo $callback ? ');' : '';
    exit();
}

if (!empty($list)) {
    $result = ApiResearchers::listId($author_username);
    if (!empty($id_type)){
        foreach($result as $details) {
            if ($details['id'] == $id_type) {
                echo json_encode($details);
                exit();
            }
        }
        echo json_encode(array(), JSON_FORCE_OBJECT);
    } else {
    echo json_encode($result);
    }
} else if (!empty($grant)) {
    $result = ApiResearchers::saveGrantInfo($author_username, $id_type, $name, $status, $expires, $value);
    $result = ApiResearchers::changeId($author_username, $id, $id_type);
    echo json_encode(array("status" => "ok"));
} else {
    $result = ApiResearchers::changeId($author_username, $id, $id_type);
    echo json_encode(array("status" => "ok"));
}

echo $callback ? ');' : '';
