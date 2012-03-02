<?php

class Framework_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez Framework');
        
        // Grep Functional & Unit tests
//        $suite->addTest(Framework_UnitTests::suite());
        $suite->addTest(Framework_FunctionalTests::suite());

        return $suite;
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }
}