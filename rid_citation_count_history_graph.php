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

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "ezc/Base/src/base.php");

$pid = @$_POST["pid"] ? $_POST["pid"] : $_GET["pid"];

$record_obj = new RecordObject($pid);
$object_title = $record_obj->getTitle();

$record = new Record();
$list = $record->getThomsonCitationCountHistory($pid, false, 'ASC');

$count = count($list);
if($count < 1) {
	print '<i>Not enough historical data available at this time.</i>';
	exit;
}

$citation_data = array();

$format = 'd M Y';
$range = $list[$count-1]['tc_created'] - $list[0]['tc_created'];

// More than 1 year's worth of history
if($range > 31536000) {
	$format = 'Y';	
}
// More than 1 month's history (based on 30 days in a month)
else if($range > 2592000) {
	$format = 'M Y';	
}
	
for($i=0; $i<count($list); $i++) {
	
	$date = date($format, $list[$i]['tc_created']);	
	$citation_data[$date] = $list[$i]['tc_count'];
}

if(count($citation_data) < 1) {
	print '<i>Not enough historical data available at this time.</i>';
	exit;
}

$object_title = $record_obj->getTitle();

$graph_data = array('Citation Count' => $citation_data);

$graph = new ezcGraphBarChart();
$graph->palette = new ezcGraphPaletteEz();

$graph->yAxis = new ezcGraphChartElementNumericAxis(); 
$graph->yAxis->min = 0;
$graph->xAxis->labelCount = count($citation_data);

if(isset($_GET['output'])) {
	$graph->driver = new ezcGraphGdDriver(); 
	$graph->options->font = APP_INC_PATH . 'ezc/tutorial_font.ttf';
	$graph->driver->options->supersampling = 1; 
	$graph->driver->options->imageFormat = ($_GET['output'] == 'IMG_PNG') ? IMG_PNG : IMG_JPEG;
}

$graph->xAxis->label = 'Time';
$graph->yAxis->label = 'Citation Count';
//$graph->title = 'ResearcherID Citation Count History';
$graph->legend = false; 

// Add data
foreach ( $graph_data as $language => $data )
{
    $graph->data[$language] = new ezcGraphArrayDataSet( $data );
}
$graph->data['Citation Count']->symbol = ezcGraph::NO_SYMBOL;

$graph->renderer = new ezcGraphRenderer3d();

$graph->renderer->options->legendSymbolGleam = .5;
$graph->renderer->options->barChartGleam = .5;

$graph->renderToOutput( 780, 300 ); 



