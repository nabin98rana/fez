<?php

class Framework_FunctionalTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez Framework Functional');
        $suite->addTestSuite('Functional_HomepageTest');

        return $suite;
    }

    protected function setUp()
    {
//        @todo : Database schema setup for tests
//        $db = Zend_Registry::get('db');
//        $createSql = file_get_contents(FILE_DB_CREATE);
//        $insertSql = file_get_contents(FILE_DB_INSERT);
//        $db->query($createSql);
//        $db->query($insertSql);
    }

    protected function tearDown()
    {
    }
}
