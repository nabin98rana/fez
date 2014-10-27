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
// | Authors: Aaron Brown <a.brown@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+
//
// This function finds datastreams missing from Fedora
//Server will require unrestricted access
/*


*/

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.auth.php");
error_reporting(E_ALL & !E_NOTICE);

$isUser = Auth::getUsername();
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
    set_time_limit(0);
    echo "Script started: " . date('Y-m-d H:i:s') . "\n";
    echo "--------------------------\n";
    ob_flush();
    flush();

    $db = DB_API::get();
    $log = FezLog::get();
    $isUser = Auth::getUsername();

    $fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('','');
    //$fedoraPids[] = array('pid' => 'UQ:3875');
    foreach ($fedoraPids as $pid) {
        $pid = $pid['pid'];
        $datastreams = Fedora_API::callGetDatastreams($pid);
        foreach ($datastreams as $datastream) {
            if ($datastream['controlGroup'] == "M") {
                $url = APP_FEDORA_LOCATION."/objects/";
                $file = 'http://'.$url.$pid.'/datastreams/'.$datastream['ID'].'/content';
                $file_headers = @get_headers($file);
                if (!strpos($file_headers[0], '200')) {
                    echo "File not found: ".$pid.", ".$datastream['ID']. "\n";
                    ob_flush();
                    flush();
                }
            }

            if ($open) {
                $open = false;
                echo "--------------------------\n";
            }
        }
    }
    echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
    echo "--------------------------\n";

} else {
    echo "Must be run from the command line or as superadmin";
}
