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
        'editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'common.js'     =>  array(APP_PATH . '/js/browserSniffer.js', APP_PATH .'/js/global.js', APP_PATH .'/js/validation.js'),
        'tabs.js'       =>  array(APP_PATH . '/js/tabcontent.js', APP_PATH .'/js/ajaxtabs.js'),
    )
));