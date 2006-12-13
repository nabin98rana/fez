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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH.'class.sanity_checks.php');


function parse_mysql_dump($url, $ignoreerrors = false) {
    $file_content = file($url);
    //print_r($file_content);
    $query = "";
    foreach($file_content as $sql_line) {
        $sql_line = replace_table_prefix($sql_line);
        $tsl = trim($sql_line);
        if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
            $query .= $sql_line;
            if(preg_match("/;\s*$/", $sql_line)) {
                $res = $GLOBALS["db_api"]->dbh->query($query);
                if (PEAR::isError($res)) {
                    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    return false;
                }
                $query = "";
            }
        }
    }
    return true;
}





function replace_table_prefix($str)
{
    return str_replace('%TABLE_PREFIX%', APP_TABLE_PREFIX, $str);
}

function upgrade()
{
    clearstatcache();
    // check if config.inc.php in the root directory is writable
    /**
     *  if (!is_writable('../config.inc.php')) {
     *      return "The file 'config.inc.php' in Fez's root directory needs to be writable by the web server user. Please correct this problem and try again.";
     *  }
     */

	if (parse_mysql_dump("upgrade.sql")) {
        return 'success';
    } else {
    	return 'The upgrade failed - check error_handler.log';
    }
}

$step = Misc::GETorPOST('step');
if (empty($step)) {
	$step = 1;
}

$tpl = new Template_API();
$tpl->setTemplate('upgrade.tpl.html');

switch ($step) {
    case 1:
        // do nothing
    break;
    case 2:
    if (!empty($_POST["upgrade"])) {
        $res = upgrade();
        $tpl->assign("result", $res);
        if ($res != 'success') {
        	$step = 1;
        }
    }
    break;
    case 3:
        $sanity = SanityChecks::runAllChecks();
        $tpl->assign('sanity_results',$sanity);
    break;
}

$tpl->assign('step', $step);


$tpl->displayTemplate();

?>
