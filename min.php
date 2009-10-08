<?php

include_once("config.inc.php");

// Prepends include_path. You could alternately do this via .htaccess or php.ini
set_include_path( 
    APP_PATH . 'min/lib'
    . PATH_SEPARATOR . get_include_path()
);

$file = substr($_SERVER['PATH_INFO'], 1);

// Check it's of the format js/path/filename.js
if(!preg_match('/^js\/([a-zA-Z0-9_\-\/])+.js$/', $file)) {
	exit;
}

require 'Minify.php';
Minify::setCache();
Minify::serve('Groups', array(
    'groups' => array(
		$file =>  array(APP_PATH . $file),
        'editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'common.js'     =>  array(APP_PATH . '/js/browserSniffer.js', APP_PATH .'/js/global.js', APP_PATH .'/js/validation.js'),
        'tabs.js'       =>  array(APP_PATH . '/js/tabcontent.js', APP_PATH .'/js/ajaxtabs.js'),
    )
));