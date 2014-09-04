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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . 'class.background_process_list.php');
include_once(APP_INC_PATH . 'class.auth.php');

if (!APP_API) {
    $pid = $_REQUEST['pid'];
    $href = $_REQUEST['href'];
    $wfl_title = $_REQUEST['wfl_title'];
    $wft_type = $_REQUEST['wft_type'];
} else {
    $pid = $args['pid'];
    $href = $args['href'];
    $wfl_title = $args['wfl_title'];
    $wft_type = $args['wft_type'];
}

$isAdministrator = Auth::isAdministrator();

if (!APP_API) {
    if ($isAdministrator && $wft_type == "Create") {
      if (Misc::isValidPid($pid)) {
          $record = new RecordObject($pid);
          if ($record->isCommunity()) {
              $redirect = APP_RELATIVE_URL . "community/" . $pid;
          } elseif ($record->isCollection()) {
              $redirect = APP_RELATIVE_URL . "collection/" . $pid;
          } else {
              $redirect = APP_RELATIVE_URL . "view/" . $pid;
          }
      }
    } elseif ($href) {
        $redirect = APP_RELATIVE_URL . substr($_REQUEST['href'], strlen(APP_RELATIVE_URL));
    } else {
        if ($wft_type != 'Delete') {
            if (Misc::isValidPid($pid)) {
                $record = new RecordObject($pid);
                if ($record->isCommunity()) {
                    $redirect = APP_RELATIVE_URL . "community/" . $pid;
                } elseif ($record->isCollection()) {
                    $redirect = APP_RELATIVE_URL . "collection/" . $pid;
                } else {
                    $redirect = APP_RELATIVE_URL . "view/" . $pid;
                }
            } else {
    			$redirect = APP_RELATIVE_URL . "list.php";
    		}
        } else {
            // Take a stab at a parent URL.
            $parents_list = unserialize(stripslashes($_REQUEST['parents_list']));
            foreach ($parents_list as &$item) {
                if (Misc::isValidPid($item)) {
                    $precord = new RecordObject($item);
                    if ($precord->isCommunity()) {
                        $redirect = APP_RELATIVE_URL . "community/" . $item;
                    } else {
                        $redirect = APP_RELATIVE_URL . "collection/" . $item;
                    }
                    break;
                }
            }
        }
    }
}

$message = "Finished " . $_REQUEST['wfl_title'];
Session::setMessage($message, 'ok');
$bgp_list = new BackgroundProcessList;
$bgp_list->autoDeleteOld(Auth::getUserID());

if (APP_API) {
    $outcome = null;
    // Extract some information from the workflow in the event of
    // something failing...
    if (isset($this->vars['outcome'])) {
        $outcome = isset($this->vars['outcome']);
        if (isset($this->vars['outcome_details'])) {
            $message = $this->vars['outcome_details'];
        }
    }
    switch ($outcome) {
        case 'notCreated':
            $httpcode = 500;
            break;
        default:
            $httpcode = 202; // 202 = accepted
            break;
    }
    $arr = API::makeResponse($httpcode, $message, array('pid' => $pid));
    API::reply($httpcode, $arr, APP_API);
    exit;
} else {
    header('Location: ' . $redirect);
}

