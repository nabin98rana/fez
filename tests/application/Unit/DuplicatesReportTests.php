<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 1/06/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
//require_once('unit_test_setup.php');
 
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Unit_DuplicatesReportTests::main');
}
 
//require_once APP_PEAR_PATH.'PHPUnit/TextUI/TestRunner.php';
 
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportMiscTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportRearrangeSetsTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportMergeRearrangedSetsTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGenerateXMLTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGetListingTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportGetItemDetailsTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportSetDuplicateXMLTest.php';
//require_once APP_TEST_PATH.'duplicates_report/DuplicatesReportSwapBaseXMLTest.php';



class Unit_DuplicatesReportTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('DuplicatesReportTests Suite');
 
        $suite->addTestSuite('Unit_DuplicatesReport_MiscTest');
        $suite->addTestSuite('Unit_DuplicatesReport_RearrangeSetsTest');
        $suite->addTestSuite('Unit_DuplicatesReport_MergeRearrangedSetsTest');
        $suite->addTestSuite('Unit_DuplicatesReport_GetListingTest');
        $suite->addTestSuite('Unit_DuplicatesReport_GetItemDetailsTest');
        $suite->addTestSuite('Unit_DuplicatesReport_SetDuplicateXMLTest');
        $suite->addTestSuite('Unit_DuplicatesReport_SwapBaseXMLTest');
        
        // ...
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'Unit_DuplicatesReportTests::main') {
    Unit_DuplicatesReportTests::main();
} 
 
?>
