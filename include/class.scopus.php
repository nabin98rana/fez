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
 * Class to handle access to the Scopus XAbstracts Metadata Service
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . 'nusoap.php');
include_once(APP_INC_PATH . "class.misc.php");

class Scopus
{
// Production Addresses

	const WSDL = 'http://services.elsevier.com/EWSXAbstractsMetadataWebSvc/XAbstractsMetadataServiceV8/WEB-INF/wsdl/absmet_service_v8.wsdl';
	const ENDPOINT = 'http://services.elsevier.com/EWSXAbstractsMetadataWebSvc/XAbstractsMetadataServiceV8';

// Development addresses	
//	const WSDL = 'http://cdc315-services.elsevier.com/EWSXAbstractsMetadataWebSvc/XAbstractsMetadataServiceV8/WEB-INF/wsdl/absmet_service_v8.wsdl';
//	const ENDPOINT = 'http://cdc315-services.elsevier.com/EWSXAbstractsMetadataWebSvc/XAbstractsMetadataServiceV8';
	
	function __construct()
	{
				
	}


	/**
	 * Method used to get the pid and scopus eid from the returned eid tagging table - only really meant for UQ consumption but might be interesting to some other Australian users.
	 *
	 * @access  public
	 * @return  array The list of languages
	 */
	function getReturnedEIDTaggingList($page = 0, $max = 0) {
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($page) || !is_numeric($max)) {
			return false;
		}
		$limit = "";
		if ($max != 0) {
			$limit = "LIMIT ".$max." OFFSET ".($page * $max);
		}

		$stmt = "
					SELECT
						uq_pid, sco_eid
					FROM
						era_eid_returned_results
					WHERE sco_eid != ''
					ORDER BY
						uq_pid ASC
		".$limit;
		
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}	

		
	/**
	 * Retrieve cited by count information for a list of articles
	 *
	 * @param string $input_keys	
	 * @return SimpleXMLElement The object containing records found in Scopus matching the input key(s) specified 
	 */
	public static function getCitedByCount($input_keys) 
	{		
		$log = FezLog::get();
		
		$client = new soapclient_internal(self::ENDPOINT, false);
		$client->soap_defencoding = 'US-ASCII';
		
		$err = $client->getError();
		if ($err) {
			$log->err('Error occurred while creating new soap client: '.$err, __FILE__, __LINE__);
			return false;
		}		
		$headers = '<EASIReq xmlns="http://webservices.elsevier.com/schemas/easi/headers/types/v1">
						<ReqId xmlns="">001</ReqId>
						<Ver xmlns="">2</Ver>
						<Consumer xmlns="">ESPACE</Consumer>
						<ConsumerClient xmlns="">tester_client</ConsumerClient>
						<OpaqueInfo xmlns="">prodId=1053;acctId=53745</OpaqueInfo>
						<LogLevel xmlns="">Default</LogLevel>
					</EASIReq>';
		
		$params = '<getCitedByCount xmlns="http://webservices.elsevier.com/schemas/metadata/abstracts/types/v8">
					<getCitedByCountReqPayload>
					 <dataResponseStyle>MESSAGE</dataResponseStyle>
					 <absMetSource>all</absMetSource>
					 <responseStyle>wellDefined</responseStyle>';		
		foreach($input_keys as $k => $v) {			
			$params .= '<inputKey><clientCRF xmlns="">'.$k.'</clientCRF>';
			foreach($v as $_k => $_v) {
				$params .= '<'.$_k.' xmlns="">'.$_v.'</'.$_k.'>';
			}
			$params .= '</inputKey>';
		}
		$params .= '</getCitedByCountReqPayload></getCitedByCount>';
		
		$result = $client->call('getCitedByCount', $params, null, null, $headers, null, 'document', 'encoded');
		
		if ($client->fault) {
			$log->err('Fault occurred while retrieving records from Scopus: '.$client->fault, __FILE__, __LINE__);
			return false;
		} else {
			$err = $client->getError();
			if ($err) {
				$log->err('Error occurred while retrieving records from Scopus: '.$err, __FILE__, __LINE__);
				return false;
			} else {				
				$cited_by_count = @$result['getCitedByCountRspPayload']['citedByCountList']['citedByCount'];
				if(is_array($cited_by_count)) {
					$return = array();
					if(array_key_exists('inputKey', $cited_by_count)) {	
						// Only 1 result					
						$key = $cited_by_count['inputKey']['clientCRF'];
						$return[$key] = array(
											'eid' => $cited_by_count['linkData']['eid'],
											'scopusID' => $cited_by_count['linkData']['scopusID'],
											'citedByCount' => $cited_by_count['linkData']['citedByCount']
										); 
					}
					else {
						foreach($cited_by_count as $cited) {
							$key = $cited['inputKey']['clientCRF'];
							$return[$key] = array(
												'eid' => $cited['linkData']['eid'],
												'scopusID' => $cited['linkData']['scopusID'],
												'citedByCount' => $cited['linkData']['citedByCount']
											); 
						}
					}
					return $return;
				}
				else {
					return false;
				}
			}
		}
	}
}