<?php
/*
 * Fez Devel
 * University of Queensland Library
 * Created by Matthew Smith on 19/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
define('APP_UNIT_TESTING', true);
// Where is config.inc?

require_once('../../configs/config.inc.php');
//require_once('../../public/init.php');
define(APP_TEST_PATH, APP_PATH.'/test/');
//echo "HERE".APP_TEST_PATH."\n\n";
//require_once APP_PEAR_PATH.'PHPUnit/Framework.php';
require_once APP_TEST_PATH.'test_common.php';
