<?php

/**
 * Lightweight class for performing basic assertions for use with
 * behat.
 *
 * Usage:
 *   $e = new Expectation();
 * Then:
 *   $e->setThing('foo');
 *   $e->equals('bar'); => exception
 * Or:
 *   $e->expect('foo')->equals('bar'); => exception
 *   
 */
class Expectation
{

    public function __construct($thing = NULL)
    {
        $this->thing = $thing;
    }

    public function setThing($thing)
    {
        $this->thing = $thing;
        return $this;
    }

    // eg expect('foo')->equals('bar'); // => exception

    public function expect($thing)
    {
        $this->setThing($thing);
        return $this;
    }

    // ------------------------------------------------------------
    // Assertion code
    
    public function equals($expected, $comment=null)
    {
        if ($expected != $this->thing) {
            throw new Exception("Expected '{$this->thing}' to equal '$expected'. $comment");
        }
        return $this;
    }

    public function gt($num, $comment=null)
    {
        if ($this->thing <= $num) {
            throw new Exception("Expected '{$this->thing}' > '$expected'. $comment");
        }
        return $this;
    }

    public function not_equals($expected, $comment=null)
    {
        if ($expected == $this->thing) {
            throw new Exception("Expected '{$this->thing}' to not equal '$expected'. $comment");
        }
        return $this;
    }

    // @param string $pattern eg /regex-here/

    public function matches($pattern, $comment=null)
    {
        if (!preg_match($pattern, $this->thing)) {
            throw new Exception("Expected '{$this->thing}' to match regex '$pattern'. $comment");
        }
        return $this;
    }

    public function contains($string, $comment=null)
    {
        if (strpos($this->thing, $string) !== false) {
            return $this;
        }
        throw new Exception("Expected '{$this->thing}' to match contain '$string'. $comment");
    }
}
