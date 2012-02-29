<?php

class Framework_UnitTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez Framework Unit');
        // @todo: Setup Unit tests, if any
//        $suite->addTestSuite('Unit_TerminalmapModelTest');

        return $suite;
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }
}