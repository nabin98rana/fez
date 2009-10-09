<?php

include_once("config.inc.php");

// Prepends include_path. You could alternately do this via .htaccess or php.ini
set_include_path( 
    APP_PATH . 'min/lib'
    . PATH_SEPARATOR . get_include_path()
);


$file = substr($_SERVER['PATH_INFO'], 1);
$file = str_replace('..', '', $file);

if(! preg_match('/^(js\/)?([a-zA-Z0-9_\-\/\.])+.js(\?*.?)$/', $file) ) {
	header("HTTP/1.0 404 Not Found");
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
		'js/editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'js/common.js'     =>  array(APP_PATH . '/js/browserSniffer.js', APP_PATH .'/js/global.js', APP_PATH .'/js/validation.js'),
        'js/tabs.js'       =>  array(APP_PATH . '/js/tabcontent.js', APP_PATH .'/js/ajaxtabs.js')
    )
));