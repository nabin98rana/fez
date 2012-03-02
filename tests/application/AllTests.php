<?php

class AllTests 
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez');
        
        // Grep all test suites - both Functional & Unit tests.
        $suite->addTest(Framework_AllTests::suite());

        return $suite;
    }

}