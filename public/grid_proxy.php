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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once('config.inc.php');
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.lister.php");

//Auth::checkAuthentication(APP_SESSION);
//$user = Auth::getUsername();
//$isAdministrator = User::isUserAdministrator($user);
//$isSuperAdministrator = User::isUserSuperAdministrator($user);
//echo '<pre>';

//$search =
$params = $_GET;

        $args = array(
            '_search'  =>  'string',
            'sidx'     =>  'string',
            'sord'     =>  'string',
            'page'     =>   'numeric',
            'rows'     =>   'numeric'
        );

        foreach ($args as $getName => $getType) {
            if( Misc::sanity_check($params[$getName], $getType) !== false ) {
                $allowed[$getName] = $params[$getName];
            } else {
              $allowed[$getName] = '';
            }
        }
        $params = $allowed;


//echo '<pre>';
//print_r($params);
//print_r(GridProxy::get($params['sidx'], $params['sord']));
header('Content-Type: application/json');
$grid = GridProxy::get($params['sidx'], $params['sord']);
echo json_encode($grid);
//echo '</pre>';
//exit;
//print_r($_SERVER['REQUEST_METHOD']); exit;


class GridProxy
{

	public function get($sidx = '', $sord='asc', $rows, $page)
	{
		$log = FezLog::get();
		$db = DB_API::get();

    if ($sord == 'asc') {
      $sord = '0';
    } else {
      $sord = '1';
    }

    $params = array(
      'tpl' => 10,
      'sort_order' => $sord,
      'sort_by' => $sidx,
      'cat' => 'quick_filter',
      'rows' => $rows,
      'pager_row' => $page
    );

    $list =  Lister::getList($params, false);
//    echo '<pre>';
//    print_r($params);
////    print_r($list);
//    echo '</pre>';
//    exit;


    //return $list['list'];
//    $grid = array(
//      'rows' => array(
//        'page' => $list['list_info']['current_page'],
//        'total' => $list['list_info']['total_pages'],
//        'records' => $list['list_info']['total_rows']
//      )
//
//    );
//    $rows = array();
//    $grid['rows']['row'] = array();
//    foreach ($list['list'] as $row) {
//      $grid['rows']['row'][] = array(
//        'id' => $row['rek_pid'],
//        'cell' => array (
//          $row['rek_pid'],
//          $row['rek_title']
//        )
//      );
//    }

    $grid = array(

        'page' => $list['list_info']['current_page'],
        'total' => $list['list_info']['total_pages'],
        'records' => $list['list_info']['total_rows']


    );
    $rows = array();
    $grid['rows'] = array();
    foreach ($list['list'] as $row) {
      $grid['rows'][] = array(
        'id' => $row['rek_pid'],
        'cell' => array (
          $row['rek_pid'],
          $row['rek_title'],
          $row['rek_date'],
        )
      );
    }


    return $grid;
  }
}
