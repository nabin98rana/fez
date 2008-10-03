<?php

include_once("config.inc.php");

// Prepends include_path. You could alternately do this via .htaccess or php.ini
set_include_path( 
    APP_PATH . 'min/lib'
    . PATH_SEPARATOR . get_include_path()
);

require 'Minify.php';
Minify::setCache();
Minify::serve('Groups', array(
    'groups' => array(
        'editmeta.js'   =>  array('//js/editmetadata.js'),
        'common.js'     =>  array('//js/browserSniffer.js', '//js/global.js', '//js/validation.js'),
        'tabs.js'       =>  array('//js/tabcontent.js', '//js/ajaxtabs.js'),
    )
));
?>