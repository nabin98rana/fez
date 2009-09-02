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

$query = 'OG=(Univ Queensland) and DT=(@)';
$depth = '2008-2009';
$editions = '';
$sort = '';
$first_rec = 1;
$num_recs = 100;

$result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);

$records_found = (int)$result['recordsFound'];
$pages = ceil(($records_found/$num_recs));

for($i=0; $i<$pages; $i++) {
	
	$first_rec += $num_recs;
	
	if($i>0)
		$result = EstiSearchService::searchRetrieve('WOS', $query, $depth, $editions, $sort, $first_rec, $num_recs);
	
	$records = @simplexml_load_string($result['records']);	
	
	if($records) {
		foreach($records->REC as $record) {
			$pid = Record::getPIDByIsiLoc($record->item->ut);
			
			if(! $pid) {
				// ingest	
				addRecord($record);
			}
		}
	}	
}

// TODO: run de-dedupe report



/**
 * Adds the record to Fedora
 * 
 * @param $record The WoS record to add to Fedora
 * 
 * @return bool True if succeeded otherwise false
 */
function addRecord($record) {
    
	if($record->item->doctype != 'Article')
		return false;
		
	$date_issued = '';
	if(@$record->item->attributes()->coverdate) {
		preg_match('/(\d{4})(\d{2})/', $record->item->attributes()->coverdate, $matches);
		if(count($matches) == 3) {
			if($matches[2] == '00')
				$date_issued = $matches[1];
			else
				$date_issued = $matches[1] . '-' . $matches[2];
		}
		else {
			if(@$record->item->bib_issue->attributes()->year) {
				$date_issued = $record->item->bib_issue->attributes()->year;
			}
		}
	}
	
	$_record = $record->item;
	unset($record);
	$record = $_record;
	unset($_record);
	
	$item_title = htmlspecialchars($record->item_title, ENT_QUOTES);
	$source_title = htmlspecialchars($record->source_title, ENT_QUOTES);
	$title = substr(htmlspecialchars($record->item_title), 0, 255);
	
    // MODS
    $mods = array();
    $mods['titleInfo']['title'] = $item_title;
    
    $mods['name'][0]['id'] = '0';
    $mods['name'][0]['authority'] = APP_ORG_NAME;
    $mods['name'][0]['namePart_personal'] = $record->authors->primaryauthor;
   	$mods['name'][0]['role']['roleTerm_text'] = 'author';

   	$i = 1;
    foreach((array)$record->authors->author as $author) {
    	$mods['name'][$i]['id'] = '0';
    	$mods['name'][$i]['authority'] = APP_ORG_NAME;
    	$mods['name'][$i]['namePart_personal'] = $author;
    	$mods['name'][$i]['role']['roleTerm_text'] = 'author';
    	$i++;
    }
    
    $i = 0;
    foreach((array)$record->keywords->keyword as $keyword) {
    	$mods['subject'][$i]['authority'] = 'keyword';
    	$mods['subject'][$i]['topic'] = $keyword;
    	$i++;
    }    
    foreach((array)$record->keywords_plus->keyword as $keyword) {
    	$mods['subject'][$i]['authority'] = 'keyword';
    	$mods['subject'][$i]['topic'] = $keyword;
    	$i++;
    }
    
    $mods['genre'] = 'Journal Article';
    $mods['identifier_isi_loc'] = $record->ut;
    $mods['identifier_isbn'] = $record->isbn;

    $mods['relatedItem']['name'][0]['namePart_type'] = 'journal';
    $mods['relatedItem']['name'][0]['namePart'] = $source_title;  	    	
    $mods['relatedItem']['part']['detail_volume']['number'] = @$record->bib_issue->attributes()->vol;
    
    $mods['relatedItem']['originInfo']['dateIssued'] = $date_issued;
    
    preg_match('/\(([^\)]+)\):/', $record->bib_id, $matches);    	
    if(count($matches) == 2)
    	$mods['relatedItem']['part']['detail_issue']['number'] = $matches[1];
    	
    if($record->bib_pages) {
    	$pages = @split('-', $record->bib_pages);    	
    	if(count($pages) == 2) {
    		$mods['relatedItem']['part']['extent_page']['start'] = $pages[0];
    		$mods['relatedItem']['part']['extent_page']['end'] = $pages[1];
    	}
    }
    
    if($record->abstract->p) {
    	$abstract = (is_array($record->abstract->p)) ? implode('</p><p>', $record->abstract->p) : $record->abstract->p; 
    	$mods['abstract'] = htmlspecialchars('<p>'.$abstract.'</p>', ENT_QUOTES);
    }

    // Dublin Core
    $dc = array();
    $dc['title'] = $item_title;
    
    // FezMD
    $xdis_id = XSD_Display::getXDIS_IDByTitleVersion($mods['genre'], 'MODS 1.0');
    $fezmd['xdis_id'] = $xdis_id;
    $fezmd['sta_id'] = Status::getID("Published");
    $fezmd['ret_id'] = Object_Type::getID('Record');
    $fezmd['created_date'] = Date_API::getFedoraFormattedDateUTC();;
    $fezmd['updated_date'] = $fezmd['created_date'];
    $fezmd['depositor'] = Auth::getUserID();

    // RELS-EXT
    $rels['parent_pid'] = 'UQ:180159'; // TODO: grab from admin config'd value
    
    $pid = Fedora_API::getNextPID();
    $tpl = new Template_API();
    $tpl_file = "foxml.tpl.html";
    $tpl->setTemplate($tpl_file);
    $tpl->assign("mods", $mods);    
    $tpl->assign("dc", $dc);
    $tpl->assign("fezmd", $fezmd);
    $tpl->assign("rels", $rels);
    $tpl->assign("pid", $pid);
    $tpl->assign("title", $title);
    $foxml = $tpl->getTemplateContents();
    
    $xml_request_data = new DOMDocument();
   
    if(! @$xml_request_data->loadXML($foxml)) {
    	print $foxml;
    	exit;
    }
    
    $result = Fedora_API::callIngestObject($foxml);
    
    if($result) {
    	Record::setIndexMatchingFields($pid);
    	if(@$record->attributes()->timescited) {
    		Record::updateThomsonCitationCount($pid, $record->attributes()->timescited);
    	}
    	$historyDetail = 'Imported from ESTI Search Service download';
    	History::addHistory($pid, null, "", "", true, $historyDetail);
    }
}
