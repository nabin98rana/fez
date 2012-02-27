<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     in_array
 * Version:  1.0
 * Author:   Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * Credits:  Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * Purpose:  Replicate the functionality of PHP in_array function for Smarty
 * Input:    var       =  name of the array
 *           values    =  list of values (seperated by delimiter)
 *           delimiter =  value delimiter, default is ","
 *
 * Examples: {if assign_array var="foo" values="bar1,bar2"}
 *           {assign_array var="foo" values="bar1;bar2;bar3" delimiter=";"}
 * -------------------------------------------------------------
 */
function smarty_function_in_array($params, &$smarty)
{
    extract($params);

  if(empty($delimiter)) {
    $delimiter = ',';
  }

    if (empty($var)) {
        $smarty->trigger_error("assign_array: missing 'var' parameter");
        return;
    }

    if (!in_array('values', array_keys($params))) {
        $smarty->trigger_error("assign_array: missing 'values' parameter");
        return;
    }

    $smarty->assign($var, explode($delimiter,$values) );
}

/* vim: set expandtab: */

?>
