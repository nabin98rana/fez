<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Jonathan Harker <jonathan@catalyst.net.nz>                  |
// +----------------------------------------------------------------------+

/*
 * This lets us return the citation HTML as OEmbed code.
 * See documentation at http://oembed.com/
 */
include_once dirname(__FILE__).'/config.inc.php';

$filter = new Zend_Filter_Alpha();
$format    = $filter->filter($_GET['format_type']);
$filter = new Zend_Filter_Int();
$maxwidth  = $filter->filter($_GET['maxwidth']);
$maxheight = $filter->filter($_GET['maxheight']);
$url = $_GET['url'];

function oembed_notfound($format='') {
    header('HTTP/1.0 404 Not Found');
    if ($format == 'json') {
      header('Content-type: application/json');
      echo json_encode(array('Success: false', 'Message: "404 pid or url not found"'));
    } else {
      echo '404 pid or url not found';
    }

    exit;
}

if (empty($maxwidth)) {
    $maxwidth = 640;
}
if (empty($maxheight)) {
    $maxheight = 384;
}
if (empty($format)) {
    $format = 'json';
}
if (empty($url)) {
    oembed_notfound($format);
}

preg_match('/'.APP_PID_NAMESPACE.':[0-9]+/', $url, $match);
if (empty($match)) {
    oembed_notfound($format);
}
$pid = $match[0];

$options = array();
$skpid = Search_Key::getID('Pid');
$options["searchKey$skpid"] = $pid;
$list = Record::getListing($options, array("Lister"), 0, 1, "Title", true);
if (empty($list) or count($list['list']) < 1) {
    oembed_notfound($format);
}
$item = $list['list'][0];

// TODO: code here could inspect genre or datastream MIME type, return embedded player, etc.
$oembed = new SimpleXMLElement('<oembed></oembed>');

// Use absolute URLs
$citation = str_replace('href="/', 'href="'.APP_BASE_URL, $item['rek_citation']);

// OEmbed type can be 'photo', 'video', 'link', 'rich'
$oembed->addChild('type',         'rich');
$oembed->addChild('version',      '1.0');
$oembed->addChild('maxwidth',     $maxwidth);
$oembed->addChild('maxheight',    $maxheight);
$oembed->addChild('html',         $citation);
$oembed->addChild('provider_name', APP_ORG_NAME);
$oembed->addChild('provider_url',  APP_BASE_URL);
$oembed->addChild('pid',          $pid);
$oembed->addChild('identifier',   $url);
$oembed->addChild('url',          $url);
foreach ($item['rek_author'] as $author) {
    $oembed->addChild('author_name', $author);
}
foreach ($item['rek_author_id'] as $author_id) {
    $oembed->addChild('author_url', APP_BASE_URL."list/author_id/$author_id");
}
$oembed->addChild('title',        $item['rek_title']);
$oembed->addChild('description',  $item['rek_description']);
$oembed->addChild('genre',        $item['rek_genre']);
foreach ($item['rek_keywords'] as $keyword) {
    $oembed->addChild('keyword', $keyword);
}
foreach ($item['rek_subject'] as $subject) {
    $oembed->addChild('subject', $subject);
}
$oembed->addChild('created_date', $item['rek_created_date']);
$oembed->addChild('updated_date', $item['rek_updated_date']);
$oembed->addChild('publication_date', $item['rek_publication_date']);
$oembed->addChild('publisher', $item['rek_publisher']);

foreach ($item['rek_file_attachment_name'] as $file) {
    $oembed->addChild('file', APP_BASE_URL."view/{$pid}/{$file}");
}

if ($format == 'xml') {
    header('Content-type: text/xml');
    echo $oembed->asXML();
} else {
    $json = json_encode($oembed);
    if (json_last_error()) {
        oembed_notfound($format);
    }
    header('Content-type: application/json');
    echo $json;
}

