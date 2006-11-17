<?php

require_once('config.inc.php');
include_once(APP_INC_PATH.'class.fedora_api.php');

/*
 * Created on 3/11/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function smarty_function_element_editor($params, &$smarty) {
    extract($params);
    $escaped_path = XSD_HTML_Match::escapeXPath($xpath);
    // $html_result = "<div><pre>$xpath:$escaped_path</pre></div>";
    $html_result = XSD_HTML_Match_Generator::generateEditWidget($pid, $escaped_path, $label);
    // return js editor(s) html
    
    return $html_result;
 }
?>
