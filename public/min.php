<?php

include_once("config.inc.php");

// Prepends include_path. You could alternately do this via .htaccess or php.ini
set_include_path(
    APP_PATH . 'min/lib'
    . PATH_SEPARATOR . get_include_path()
);

$file = substr($_SERVER['REQUEST_URI'], 1);
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
		'lastModified' => ($_SERVER['REQUEST_TIME'] - $_SERVER['REQUEST_TIME'] % 1), // cache for 1 hour
	));
}

$max_age = 1800;
if($file == 'js/shib.js') {
	$max_age = 0;
}
$min_allowDebugFlag = true;
$min_serveOptions = array();
if ($min_allowDebugFlag && isset($_GET['debug'])) {
    $min_serveOptions['debug'] = true;
}

if (isset($min_errorLogger)) {
    require_once 'Minify/Logger.php';
    if (true === $min_errorLogger) {
        require_once 'FirePHP.php';
        Minify_Logger::setLogger(FirePHP::getInstance(true));
    } else {
        Minify_Logger::setLogger($min_errorLogger);
    }
}

// ensure $_SERVER['DOCUMENT_ROOT'] never has a trailing slash, because some servers don't have the trailing slash while others do.
$_SERVER['DOCUMENT_ROOT'] = rtrim($_SERVER['DOCUMENT_ROOT'],'/');
$pathfixup = substr( str_replace($_SERVER["DOCUMENT_ROOT"], '', APP_PATH), 1); //GC

$min_serveOptions['groups'] = array(
        $file =>  array($_SERVER["DOCUMENT_ROOT"] . $_SERVER["REQUEST_URI"]), //GC
        $pathfixup . 'js/editmeta.js'   =>  array(APP_PATH . 'js/editmetadata.js'),
        $pathfixup . 'js/common.js'     =>  array(APP_PATH . 'js/browserSniffer.js', APP_PATH .'js/global.js', APP_PATH .'js/validation.js'),
        $pathfixup . 'js/tabs.js'       =>  array(APP_PATH . 'js/tabcontent.js', APP_PATH .'js/ajaxtabs.js'),
        $pathfixup . 'js/autosuggestgroup.js'       =>  array(APP_PATH . 'js/suggestions.js', APP_PATH .'js/autosuggest.js', APP_PATH .'js/autocomplete.js'),
        $pathfixup . 'js/yuisuggestgroup.js'       =>  array(APP_PATH . 'js/yahoo/utilities.js', APP_PATH .'js/yahoo/autocomplete.js'),
        $pathfixup . 'js/swfgroup.js'       =>  array(APP_PATH . 'js/flash_detection.js', APP_PATH .'js/swfupload/swfupload.js', APP_PATH .'js/swfupload/swfupload.queue.js', APP_PATH .'js/swfuploader.js'),
	    $pathfixup . 'js/shib.js'       =>  $shib_source,
        );
$min_serveOptions['maxAge'] = $max_age;
Minify::setCache();
Minify::serve('Groups', $min_serveOptions);

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
