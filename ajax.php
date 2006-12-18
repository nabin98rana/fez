<?php
/*
 * Created on 6/11/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 *
 */

include_once("config.inc.php");
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_classes.php");


NAJAX_Server::allowClasses(array('SelectOrgStructure', 'Suggestor', 'NajaxRecord',
    'SelectOrgStructure','SelectReindexInfo','SelectCreateInfo'));
if (NAJAX_Server::runServer()) {
    exit;
}

?>
