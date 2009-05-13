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
 * Class to handle requests to the Links Article Match Retrieve Service
 * from Thomson Reuters(R).
 *
 * @version 0.1
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class LinksAmrService
{
	const SERVICE_ENDPOINT = 'https://ws.isiknowledge.com/esti/xrpc';
	
	const COLLECTION = 'WOS';
	
	function __construct()
	{

	}

	/**
	 *
	 * @param array $list 	Named list comprising the data elements to be included in the response,
	 * 						and the articles for which you are requesting data
	 * @param string $collection 	The product code for the ISI Web of Knowledge collection to be
	 * 								searched for matching citations
	 * @return mixed
	 */
	public static function retrieve($list, $collection = self::COLLECTION)
	{
		//header('content-type: application/xml; charset=utf-8');

		$tpl = new Template_API();
		$tpl_file = "links_amr_retrieve.tpl.html";
		$tpl->setTemplate($tpl_file);
		$tpl->assign("collection", $collection);
		$tpl->assign("list", $list);
		$request_data = $tpl->getTemplateContents();

		$xml_api_data_request = new DOMDocument();
		$xml_api_data_request->loadXML($request_data);

		//echo $xml_api_data_request->saveXML();

		// Do the service request
		$response_document = new DOMDocument();
		$response_document = LinksAmrService::doServiceRequest($xml_api_data_request->saveXML());
		//$response_document->loadXML(file_get_contents('http://dev-repo.library.uq.edu.au/uqamartl/rid/lamr/01_response.xml'));

		return $response_document;		
	}

	/**
	 * Method used to perform a service request
	 *
	 * @access  private
	 * @param   string $post Data to POST to the service
	 * @return  string The XML returned by the service.
	 */
	private static function doServiceRequest($post_fields)
	{
		// Do the service request
		$header[] = "Content-type: text/xml";
		$ch = curl_init(self::SERVICE_ENDPOINT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		if (APP_HTTPS_CURL_CHECK_CERT == 'OFF')  {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			Error_Handler::logError(curl_error($ch)." ".self::SERVICE_ENDPOINT, __FILE__, __LINE__);
			return false;
		} else {
			curl_close($ch);
			$response_document = new DOMDocument();
            $response_document->loadXML($response);  
            return $response_document;
		}
	}
}