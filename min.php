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

$shib_source = array();
if(SHIB_SWITCH == 'ON' && SHIB_VERSION == '2') {
	$shib_source[] = new Minify_Source(array(
	    'id' 				=> 'js/shib.js',
	    'getContentFunc' 	=> 'shib_wayf_js_fetch',
	    'contentType' => Minify::TYPE_JS,
		'lastModified' => ($_SERVER['REQUEST_TIME'] - $_SERVER['REQUEST_TIME'] % 3600), // cache for 1 hour
	));
}

Minify::setCache();
Minify::serve('Groups', array(
    'groups' => array(
		$file =>  array(APP_PATH . $file),
        'editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'common.js'     =>  array(APP_PATH . '/js/browserSniffer.js', APP_PATH .'/js/global.js', APP_PATH .'/js/validation.js'),
        'tabs.js'       =>  array(APP_PATH . '/js/tabcontent.js', APP_PATH .'/js/ajaxtabs.js'),
		'js/editmeta.js'   =>  array(APP_PATH . '/js/editmetadata.js'),
        'js/common.js'     =>  array(APP_PATH . '/js/browserSniffer.js', APP_PATH .'/js/global.js', APP_PATH .'/js/validation.js'),
        'js/tabs.js'       =>  array(APP_PATH . '/js/tabcontent.js', APP_PATH .'/js/ajaxtabs.js'),
		'js/shib.js'       =>  $shib_source,		
    )
));


function shib_wayf_js_fetch() {
	$cache_file = APP_TEMP_DIR . '/shib_wayf_js_fetch_cache.js';
	$js = Misc::processURL(SHIB_WAYF_JS);
	if(is_array($js) && count($js) > 0 && (!empty($js[0])) && $js[1]['http_code'] == '200') {
		@file_put_contents($cache_file, $js[0]);
		return $js[0];
	}
	else {
		return @file_get_contents($cache_file);
	}
}