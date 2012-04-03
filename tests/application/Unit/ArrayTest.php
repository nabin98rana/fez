<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 18/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
require_once 'phpunit/Framework.php';
 
class ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testNewArrayIsEmpty()
    {
        // Create the Array fixture.
        $fixture = array();
 
        // Assert that the size of the Array fixture is 0.
        $this->assertEquals(0, sizeof($fixture));
    }
 
    public function testArrayContainsAnElement()
    {
        // Create the Array fixture.
        $fixture = array();
 
        // Add an element to the Array fixture.
        $fixture[] = 'Element';
 
        // Assert that the size of the Array fixture is 1.
        $this->assertEquals(1, sizeof($fixture));
    }
}
?>