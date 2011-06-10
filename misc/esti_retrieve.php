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

include_once('../config.inc.php');
include_once(APP_INC_PATH . 'class.esti_search_service.php');
include_once(APP_INC_PATH . "class.record.php");

if(isset($_GET['d']) && @constant('EstiSearchService::'.$_GET['d']) !== NULL) {
	
	$databases = array('WOS');
	if(in_array($_GET['db'], $databases)) {
		$database = $_GET['db'];
	}
	else {
		$database = 'WOS';
	}
	
	$doc_type = constant('EstiSearchService::'.$_GET['d']);
//	$query = 'OG=(Univ Queensland) and DT=('.$doc_type.')';
	$query = 'OG=(Univ Queensland) and DT=('.$doc_type.')';

        //$aut = split(':', "WOS:000225243000002");
        $aut = preg_split(':', "WOS:A1982PK71700083");

	//$query = '000225243000002';
	$depth = '2000-2009';
	$editions = '';
	$sort = '';
	$first_rec = 1; //rand(0,99);
	$num_recs = 1;
	
	$result = EstiSearchService::retrieve($aut[1], $aut[0]); 
print_r($result); exit;
	//$result = EstiSearchService::searchRetrieve($database, $query);
//	$result = EstiSearchService::searchRetrieve($database, $query, $depth, $editions, $sort, $first_rec, $num_recs);
	
	if($result['recordsFound'] > 0) {
		header('content-type: application/xml; charset=utf-8');
		print_r($result['records']);
	}
	else {
		print 'No records found. <a href="esti_retrieve.php">Back</a>';
	}
	exit;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ESTI : Retrieve Sample WoS Record</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">
	body {
		font:80%/1 sans-serif;
	}
	fieldset {
		padding: 1em;
		font:80%/1 sans-serif;
	}
	label {
		float:left;
		width:100px;
		margin-right:0.5em;
		padding-top:0.2em;
		text-align:right;
		font-weight:bold;
	}	
	</style>
</head>
<body>
<h1>ESTI : Retrieve Sample WoS Record</h1>

<p>Retrieve's a sample record from WoS matching the selected document type below, where UQ is in the address field
and the database search depth is 2000-2009.</p>
<form action="" method="get">

<fieldset>
<legend>ESTI Search Options</legend>
<label for="db">Database:</label>
<select name="db">
	<option value="WOS">Web of Science (WOS)</option>
	<option value="ISIP">ISI Proceedings (ISIP)</option>
</select>
<br /><br />
<label for="d">Document Type:</label>
<select name="d">
	<option value="DOC_TYPE_ABSTRACT_OF_PUBLISHED_ITEM">Abstract of Published Item</option>
	<option value="DOC_TYPE_ART_EXHIBIT_REVIEW">Art Exhibit Review</option>
	<option value="DOC_TYPE_ARTICLE">Article</option>
	<option value="DOC_TYPE_BIBLIOGRAPHY">Bibliography</option>
	<option value="DOC_TYPE_BIOGRAPHICAL_ITEM">Biographical-Item</option>
	<option value="DOC_TYPE_BOOK_REVIEW">Book Review</option>
	<option value="DOC_TYPE_CHRONOLOGY">Chronology</option>
	<option value="DOC_TYPE_CORRECTION_ADDITION">Correction, Addition</option>
	<option value="DOC_TYPE_DANCE_PERFORMANCE_REVIEW">Dance Performance Review</option>
	<option value="DOC_TYPE_DATABASE_REVIEW">Database Review</option>
	<option value="DOC_TYPE_DISCUSSION">Discussion</option>
	<option value="DOC_TYPE_EDITORIAL_MATERIAL">Editorial Material</option>
	<option value="DOC_TYPE_EXCERPT">Excerpt</option>
	<option value="DOC_TYPE_FICTION_CREATIVE_PROSE">Fiction, Creative Prose</option>
	<option value="DOC_TYPE_FILM_REVIEW">Film Review</option>
	<option value="DOC_TYPE_HARDWARE_REVIEW">Hardware Review</option>
	<option value="DOC_TYPE_ITEM_ABOUT_AN_INDIVIDUAL">Item About an Individual</option>
	<option value="DOC_TYPE_LETTER">Letter</option>
	<option value="DOC_TYPE_MEETING_ABSTRACT_1">Meeting Abstract (M)</option>
	<option value="DOC_TYPE_MEETING_ABSTRACT_2">Meeting Abstract (MC)</option>
	<option value="DOC_TYPE_MUSIC_PERFORMANCE_REVIEW">Music Performance Review</option>
	<option value="DOC_TYPE_MUSIC_SCORE">Music Score</option>
	<option value="DOC_TYPE_MUSIC_SCORE_REVIEW">Music Score Review</option>
	<option value="DOC_TYPE_NEWS_ITEM">News Item</option>
	<option value="DOC_TYPE_NOTE">Note</option>
	<option value="DOC_TYPE_POETRY">Poetry</option>
	<option value="DOC_TYPE_PROCEEDINGS_PAPER_1">Proceedings Paper ($)</option>
	<option value="DOC_TYPE_PROCEEDINGS_PAPER_2">Proceedings Paper (P)</option>
	<option value="DOC_TYPE_PROCEEDINGS_PAPER_3">Proceedings Paper (U)</option>
	<option value="DOC_TYPE_RECORD_REVIEW">Record Review</option>
	<option value="DOC_TYPE_REPRINT">Reprint</option>
	<option value="DOC_TYPE_REVIEW">Review</option>
	<option value="DOC_TYPE_SCRIPT">Script</option>
	<option value="DOC_TYPE_SOFTWARE_REVIEW">Software Review</option>
	<option value="DOC_TYPE_TV_REVIEW_RADIO_REVIEW_VIDEO">TV Review, Radio Review, Video</option>
	<option value="DOC_TYPE_THEATER_REVIEW">Theater Review</option>
</select>
<br /><br />
<input type="submit" name="Submit" value="Retrieve Sample Record" />
</fieldset>

</form>
</body>
</html>
