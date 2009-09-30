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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.author.php");

$sek_suggest_function = false;

$xsdmf_id = $_GET['xsdmf_id'];
$sek_id = $_GET['sek_id'];
$use_xsdmf_id = false;

if ((!empty($xsdmf_id)) && is_numeric($xsdmf_id)) {	
	$use_xsdmf_id = true;
	$sek_suggest_function = Search_Key::getSuggestFunctionByXSDMF_ID($xsdmf_id);
}
else if (! empty($sek_id)) {
	$sek_suggest_function = Search_Key::getSuggestFunctionBySek_ID($sek_id);
}
if(! $sek_suggest_function) {
	return false;
}

$suggestions = array();
$query = Misc::escapeString($_GET['query']);
if ($sek_suggest_function == "Search_Key::suggestSearchKeyIndexValue") {
	
	if($use_xsdmf_id) {
		$sek_details = Search_Key::getDetailsByXSDMF_ID($xsdmf_id);
	}
	else {
		$sek_details = Search_Key::getDetails($sek_id);
	}
	
	eval('$suggestions = '.$sek_suggest_function.'($sek_details, "'.$query.'", true);');
} else {
	eval('$suggestions = '.$sek_suggest_function.'("'.$query.'", true);');
}
$suggestions = array(
    'Result'    =>  $suggestions
);

echo json_encode($suggestions);
FezLog::get()->close();