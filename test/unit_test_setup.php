<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
 
define('APP_UNIT_TESTING', true);
// Where is config.inc?

require_once('../config.inc.php');
define(APP_TEST_PATH, APP_PATH.'/test/');
require_once APP_PEAR_PATH.'PHPUnit/Framework.php';
require_once APP_TEST_PATH.'test_common.php';
 
 
?>
