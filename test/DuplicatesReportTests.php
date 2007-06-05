<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 1/06/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
require_once('unit_test_setup.php');
 
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'DuplicatesReportTests::main');
}
 
require_once APP_PEAR_PATH.'PHPUnit/TextUI/TestRunner.php';
 
require_once 'DuplicatesReportMiscTest.php';
require_once 'DuplicatesReportRearrangeSetsTest.php';
require_once 'DuplicatesReportMergeRearrangedSetsTest.php';
require_once 'DuplicatesReportGenerateXMLTest.php';
require_once 'DuplicatesReportGetListingTest.php';

class DuplicatesReportTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DuplicatesReportTests Suite');
 
        $suite->addTestSuite('DuplicatesReportMiscTest');
        $suite->addTestSuite('DuplicatesReportRearrangeSetsTest');
        $suite->addTestSuite('DuplicatesReportMergeRearrangedSetsTest');
        $suite->addTestSuite('DuplicatesReportGenerateXMLTest');
        $suite->addTestSuite('DuplicatesReportGetListingTest');
        
        // ...
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'DuplicatesReportTests::main') {
    DuplicatesReportTests::main();
} 
 
?>
