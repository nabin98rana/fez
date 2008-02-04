<?php
// Yahoo! proxy
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.author.php");

Error_Handler::logError('ysearch_proxy', __FILE__, __LINE__);

$suggestions = Author::suggest($_GET['query'], true);

$suggestions = array(
    'Result'    =>  $suggestions
);

echo json_encode($suggestions);

?>
