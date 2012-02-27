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
 
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportMiscTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportRearrangeSetsTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportMergeRearrangedSetsTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGenerateXMLTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGetListingTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGetItemDetailsTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportSetDuplicateXMLTest.php';
require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportSwapBaseXMLTest.php';



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
        $suite->addTestSuite('DuplicatesReportGetItemDetailsTest');
        $suite->addTestSuite('DuplicatesReportSetDuplicateXMLTest');
        $suite->addTestSuite('DuplicatesReportSwapBaseXMLTest');
        
        // ...
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'DuplicatesReportTests::main') {
    DuplicatesReportTests::main();
} 
 
?>
