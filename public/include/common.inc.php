<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 22/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
// workaround for bug http://bugs.php.net/bug.php?id=41293 where raw headers are not being set
if (phpversion()=="5.2.2" && !isset($HTTP_RAW_POST_DATA)) {
    $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
}
