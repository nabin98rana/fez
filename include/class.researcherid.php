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
 * Class to handle ResearcherID batch uploads and downloads from the 
 * Thomson Reuters batch upload/download service.
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");

class ResearcherID
{  
  
   /**
     * Method used to request a ResearcherID download. 
     *
     * @access  public
     * @param   array  $ids An array of employee/researcher IDs to request data for.
     * @param   string $researchers_type The type of IDs being used. May be one of either 'researcherIDs' or 'employeeIDs'.
     * @return  string The job ticket number if the request is successful, otherwise false.
     */
    function downloadRequest($ids, $researchers_id_type, $researcher_id_type)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $ticket_number = null;
        
        // Validate params
        if(! is_array($ids)) {
            $log->err(array('First parameter for downloadRequest() requires an array containing researcher ids or employee ids', __FILE__, __LINE__));
            return false;
        }
        else if(! ($researchers_id_type == 'researcherIDs' || $researchers_id_type == 'employeeIDs')) {
            $log->err(array('Second parameter for downloadRequest() requires either "researcherIDs" or "employeeIDs", given "'.$researchers_type.'"', __FILE__, __LINE__));
            return false;
        } 
        else if(! ($researcher_id_type == 'researcherID' || $researcher_id_type == 'employeeID')) {
            $log->err(array('Third parameter for downloadRequest() requires either "researcherID" or "employeeID", given "'.$researchers_type.'"', __FILE__, __LINE__));
            return false;
        }        
        
        $tpl = new Template_API();
        $tpl_file = "researcher_download_request_data.tpl.html";
        $tpl->setTemplate($tpl_file);
        $tpl->assign("download_type", 'both');
        $tpl->assign("list", $ids);
        $tpl->assign("researchers_id_type", $researchers_id_type);
        $tpl->assign("researcher_id_type", $researcher_id_type);        
        $request_data = $tpl->getTemplateContents();
        
        $xml_request_data = new DOMDocument();
        $xml_request_data->loadXML($request_data);
        
        // Validate against schema
        if (! $xml_request_data->schemaValidate(RID_DL_SERVICE_REQUEST_XSD)) {
            // Not valid
           $log->err(array('XML request data does not validate against schema.', __FILE__, __LINE__));
           return false;
        }
        else {            
            $tpl = new Template_API();
            $tpl_file = "researcher_download_request.tpl.html";
            $tpl->setTemplate($tpl_file);
            $tpl->assign("type", 'UsernameAuth');
            $tpl->assign("product", 'Portal');
            $tpl->assign("username", RID_DL_SERVICE_USERNAME);
            $tpl->assign("password", RID_DL_SERVICE_PASSWORD);        
            $tpl->assign("get_product", 'RID');
            $tpl->assign("request_data", $request_data);
            $request = $tpl->getTemplateContents();    
            
            $xml_api_data_request = new DOMDocument();
            $xml_api_data_request->loadXML($request);
                
            // Do the service request
            $response_document = new DOMDocument();
            $response_document = ResearcherID::doServiceRequest($xml_api_data_request->saveXML());
            
            header('content-type: application/xml; charset='.RID_DL_SERVICE_CHARSET);
            echo $response_document->saveXML();
            
            if($response_document) {
                // Get job ticket number from response
                $xpath = new DOMXPath($response_document);
                $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc41');
                $query = "/rid:response/rid:fn[@name='AuthorResearch.downloadRIDData']/rid:val";
                $elements = $xpath->query($query);               
                if (!is_null($elements)) {
                    foreach ($elements as $element) {
                        $nodes = $element->childNodes;
                        foreach ($nodes as $node) {
                            $ticket_number = $node->nodeValue;
                        }
                    }
                }                
            }
            else {
                // Service request failed           
                return false;
            }  
        }
        
