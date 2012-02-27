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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

include_once("config.inc.php");

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "ezc/Base/src/base.php");

class citationCustomPalette extends ezcGraphPalette
{
	protected $axisColor = '#000000';
	protected $majorGridColor = '#000000BB';
	protected $dataSetColor = array(
									'#4E9A0688',
									'#3465A4',
									'#F57900'
								);								
	protected $dataSetSymbol = array(
									ezcGraph::BULLET,
								);
	protected $fontName = 'sans-serif';
	protected $fontColor = '#555753';
}

$pid = @$_POST["pid"] ? $_POST["pid"] : $_GET["pid"];

$record_obj = new RecordObject($pid);
$object_title = $record_obj->getTitle();

$record = new Record(); 
$list = $record->getScopusCitationCountHistory($pid, false, 'ASC');

$count = count($list);
$citation_data = array();

if($count > 0) {
	$format = 'd M Y';
	$range = $list[$count-1]['sc_created'] - $list[0]['sc_created'];
	
	// More than 1 year's worth of history
	if($range > 31536000) {
		$format = 'Y';	
	}
	// More than 1 month's history (based on 30 days in a month)
	else if($range > 2592000) {
		$format = 'M Y';	
	}
	
	for($i=0; $i<count($list); $i++) {
		
		$date = date($format, $list[$i]['sc_created']);	
		$citation_data[$date] = $list[$i]['sc_count'];
	}
}

$graph = new ezcGraphBarChart();

if($count < 1 || count($citation_data) < 2) {
	
	if(isset($_GET['output'])) {
		header ('Content-Type: image/gif');
		echo file_get_contents('images/no_historical_data.gif');
		exit;	
	}
	
	if($count > 0) {	
		$citation_data = array('Earlier dates not recorded' => 0, date('M Y', $list[0]['sc_created']) => $list[0]['sc_count']);
	}
	else {
		$citation_data = array('Earlier dates not recorded' => 0, date('M Y') => 0);
	}
	$graph->title = 'No historical data available at this time.';
}


$graph_data = array('Citation Count' => $citation_data);

//$graph->palette = new ezcGraphPaletteEz();
$graph->palette = new citationCustomPalette(); 
$graph->yAxis = new ezcGraphChartElementNumericAxis(); 
$graph->yAxis->min = 0;
$graph->xAxis->labelCount = count($citation_data);

if( (!isset($_GET['ext'])) || $_GET['ext'] == 'png' || $_GET['ext'] == 'jpg') {
	$graph->driver = new ezcGraphGdDriver(); 
	$graph->options->font = APP_INC_PATH . 'ezc/tutorial_font.ttf';
	$graph->driver->options->supersampling = 1; 
	$graph->driver->options->imageFormat = ($_GET['ext'] == 'png') ? IMG_PNG : IMG_JPEG;
}
else if(extension_loaded('ming') && $_GET['ext'] == 'swf') {
	// Flash version
	$graph->driver = new ezcGraphFlashDriver();
	$graph->options->font = APP_INC_PATH . 'ezc/tutorial_font.fdb';
}

$graph->xAxis->label = 'Time';
$graph->yAxis->label = 'Citation Count';
$graph->legend = false; 

// Add data
foreach ( $graph_data as $language => $data )
{
    $graph->data[$language] = new ezcGraphArrayDataSet( $data );
}
$graph->data['Citation Count']->symbol = ezcGraph::NO_SYMBOL;
$graph->data['Citation Count']->highlight = true;
$graph->options->highlightSize = 12;

$graph->renderer = new ezcGraphRenderer3d();
//$graph->renderer->options->legendSymbolGleam = .5;
//$graph->renderer->options->barChartGleam = .5;

$graph->renderToOutput( 780, 300 ); 






