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


/**
 * Class to handle access to the ESTI Search Service which provides 
 * access to resources in ISI Web of Knowledge(SM) from Thomson Reuters(R).
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . 'nusoap.php');
include_once(APP_INC_PATH . "class.misc.php");

class EstiSearchService
{
	const WSDL = 'http://wok-ws.isiknowledge.com/esti/soap/SearchRetrieve?wsdl';
	
	// Fields not covered by our agreement: abstract, research_addrs(1), show_cited_refs
	// (1) research_addrs may be ok, but we do not require it anyway
	const FIELDS = 'times_cited abbrev_11 abbrev_22 abbrev_29 abbrev_iso author author authors bib_date bib_id bib_issue bib_misc bib_pagecount bib_pages bib_pages bib_vol bk_binding bk_ordering bk_prepay bk_price bk_publisher book_authors book_chapters book_corpauthor book_desc book_editor book_editors book_note book_notes book_series book_series_title book_subtitle bs_subtitle bs_title conf_city conf_date conf_end conf_host conf_id conf_location conf_sponsor conf_sponsors conf_start conf_state conf_title conference conferences copyright corp_authors doctype editions editor i_cid i_ckey ids io isbn issn issue_ed issue_title item item_enhancedtitle item_title items keyword keyword keywords keywords_plus languages load loc meeting_abstract p primaryauthor primarylang pub_address pub_city pub_url publisher pubtype ref refs reprint research reviewed_work rp_address rp_author rp_city rp_country rp_organization rp_state rp_street rp_suborganization rp_suborganizations rp_zip rp_zips rs_address rs_city rs_country rs_organization rs_state rs_street rs_suborganization rs_suborganizations rs_zip rs_zips rw_author rw_authors rw_lang rw_langs rw_year source_abbrev source_editors source_series source_title sq subject subjects ui unit units ut';
	
	const DATABASE_ID = 'WOS';
	
	function __construct()
	{
				
	}
	
	/**
	 * Retrieves records from an ISI Web of Knowledge resource.
	 *
	 * @param string $primary_keys 	The value of the primaryKey element in a record.  
	 * 								This value uniquely identifies the parent record.  
	 * 								For Web of Science (the default database) this value is in the <ut> element.
	 * @param string $database_id Identifies the ISI Web of Knowledge resource that this request will search (default is WOS)
	 * @param string $sort The sort order for record retrieval
	 * @param string $fields The fields that will be retrieved for each record.
	 * @return SimpleXMLElement The object containing records found in WoS matching the primaryKey(s) specified 
	 */
	public static function retrieve($primary_keys, $database_id = self::DATABASE_ID, $sort = '', $fields = self::FIELDS) 
	{		
		$log = FezLog::get();
		
		$client = new soapclient_internal(self::WSDL, true);
		$err = $client->getError();
		if ($err) {
			$log->err('Error occurred while creating new soap client: '.$err, __FILE__, __LINE__);
			return false;
		}
		$retrieve = array(
						'databaseID' => $database_id,
						'primaryKeys' => $primary_keys,
						'sort' => $sort,				
						'fields' => $fields
					);
		
		$result = $client->call('retrieve', $retrieve, '', '', false, true);
		
		if ($client->fault) {
			$log->err('Fault occurred while retrieving records from WoK: '.$client->fault, __FILE__, __LINE__);
			return false;
		} else {
			$err = $client->getError();
			if ($err) {
				$log->err('Error occurred while retrieving records from WoK: '.$err, __FILE__, __LINE__);
				return false;
			} else {
				return @simplexml_load_string($result);				
			}
		}
	}
	
	/**
	 * Performs a search and retrieves records from an ISI Web of Knowledge.
	 *
	 * @param string $database_id Identifies the ISI Web of Knowledge resource that this request will search (default is WOS).
	 * @param string $query The search expression in Advanced Search format. 
	 * @param string $depth The time span that this search will cover such as 1week, or 2000-2002.
	 * @param string $editions The editions that this search will cover.
	 * @param string $sort The sort order for record retrieval.
	 * @param string $first_rec The index of the first record's primary key this request will retrieve.  
	 * 							The index of the initial record in the search result is 1.
	 * @param string $num_recs The number of records this request will retrieve.  
	 * 							If the number of records in the search result is less than this value, 
	 * 							then this request will return a smaller number of records. 
	 * 							This value must not be greater than 100.	 
	 * @param string $fields The fields that will be retrieved for each record.
	 * @return SimpleXMLElement The object containing records found in WoS matching the primaryKey(s) specified. 
	 */
	public static function searchRetrieve($database_id, $query, $depth, $editions, $sort = '', $first_rec = 1, $num_recs = 100, $fields = self::FIELDS) 
	{		
		$log = FezLog::get();
					
		$client = new soapclient_internal(self::WSDL, true);
		$err = $client->getError();
		if ($err) {
			$log->err('Error occurred while creating new soap client: '.$err, __FILE__, __LINE__);
			return false;
		}
		$retrieve = array(
						'databaseID' => $database_id,
						'query' => $query,
						'depth' => $depth,
						'editions' => $editions,						
						'sort' => $sort,
						'firstRec' => $first_rec,
						'numRecs' => $num_recs,		
						'fields' => $fields
					);
		$result = $client->call('searchRetrieve', $retrieve, '', '', false, true);
		
		if ($client->fault) {
			$log->err('Fault occurred while retrieving records from WoK: '.$client->fault, __FILE__, __LINE__);
			return false;
		} else {
			$err = $client->getError();
			if ($err) {
				$log->err('Error occurred while retrieving records from WoK: '.$err, __FILE__, __LINE__);
				return false;
			} else {
				return $result;				
			}
		}
	}
	
	/**
	 * Retrieves meta-data for an ISI Web of Knowledge resource as an XML document.
	 *
	 * @param string $database_id Identifies the ISI Web of Knowledge resource that this request will search (default is WOS)	 
	 * @param string $format Identifies the format desired.  Recognized formats are: "catalog", "databases", "instnames", "loaddate" and "metadata".  
	 * @return SimpleXMLElement The meta-data XML
	 */
	public static function describe_database($database_id = self::DATABASE_ID, $format = 'instnames') 
	{		
		$log = FezLog::get();
					
		$client = new soapclient_internal(self::WSDL, true);
		
		$err = $client->getError();
		if ($err) {
			$log->err('Error occurred while creating new soap client: '.$err, __FILE__, __LINE__);
			return false;
		}	
		$describe = array(
						'databaseID' => $database_id,
						'format' => $format
					);
		$result = $client->call('describeDatabase', $describe, '', '', false, true);
		
		if ($client->fault) {
			$log->err('Fault occurred while retrieving meta-data from WoK: '.$client->fault, __FILE__, __LINE__);
			return false;
		} else {
			$err = $client->getError();
			if ($err) {
				$log->err('Error occurred while retrieving meta-data from WoK: '.$err, __FILE__, __LINE__);
				return false;
			} else {
				return @simplexml_load_string($result);				
			}
		}
	}
}