        if(is_null($ticket_number) || empty($ticket_number)) {
            $log->err(array('Failed to get a ticket number.', __FILE__, __LINE__));
            return false;
        }
        else {
            return ResearcherID::addJob($ticket_number, $xml_api_data_request->saveXML(), $response_document->saveXML());
        }
    }   
    
    
    /**
     * Method used to check on the status of all ResearcherID download request jobs currently not 'DONE'
     *
     * @access  public
     * @param   string $ticket_number The job ticket number of an existing download request job.
     * @return  string The current status of the job. 
     */
    function checkAllJobsStatus() 
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT
                    rij_ticketno
                 FROM
                    " . APP_TABLE_PREFIX . "rid_jobs
                 WHERE
                    rij_status <> 'DONE'";
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		foreach($res as $r) {
        	ResearcherID::checkJobStatus($r[0]);
        }
    }
    
    
    /**
     * Method used to check on the status of an existing ResearcherID download request job
     *
     * @access  public
     * @param   string $ticket_number The job ticket number of an existing download request job.
     * @return  string The current status of the job. 
     */
    function checkJobStatus($ticket_number) 
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $tpl = new Template_API();
        $tpl_file = "researcher_get_download_status.tpl.html";
        $tpl->setTemplate($tpl_file);
        $tpl->assign("ticket", $ticket_number);
        $request = $tpl->getTemplateContents();
        
        $xml_api_status_request = new DOMDocument();
        $xml_api_status_request->loadXML($request);
                
        // Do the service request
        $response_document = new DOMDocument();
        $response_document = ResearcherID::doServiceRequest($xml_api_status_request->saveXML());
        
        header('content-type: application/xml; charset='.RID_DL_SERVICE_CHARSET);
        echo $response_document->saveXML();
        
        // Get the donwload status from the response
        $job_status = null;
        if($response_document) {
            $xpath = new DOMXPath($response_document);
            $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc41');
            $query = "/rid:response/rid:fn[@name='AuthorResearch.getDownloadStatus']/rid:map/rid:val[@name='Status']";
            $elements = $xpath->query($query);              
            if (!is_null($elements)) {
                foreach ($elements as $element) {
                    $nodes = $element->childNodes;
                    foreach ($nodes as $node) {
                        $job_status = $node->nodeValue;
                    }
                }
            }
            if($job_status) {
                ResearcherID::updateJobStatus($ticket_number, $job_status, $response_document->saveXML());
            }
            else
                $log->err(array('No job status returned for ticket number: '.$ticket_number, __FILE__, __LINE__));    
        }
        else {
            // Service request failed
            $log->err(array('Failed to check job status for ticket number: '.$ticket_number, __FILE__, __LINE__));
        }
    }


    /**
     * Method used to add a job we want to check the status for.
     *
     * @access  public
     * @param   string $ticket_number The ticket number of the job to add
     * @return  boolean
     */
    function addJob($ticket_number, $request, $response)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "rid_jobs
                 (
                    rij_id,
                    rij_ticketno,
                    rij_lastcheck,
                    rij_status,
                    rij_count,
                    rij_downloadrequest,
                    rij_lastresponse,                    
                    rij_timestarted,
                    rij_timefinished
                 ) VALUES (
                    null,
                    " . $db->quote($ticket_number) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    'NEW',
                    1,
                    " . $db->quote($request) . ",
                    " . $db->quote($response) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    null                    
                 )";
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
		return true;
    }
    
    
    /**
     * Method used to update an existing job.
     *
     * @access  public
     * @param   string $ticket_number The ticket number of the job to update
     * @return  boolean
     */
    function updateJobStatus($ticket_number, $job_status, $response)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $finished = '';
        if($job_status == 'DONE')
            $finished = ", rij_timefinished = " . $db->quote(Date_API::getCurrentDateGMT());
            
        $stmt = "UPDATE 
                    " . APP_TABLE_PREFIX . "rid_jobs
                    SET 
                     rij_lastcheck = " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                     rij_status = " . $db->quote($job_status) . ",
                     rij_count = (SELECT rij_count FROM (SELECT * FROM " . APP_TABLE_PREFIX . "rid_jobs) AS x WHERE rij_ticketno = " . $db->quote($ticket_number) . ")+1 ".",
                     rij_lastresponse =  ". $db->quote($response) . 
                     $finished . "
                    WHERE 
                     rij_ticketno = " . $db->quote($ticket_number);
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return false;
		}
        
		return true;
    }
    
    
    /**
     * Method used to perform a service request
     *
     * @access  private
     * @param   string $post Data to POST to the service
     * @return  DOMDocument The XML returned by the service. 
     */
    function doServiceRequest($post_fields)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        // Do the service request
        $header[] = "Content-type: text/xml";			
        $ch = curl_init(RID_DL_SERVICE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
            $log->err(array(curl_error($ch)." ".RID_DL_SERVICE_URL, __FILE__, __LINE__));
            return false;
        } else {
            curl_close($ch);
            $response_document = new DOMDocument();
            $response_document->loadXML($response);  
            return $response_document;
        }
    }
}
