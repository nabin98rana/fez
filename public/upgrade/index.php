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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

set_time_limit(3600);

@DEFINE("APP_BENCHMARK", false);
@DEFINE("APP_CURRENT_LANG", "en");

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.sanity_checks.php");
include_once(APP_INC_PATH . "class.upgrade.php");

$step = @$_GET["step"] ? @$_GET["step"] : @$_POST["step"];
if (empty($step)) {
	$step = 1;
}

$tpl = new Template_API();
$tpl->setTemplate('upgrade.tpl.html');
$tpl->assign('setup', true);

$skip = 0;

$up = new upgrade;

switch ($step) {
    case 1:
        // do nothing
    break;
    case 2:
    if (!empty($_POST["upgrade"]) || !empty($_POST["skip"])) {
        if (!empty($_POST["skip"])) {
            $skip = 1;
        }
        list($res, $message) = $up->upgrade($skip);
        $tpl->assign("result", $message);
        $tpl->assign("result_good", $res);
        $tpl->assign('display_config_changes', $up->runningDBconfig());
        if (!$res) {
        	$step = 1;
        }
    }
    break;
    case 3:
        $sanity = SanityChecks::runAllChecks();
        $tpl->assign('sanity_results',$sanity);
    break;
    case 4:
        // Run the config upgrade thing.
        $result = $up->saveExistingConfigToDB();
        $tpl->assign("upgrade_result", $result);
    break;
}

$tpl->assign('step', $step);
$tpl->displayTemplate();

?>
