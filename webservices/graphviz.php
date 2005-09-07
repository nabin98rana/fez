<?php
include_once('../config.inc.php');
include_once(APP_INC_PATH. 'graphviz.php');

        $encoded_dot = $_GET['dot'];
        $dot = base64_decode($encoded_dot);

        // do here something
        if (@$_GET['cmapx'] == 1) {
            header("Content-type: application/xhtml+xml");
            echo Graphviz::getCMAPX($dot);
        } else {
            header("Content-type: " . image_type_to_mime_type(IMAGETYPE_PNG));
            Graphviz::getPNG($dot);
        }

?>
