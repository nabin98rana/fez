<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 5/07/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
class TestCommon
{
    /**
     * treatXML
     * Allow XML strings to be compared by replacing whitespace including line returns with a single space.
     */
    function treatXML($xml)
    {
        return trim(preg_replace('/\s+/',' ',$xml));
    }
}
?>
