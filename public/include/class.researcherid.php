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
 * @version 2.0, March 2012
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 *
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.wok_queue.php");
include_once(APP_INC_PATH . "class.record_object.php");
include_once(APP_INC_PATH . "class.record_general.php");

class ResearcherID
{
  /**
   * Returns the full path to the file that keeps the process ID of the
   * running script.
   *
   * @return  string The full path of the process file
   */
  private static function getProcessFilename()
  {
    return APP_PATH . 'misc/check_researcherid_download_status.pid';
  }

  /**
   * Checks whether it is safe or not to run the check rid downlod/process
   * download script.
   *
   * @return  boolean
   */
  public static function isSafeToRun()
  {
    $pid = ResearcherID::getProcessID();
    $pid_file = ResearcherID::getProcessFilename();

    // Check for the process file, and also check that it has not been
    // orphaned by a previous script crash - this is based on the
    // assumption that if it was last modified over 24 hours ago the
    // previous script probably died
    if ($pid && (filemtime($pid_file) >= (time() - 86400))) {
      $safe_to_run = false;
    } else {
      // create the pid file and say it's safe to run
      $fp = @fopen($pid_file, 'w');
      @fwrite($fp, getmypid());
      @fclose($fp);
      $safe_to_run = true;
    }
    return $safe_to_run;
  }

  /**
   * Returns the process ID of the script from a file
   *
   * @param $pid_file The file containing the process ID
   *
   * @return  integer The process ID of the script
   */
  public static function getProcessID()
  {
    $pid = false;
    $pid_file = ResearcherID::getProcessFilename();

    if (@file_exists($pid_file)) {
      $pid = trim(implode('', file($pid_file)));
    }
    return $pid;
  }

  /**
   * Removes the process file to allow other instances of this script to run.
   *
   * @return  void
   */
  public static function removeProcessFile()
  {
    @unlink(ResearcherID::getProcessFilename());
  }

  /**
   * Method used to request a ResearcherID download.
   *
   * @access  public
   * @param   array  $ids              An array of employee/researcher IDs to
   *                                   request data for.
   * @param   string $researchers_type The type of IDs being used. May be one of
   *                                   either 'researcherIDs' or 'employeeIDs'.
   * @param   array $dateAddedFrom     year, month, day to download new publications from
   * @return  string                   The job ticket number if the request is
   *                                   successful, otherwise false.
   */
  public static function downloadRequest($ids, $researchers_id_type, $researcher_id_type, $dateAddedFrom = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $ticket_number = null;

    // Validate params
    if (! is_array($ids)) {
      $log->err(
          array('First parameter for downloadRequest() requires an array containing researcher ids or '.
          'employee ids', __FILE__, __LINE__)
      );
      return false;
    } else if (! ($researchers_id_type == 'researcherIDs' || $researchers_id_type == 'employeeIDs')) {
      $log->err(
          array('Second parameter for downloadRequest() requires either "researcherIDs" or "employeeIDs"'.
          ', given "'.$researcher_id_type.'"', __FILE__, __LINE__)
      );
      return false;
    } else if (! ($researcher_id_type == 'researcherID' || $researcher_id_type == 'employeeID')) {
      $log->err(
          array('Third parameter for downloadRequest() requires either "researcherID" or "employeeID"'.
          ', given "'.$researcher_id_type.'"', __FILE__, __LINE__)
      );
      return false;
    }

    $tpl = new Template_API();
    $tpl_file = "researcher_download_request_data.tpl.html";
    $tpl->setTemplate($tpl_file);
    $tpl->assign("download_type", 'both');
    $tpl->assign("list", $ids);
    $tpl->assign("researchers_id_type", $researchers_id_type);
    $tpl->assign("researcher_id_type", $researcher_id_type);
    $tpl->assign("dateAddedFrom", $dateAddedFrom);
    $request_data = $tpl->getTemplateContents();

    $xml_request_data = new DOMDocument();
    $xml_request_data->loadXML($request_data);

    // Validate against schema
    if (! $xml_request_data->schemaValidate(RID_DL_SERVICE_REQUEST_XSD)) {
      // Not valid
      $log->err(array('XML request data does not validate against schema.', __FILE__, __LINE__));
      return false;
    } else {
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
      $x = $xml_api_data_request->saveXML();
      $response_document = ResearcherID::doServiceRequest($xml_api_data_request->saveXML());
      $y = $response_document->saveXML();
      if ($response_document) {
        // Get job ticket number from response
        $xpath = new DOMXPath($response_document);
        $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc42');
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
      } else {
        // Service request failed
        return false;
      }
    }

    if (is_null($ticket_number) || empty($ticket_number)) {
      $log->err(array('Failed to get a ticket number: \n'.$x.'\n'.$y, __FILE__, __LINE__));
      return false;
    } else {
      return ResearcherID::addJob($ticket_number, $xml_api_data_request->saveXML(), $response_document->saveXML());
    }
  }


    /**
     * Method used to perform a ResearcherID profile upload.
     *
     * @access  public
     * @param   array  $aut_id The author ID to upload a profile for
     * @param   string  $alt_email An alternate email address to use in the registration process
     * @return  string The job ticket number if the request is successful, otherwise false.
     */
    public static function profileUpload($aut_id, $alt_email = '')
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $ticket_number = null;

        $list = Author::getListByAutIDList(0, 1, 'aut_lname', array($aut_id));
        if (!(is_array($list) && array_key_exists('list', $list) && is_array($list['list']))) {
            $log->err('Author not found');
            return false;
        }

        $tpl = new Template_API();
        $tpl_file = "researcher_profile_upload.tpl.html";
        $tpl->setTemplate($tpl_file);
        $alt_email = trim($alt_email);
        if (!empty($alt_email)) {
            $tpl->assign("rid_alt_email", $alt_email);
        }
        $tpl->assign("list", $list['list'][0]);
        $tpl->assign("app_admin_email", RID_UL_SERVICE_USERNAME);
        $tpl->assign("org_name", APP_ORG_NAME);
        $tpl->assign("email_append_note", RID_UL_SERVICE_EMAIL_APPEND_NOTE);
        $request_data = $tpl->getTemplateContents();

        $xml_request_data = new DOMDocument();
        $xml_request_data->loadXML($request_data);

        // Validate against schema
        if (!@$xml_request_data->schemaValidate(RID_UL_SERVICE_PROFILES_XSD)) {
            // Not valid
            $log->err(array('XML request data does not validate against schema.', __FILE__, __LINE__, $request_data));
            return false;
        }

        $tpl = new Template_API();
        $tpl_file = "researcher_upload_request.tpl.html";
        $tpl->setTemplate($tpl_file);
        $tpl->assign("type", 'Profile');
        $tpl->assign("username", RID_UL_SERVICE_USERNAME);
        $tpl->assign("password", RID_UL_SERVICE_PASSWORD);
        $tpl->assign("request_data", $request_data);
        $request = $tpl->getTemplateContents();

        $xml_api_data_request = new DOMDocument();
        $xml_api_data_request->loadXML($request);

        $response = ResearcherID::_callServiceRequest($xml_api_data_request);
        $responseAll = print_r($response,1);

        // Email user of any error occurence
        if (!$response['success']){
            ResearcherID::_notifyErrorToUser($responseAll, $list['list'][0]);
        }

        // Save all response to the database TABLE_PREFIXrid_registrations.rre_response
        ResearcherID::saveProfileUploadResponse($aut_id, $responseAll);
        return true;
    }


    /**
     * Send email to Author when there is an error with the RID profile upload.
     *
     * @param string $response Response of web service request.
     * @param array $author An array of author details.
     * @return boolean Always return true.
     */
    protected function _notifyErrorToUser($response = null, $author = null)
    {
        $emailBody = "There is an error occurred on your Author Registration to Researcher ID. <br />" . chr(10) .
                     "Author: " . $author['aut_fname'] . " " . $author['aut_lname'] . "<br />" . chr(10) .
                     "The response is: " . $response;

        // Send email to the queue
        $mail = new Mail_API;
        $mail->setTextBody(stripslashes($emailBody));

        $subject = "There is an error occurred on your Author Registration to Researcher ID.";
        $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;

        $to = Auth::getUserEmail();

        $mail->send($from, $to, $subject, false);

        return true;
    }



  /**
   * Method used to perform a ResearcherID profile upload.
   *
   * @access  public
   * @param   array  $ids An array of employee/researcher IDs to upload publications for.
   * @return  bool The job ticket number if the request is successful, otherwise false.
   */
  public static function publicationsUpload($ids)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    // Validate params
    if (! is_array($ids)) {
      $log->err('First parameter for publicationsUpload() requires an array containing author ids');
      return false;
    }

    foreach ($ids as $id) {
      $list = ResearcherID::listAllRecordsByAuthorID($id, '', 'Created Date', array(), true);

      if (count($list['list']) > 0) {
        $tpl = new Template_API();
        $tpl_file = "researcher_publications_upload.tpl.html";
        $tpl->setTemplate($tpl_file);
        $tpl->assign("list", $list['list']);
        $tpl->assign("app_admin_email", APP_ADMIN_EMAIL);
        $tpl->assign("org_name", APP_ORG_NAME);
        $tpl->assign("aut_org_username", Author::getOrgUsername($id));
        $request_data = $tpl->getTemplateContents();

        $xml_request_data = new DOMDocument();
        $xml_request_data->loadXML($request_data);

        // Validate against schema
        if (! @$xml_request_data->schemaValidate(RID_UL_SERVICE_PUBLICATIONS_XSD)) {
          // Not valid
          $log->err('XML request data does not validate against schema.');
          return false;
        } else {
          $tpl = new Template_API();
          $tpl_file = "researcher_upload_request.tpl.html";
          $tpl->setTemplate($tpl_file);
          $tpl->assign("type", 'Publication');
          $tpl->assign("username", RID_UL_SERVICE_USERNAME);
          $tpl->assign("password", RID_UL_SERVICE_PASSWORD);
          $tpl->assign("request_data", $request_data);
          $request = $tpl->getTemplateContents();

          $xml_api_data_request = new DOMDocument();
          $xml_api_data_request->loadXML($request);

          // Do the service request
          $response_document = new DOMDocument();
          $response_document = ResearcherID::doServiceRequest($xml_api_data_request->saveXML());

          if (! $response_document) {
            return false;
          } else {
            return true;
          }
        }
      } else {
        $log->err('No publications to upload for author '. $id);
        return false;
      }
    }
  }

    /**
     * Method used to process the status reports received via email from the upload service
     *
     * @access  public
     * @return  bool true or false in case of failure.
     */
    public static function processUploadStatusReportEmails()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $dir = RID_UL_SERVICE_ROUTED_EMAIL_PATH;
        $processed_dir = $dir . 'processed/';
        $emails = ResearcherID::getRoutedEmails($dir);

        // Create a processed directory if one doesn't already exist
        if (!is_dir($processed_dir)) {
            // create it..
            if (!mkdir($processed_dir, 0770)) {
                $log->err(array('Unable to create processed email directory ' . $processed_dir, __FILE__, __LINE__));
            }
            return false;
        }

        if (!is_array($emails)) {
            return false;
        }

        foreach ($emails as $email) {
            $full_message = file_get_contents($dir . '/' . $email);
            $email_date = date('Y-m-d H:i:s', filemtime($dir . '/' . $email));

            // join the Content-Type line (for easier parsing?)
            if (preg_match('/^boundary=/m', $full_message)) {
                $pattern = "#(Content-Type: multipart/.+); ?\r?\n(boundary=.*)$#im";
                $replacement = '$1; $2';
                $full_message = preg_replace($pattern, $replacement, $full_message);
            }

            // remove the reply-to: header
            if (preg_match('/^reply-to:.*/im', $full_message)) {
                $full_message = preg_replace("/^(reply-to:).*\n/im", '', $full_message, 1);
            }

            $structure = Mime_Helper::decode($full_message, true, true);

            // hack for clients that set more than one 'from' header
            if (is_array($structure->headers['from'])) {
                $structure->headers['from'] = $structure->headers['from'][0];
            }

            $to = $structure->headers['to'];
            $message_id = $structure->headers['message-id'];
            $from = $structure->headers['from'];
            $cc = $structure->headers['cc'];
            $subject = $structure->headers['subject'];
            $body = $structure->parts[0]->body;

            if ($subject == 'ResearcherID Batch Processing Status (completed)') {

                //Researcher ID update 1.5 (July 17th 2011) now has the report in a URL link instead of an attachment - CK
                $urlPattern = '/computer\.(.*)For easier/'; //TODO: change this regex to match something like http://ul.researcherid/blah which should be less volatile - CK
                $uniBody = str_replace("\n", "", $body); // make the body one line so it can be preg
                preg_match($urlPattern, $uniBody, $urlMatches);
                $url = trim($urlMatches[1]);

                $result = Misc::processURL($url, false, null, null, null, 600, true);

                // Save response retrieved from the URL on RID Status Report email
                $data = array("rpu_email_filename" => $email,
                              "rpu_email_file_date"=> $email_date,
                              "rpu_response_url"   => $url,
                              "rpu_response"       => $result['response'],
                              "rpu_response_info"  => print_r($result,1)
                             );
                $savedReportId = ResearcherID::saveProfileUploadReport($data);

                if ($result['success'] !== 1 || empty($result['response'])){
                    // Unsuccessful response :
                    // Do not move this email to "processed" directory, so we can re-process again.
                    // Continue with next email.
                    continue;
                }

                // Parse content of the URL - good response will be in XML format
                // Ok, we are using '@' sign to silent the PHP Warning in the case the string is not valid XML.
                $xml_report = @simplexml_load_string($result['response']);

                if (!isset($xml_report->profileList) && !isset($xml_report->publicationList)) {
                    // Invalid XML response :
                    // Do not move this email to "processed" directory, so we can re-process again.
                    // Continue with next email.
                    continue;
                }

                // Process profile list
                if (isset($xml_report->profileList)) {
                    ResearcherID::saveAuthorResearcherID($xml_report->profileList);
                    ResearcherID::saveProfileUploadStatusAndAuthor($xml_report->profileList, $savedReportId);
                }

                // Process publication list
                // There is no further processing on this based on existing codebase
                if (isset($xml_report->publicationList)) {
                    $publications = $xml_report->publicationList->{'successfully-uploaded'}->{'researcher-profile'};
                }

                // Move the email to 'processed' directory
                rename($dir . '/' . $email, $processed_dir . $email);


            // Move any other emails to "processed" directory
            } else {

                // Log to Fez error when unknown email is received
                // Except email with subject "ResearcherID Batch Processing Status"
                if ($subject != 'ResearcherID Batch Processing Status') {
                    $log->err('Received an unknown email ' . $email);
                }

                // Move the email to 'processed' directory
                rename($dir . '/' . $email, $processed_dir . $email);
            }
        }
        return true;
    }


    /**
     * Method used to check on the status of all ResearcherID download request jobs
     * currently are not 'DONE' and not 'EXPIRED'.
     *
     * @access  public
     * @param   string $ticket_number The job ticket number of an existing download request job.
     * @return  string The current status of the job.
     */
    public static function checkAllJobsStatus()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    rij_ticketno, rij_id, rij_timestarted
                 FROM
                    " . APP_TABLE_PREFIX . "rid_jobs
                 WHERE
                    rij_status NOT IN ('DONE', 'EXPIRED')";
        try {
            $res = $db->fetchAll($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        // For each non-expired rid job, check the download status from ResearcherID web service.
        // If job has expired, change the job status expired and ignore checking it with ResearcherID.
        foreach ($res as $r) {
            if (!ResearcherID::isJobExpired($r['rij_timestarted'])) {
                ResearcherID::checkJobStatus($r['rij_ticketno']);
            } else {
                ResearcherID::setJobToExpired($r);
            }
        }
    }


    /**
     * Indicates whether a job has/has not expired.
     *
     * @param array $job A rid_jobs record
     * @return boolean True if job has been expired, false otherwise.
     */
    protected static function isJobExpired($timeStarted)
    {
        $duration = 48 * 60 * 60; // 48 hours
        $expiredtime = date('Y-m-d H:i:s', strtotime($timeStarted) + $duration);

        if (date('Y-m-d H:i:s') >= $expiredtime) {
            return true;
        }
        return false;
    }


    /**
     * Sets expiry on a RID Job,
     * so it will be excluded from status checking to ResearcherID.
     *
     * @param array $job A rid_job record
     * @return boolean True when db record successfully updated, false otherwise.
     */
    protected static function setJobToExpired($job)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "UPDATE " . APP_TABLE_PREFIX . "rid_jobs " .
                " SET rij_status = 'EXPIRED' " .
                " WHERE rij_id = " . $db->quote($job['rij_id'], 'INTEGER');

        try {
            $db->exec($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return true;
    }


  /**
   * Method used to get a list of routed email file names
   *
   * @access  public
   * @return  mixed The array of routed email file names or false on error
   */
  private static function getRoutedEmails($dir)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $emails = array();

    if ($handle = opendir($dir)) {
      while (false !== ($file = readdir($handle))) {
        if (! (is_dir($dir.$file) || $file == '.' ||  $file == '..') )
        $emails[] = $file;
      }
      closedir($handle);
    } else {
      $log->err('Unable to open routed emails directory');
      return false;
    }

    return $emails;
  }

  /**
   * Method used to check on the status of an existing ResearcherID download request job
   *
   * @access  public
   * @param   string $ticket_number The job ticket number of an existing download request job.
   * @return  string The current status of the job.
   */
  public static function checkJobStatus($ticket_number)
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

    // Get the download status from the response
    $job_status = null;
    if ($response_document) {
      $xpath = new DOMXPath($response_document);
      $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc42');
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
      if ($job_status) {
        if ($job_status == 'DONE') {
          $responseXML = ResearcherID::processDownloadResponse($response_document);

          if ($responseXML !== false) {
            return ResearcherID::updateJobStatus($ticket_number, $job_status, $response_document->saveXML(), $responseXML);
          } else {
            return false;
          }
        } else {
          return ResearcherID::updateJobStatus($ticket_number, $job_status, $response_document->saveXML());
        }

      } else {
        $log->err('No job status returned for ticket number: '.$ticket_number);
        return false;
      }
    } else {
      // Service request failed
      $log->err('Failed to check job status for ticket number: '.$ticket_number);
      return false;
    }
  }

  /**
   * Processes the download response from a completed download request job.
   *
   * @access  public
   * @param   DOMDocument $response_document The response document to process
   * @return bool True if response processing is successful else false
   */
  private static function processDownloadResponse($response_document)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $xpath = new DOMXPath($response_document);
    $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc42');
    $download_response = '';
    $return = true;

    $query = "/rid:response/rid:fn[@name='AuthorResearch.getDownloadStatus']/rid:map/rid:val[@name='Response']";
    $elements = $xpath->query($query);
    if (!is_null($elements)) {
      foreach ($elements as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
          $download_response = $node->nodeValue;
        }
      }
    }
    if ($download_response) {
      $xml_dl_response = new SimpleXMLElement($download_response);
      foreach ($xml_dl_response->outputfile as $output_file) {
        $type = (string)$output_file->attributes()->type;
        $url = (string)$output_file->url;
        $result = false;

        switch($type) {
          case 'profile':
            $profileXML = ResearcherID::processDownloadedProfiles($url);
            $profileLink = $url;
            break;
          case 'publication':
            $publicationsXML = ResearcherID::processDownloadedPublications($url);
            $publicationsLink = $url;
            break;
        }

        if ($publicationsXML !== false && $profileXML !== false){
            $result = true;
        }

        $return = (! $return) ? false: $result; // ignore result if we have already had a previous fail
        // which will ensure this job is processed again
      }
    }

    if ($return === true){
        $return = array('profileXML' => $profileXML, 'profileLink' => $profileLink, 'publicationsXML' => $publicationsXML, 'publicationsLink' => $publicationsLink);
    }

    return $return;
  }

  /**
   * Processes the downloaded profiles.
   *
   * @access  public
   * @param   string $url The URL to retrieve the profiles data from
   * @return bool True if response processing is successful else false
   */
  private static function processDownloadedProfiles($url)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $urlData = Misc::processURL($url, false, null, null, null, 600);
    $profile = $urlData[0];
    if (!$profile) {
      $log->err("wasn't able to pull down RID Profile url $url:".print_r($urlData, true));
      return false;
    }

    return $profile;
  }

  /**
   * Processes the downloaded publications.
   *
   * @access  public
   * @param   string $url The URL to retrieve the publications data from
   * @return bool True if response processing is successful else false
   */
  private static function processDownloadedPublications($url)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $urlData = Misc::processURL($url, false, null, null, null, 600);
    $publications = $urlData[0];

    if (!$publications || $urlData[1]['http_code'] != 200) {
      $log->err("wasn't able to pull down RID url $url:".print_r($urlData, true));
      return false;
    }

    $xml_publications = new SimpleXMLElement($publications);

    $ticketNo = (string)$xml_publications->{'download-summary'}->{'request-token-no'};
    $date = ResearcherID::getUpdateTime($ticketNo);

    foreach ($xml_publications->publicationList->{'researcher-publications'} as $rp) {
      $researcherid = (string)$rp->researcherID;
      $author_id = null;
      $author_id = Author::getIDByResearcherID($researcherid);

      // Clear the temp password.
      // An attempt to download (regardless of the number of records downloaded)
      // indicates that the researcher has logged in to ResearcherID and completed the registration process,
      // which requires the temp password be changed.
      if ($author_id != null) {
        Author::setRIDPassword($researcherid, '');
      }

      if ($rp->records->count() > 0 && $author_id != null) {
        foreach ($rp->records->record as $record) {
          // Only add the publication if it is not a suggested publication - so only add confirmed pubs
          if (!isset($record->{'suggested-status'})) {
            ResearcherID::addPublication($record, $author_id, $researcherid);
            if (!empty($date)) {
                ResearcherID::authorUpdateTime($author_id, $date);
            }
          }
        }
      } else {
        $aut_details = Author::getDetails($author_id);
        $message = "FOUND no records for this RID download for ".$aut_details['aut_display_name']." with author id $author_id with Researcher ID ".$aut_details['aut_researcher_id']." <br />\n";
        $log->warn($message);
        echo $message;
      }
    }

    return $publications;
  }

  /**
   * Adds a downloaded publication to the repository
   *
   * @param The $record
   * @param int $author_id
   * @param string $researcherid Optionally specify which ResearcherID account the pub was downloaded from
   * @return bool
   */
  private static function addPublication($record, $author_id = false, $researcherid = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();

//    return TRUE; // TODO: disabled until wok queue finalised

    $collection = RID_DL_COLLECTION;

    if (Fedora_API::objectExists($collection)) {
      $aut = @preg_split('/:/', $record->{'accession-num'});
      // Download from WOS collection only
      if (count($aut) > 1 && ($aut[0] == 'WOS' || $aut[0] == 'ISI')) {
        $ut = $aut[1];
        //Only add this RID UT to the WokQueue if we don't already have a pid with this existing author id on it
        $existing_pid = Record::getPIDByIsiLoc($ut);
        $author_exists_already = false;
        if ($existing_pid != '') {
          // If the pid does exist, check if the author id is already assigned, if so skip adding this ut to the queue
          $pid_aut_ids = Record::getSearchKeyIndexValue($existing_pid, "Author ID", false);
          if (count($pid_aut_ids) > 0) {
            foreach($pid_aut_ids as $pid_aut_id) {
              if ($pid_aut_id == $author_id) {
                $author_exists_already = true;
              }
            }
          }
        }
        if ($author_exists_already == false) {
          WokQueue::get()->add($ut, $author_id);
        }
      }
      return true;
    } else {
      return false;
    }
  }

  /**
   * Method used to add a job we want to check the status for.
   *
   * @access  public
   * @param   string $ticket_number The ticket number of the job to add
   * @return  boolean
   */
  private static function addJob($ticket_number, $request, $response)
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
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }


  public static function stripBadge($xml) {
    // Load XML
    $response = DOMDocument::loadXML($xml);

    $items = $response->getElementsByTagName("researcherID-badge");
    foreach ($items as $item){
      $item->nodeValue = '';
    }
    return $response->saveXML();
  }

  /**
   * Method used to update XML content of an existing job.
   * it is used for existing DONE jobs that do not have XML content saved, due to updates on updateJobStatus() method.
   *
   * @access  public
   * @param   string $ticket_number The ticket number of the job to update
   * @return  boolean
   */
  public static function updateJobResponseXML($ticket_number, $responseXML = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmtUpdate = array();

    if (isset($responseXML['profileLink'])){
        $stmtUpdate[] = "rij_response_profilelink = " . $db->quote($responseXML['profileLink']);
    }
    if (isset($responseXML['profileXML'])){
        $responseXML['profileXML'] = ResearcherID::stripBadge($responseXML['profileXML']);
        $stmtUpdate[] = "rij_response_profilexml = " . $db->quote($responseXML['profileXML']);
    }
    if (isset($responseXML['publicationsLink'])){
        $stmtUpdate[] = "rij_response_publicationslink = " . $db->quote($responseXML['publicationsLink']);
    }
    if (isset($responseXML['publicationsXML'])){
        $responseXML['publicationsXML'] = ResearcherID::stripBadge($responseXML['publicationsXML']);
        $stmtUpdate[] = "rij_response_publicationsxml = " . $db->quote($responseXML['publicationsXML']);
    }

    if (sizeof($stmtUpdate)==0 ){
        return false;
    }

    $stmt = " UPDATE
                    " . APP_TABLE_PREFIX . "rid_jobs
              SET
                    " . implode(", ", $stmtUpdate) .
            " WHERE
                     rij_ticketno = " . $db->quote($ticket_number);
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
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
  private static function updateJobStatus($ticket_number, $job_status, $response, $responseXML = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $finished = '';
    if ($job_status == 'DONE') {
      $finished = ", rij_timefinished = " . $db->quote(Date_API::getCurrentDateGMT());
    }
    $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "rid_jobs
                    SET
                     rij_lastcheck = " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                     rij_status = " . $db->quote($job_status) . ",
                     rij_count = (SELECT rij_count FROM (SELECT * FROM " . APP_TABLE_PREFIX .
                     "rid_jobs) AS x WHERE rij_ticketno = " . $db->quote($ticket_number) . ")+1 ".",
                     rij_lastresponse =  ". $db->quote($response) .
                     $finished . " ";

    if (isset($responseXML['profileLink'])){
        $stmt .= ", rij_response_profilelink = " . $db->quote($responseXML['profileLink']);
    }
    if (isset($responseXML['profileXML'])){
        $stmt .= ", rij_response_profilexml = " . $db->quote($responseXML['profileXML']);
    }
    if (isset($responseXML['publicationsLink'])){
        $stmt .= ", rij_response_publicationslink = " . $db->quote($responseXML['publicationsLink']);
    }
    if (isset($responseXML['publicationsXML'])){
        $stmt .= ", rij_response_publicationsxml = " . $db->quote($responseXML['publicationsXML']);
    }

    $stmt .= " WHERE
                     rij_ticketno = " . $db->quote($ticket_number);
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return true;
  }

  /**
   * Method used to remove an existing job.
   *
   * @access  public
   * @param   string $ticket_number The ticket number of the job to remove
   * @return  boolean
   */
  public static function removeJob($ticket_number)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "DELETE FROM " . APP_TABLE_PREFIX .
              "rid_jobs WHERE rij_ticketno = ".
              $db->quote($ticket_number);
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return true;
  }


    /**
     * Saves the response received from ResearcherID Profile Upload service request.
     * Data saved on this method are: Author ID, XML response, timestamp of insert query.
     *
     * @param int $aut_id Author ID
     * @param DOMDocument $response_document XML response of RID the web service request.
     * @return boolean True when query is successful, otherwise returns False.
     */
    public function saveProfileUploadResponse($aut_id = 0, $response_document = null)
    {
        if (is_null($response_document) || empty($response_document)) {
            return false;
        }

        $log = FezLog::get();
        $db = DB_API::get();


        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "rid_registrations
                    (rre_aut_id, rre_response, rre_created_date, rre_updated_date)
                 VALUES
                    (". $db->quote($aut_id, 'INT') .", ".
                        $db->quote($response_document, 'STRING')  . ", ".
                        $db->quote(Date_API::getCurrentDateGMT()) . ", ".
                        $db->quote(Date_API::getCurrentDateGMT()) .
                    ")";

        try {
          $res = $db->query($stmt);
        }
        catch(Exception $ex) {
          $log->err($ex);
          return false;
        }

        return true;
    }


    /**
     * Saves email content of the ResearcherID Upload Status Report and
     * the content retrieved from the URL provided on the email.
     * Updates the record when id exists, otherwise inserts as a new record.
     *
     * @param array $data An array of the data to be saved on to rid_profile_uploads table
     * @param int $id Primary key of a rid_profile_uploads record
     *
     * @return boolean True when query is successful, otherwise returns False.
     */
    public function saveProfileUploadReport($data = array(), $id = null)
    {
        if (!is_array($data) && !is_object($data)){
            return false;
        }

        if (!empty($id)){
            return ResearcherID::_updateProfileUploadReport($data, $id);

        } else {
            return ResearcherID::_insertProfileUploadReport($data);
        }
    }


    /**
     * Inserts a new Profile Upload record.
     *
     * @param array $data
     * @return boolean | int Returns MySQL last saved ID when insert query is successful.
     */
    protected static function _insertProfileUploadReport($data = array())
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $values = array();
        $fields = array();
        foreach ($data as $key => $value){
            switch ($key){
                case "rpu_email_filename":
                case "rpu_response_url":
                case "rpu_response_status":
                case "rpu_aut_org_username":
                    $values[] = $db->quote($value, "STRING");
                    $fields[] = $key;
                    break;

                case "rpu_email_file_date":
                    $values[] = $db->quote($value, "DATE");
                    $fields[] = $key;
                    break;

                case "rpu_response":
                case "rpu_response_info":
                    $values[] = $db->quote($value, "BLOB");
                    $fields[] = $key;
                    break;
            }
        }
        $fields[] = "rpu_created_date";
        $values[] = $db->quote(Date_API::getCurrentDateGMT());
        $fields[] = "rpu_updated_date";
        $values[] = $db->quote(Date_API::getCurrentDateGMT());

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "rid_profile_uploads ".
                " (" . implode(",", $fields) . ") ".
                " VALUES (". implode(",", $values) . "); ";

        try {
            $res = $db->exec($stmt);
            return $db->lastInsertId();
        }
        catch(Exception $ex) {
            $log->err($ex);
        }
        return false;
    }


    /**
     * Updates a Profile Upload record as specified by $id
     *
     * @param array $data
     * @param int $id Primary key of a rid_profile_uploads record
     *
     * @return boolean
     */
    protected static function _updateProfileUploadReport($data = array(), $id = null)
    {
        if (empty($id)){
            return false;
        }

        $db  = DB_API::get();
        $log = FezLog::get();

        $values = array();
        foreach ($data as $key => $value){
            switch ($key){
                case "rpu_email_filename":
                case "rpu_response_url":
                case "rpu_response_status":
                case "rpu_aut_org_username":
                    $values[] = $key . " = " . $db->quote($value, "STRING");
                    break;

                case "rpu_email_file_date":
                    $values[] = $key . " = " . $db->quote($value, "DATE");
                    break;

                case "rpu_response":
                case "rpu_response_info":
                    $values[] = $key . " = " . $db->quote($value, "BLOB");
                    break;
            }
        }
        $values[] = "rpu_updated_date = " . $db->quote(Date_API::getCurrentDateGMT());

        $stmt = "UPDATE " . APP_TABLE_PREFIX . "rid_profile_uploads ".
                " SET " . implode(",", $values) .
                " WHERE rpu_id = " . $db->quote($id, "INTEGER");

        try {
            $res = $db->exec($stmt);
            return true;
        }
        catch(Exception $ex) {
            $log->err($ex);
        }
        return false;
    }


    /**
     * Retrieves ResearcherID or error message from the XML response and saves it to related Author record.
     *
     * @param SimpleXMLElement $profileList XML response from RID Profile Upload
     * @return boolean
     */
    public static function saveAuthorResearcherID($profileList = null)
    {
        $profiles = $profileList->{'successfully-uploaded'}->{'researcher-profile'};
        foreach ($profiles as $profile) {
            Author::setResearcherIdByRidProfile($profile);
        }

        $profiles = $profileList->{'existing-researchers'}->{'researcher-profile'};
        foreach ($profiles as $profile) {
            Author::setResearcherIdByOrgUsername((string) $profile->employeeID, (string) $profile->researcherID);
        }

        $profiles = $profileList->{'failed-to-upload'}->{'researcher-profile'};
        foreach ($profiles as $profile) {
            if (!(empty($profile->employeeID) || empty($profile->researcherID))) {
                Author::setResearcherIdByOrgUsername((string) $profile->employeeID, (string) $profile->researcherID);
            }
        }

        return true;
    }


    /**
     * Retrieves status(es) & author(s) username from the XML response and saves them onto RID Profile Upload table.
     *
     * @param SimpleXMLElement $profileList XML response from RID Profile Upload
     * @param int $reportId
     * @return boolean
     */
    public static function saveProfileUploadStatusAndAuthor($profileList = null, $reportId = null)
    {

        $author = array();
        $status = array();

        $types = ResearcherID::_getProfileUploadSummaryTypes();

        foreach ($types as $type){
            $result = ResearcherID::_getProfileUploadResult($profileList, $type);
            if ($result){
                $status[] = $result['status'];
                $author[] = $result['author'];
            }
        }

        $data = array("rpu_aut_org_username" => implode(", ", $author),
                      "rpu_response_status"  => implode(", ", $status)
                     );
        ResearcherID::saveProfileUploadReport($data, $reportId);

        return true;
    }


    /**
     * Returns the author and status related to each type of summary report.
     * Returns false when there is no summary report for requested type.
     *
     * Note: the RID XML response is formatted to accommodate multiple authors at one request,
     * that is why the $profiles are in array.
     * Atm on Fez, we are sending one author profile upload at a time.
     * This method accommodates responses for single and/or multiple authors request.
     *
     * @param SimpleXMLElement $profileList XML content from RID
     * @param string $type Type of summary
     * Values are: 'successfully-uploaded' | 'failed-to-upload' | 'existing-researchers'
     * @return boolean | array
     */
    protected static function _getProfileUploadResult($profileList = null, $type = 'successfully-uploaded')
    {

        // no report on this type
        if ($profileList->{'upload-summary'}->$type == 0){
            return false;
        }

        // Get author profiles
        $profiles = $profileList->$type->{'researcher-profile'};

        foreach ($profiles as $profile) {
            if (isset($profile->employeeID) && !empty($profile->employeeID)){
                $authors[]  = $profile->employeeID;
            }
        }
        $author = implode(",", $authors);

        $status = str_replace("-", " ", ucfirst($type));
        if (sizeof($authors) > 1){
            $status .= ": " . implode(",", $authors);
        }

        $result = array('status' => $status, 'author' => $author);
        return $result;
    }


    /**
     * Returns an array of summary types expected from Profile Upload report.
     * @return array
     */
    protected static function _getProfileUploadSummaryTypes()
    {
        $types = array('successfully-uploaded', 'failed-to-upload', 'existing-researchers');
        return $types;
    }


    /**
     * Retrieves Profile & Publication links from XML responses of RID Download Requests.
     *
     * @param array $jobs Array of download requests jobs
     * @return array Array of jobs with profile & publication links
     */
    public static function getLinksFromLastResponse($jobs)
    {
        if (!is_array($jobs)) {
            return false;
        }

        foreach ($jobs as $key => $job) {
            if (empty($job['rij_lastresponse']) || is_null($job['rij_lastresponse'])) {
                continue;
            }

            // Load XML from rij_lastresponse
            $response = DOMDocument::loadXML($job['rij_lastresponse']);

            // Get the XML Node with Response attribute
            $xpath = new DOMXPath($response);
            $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc42');
            $query = "/rid:response/rid:fn[@name='AuthorResearch.getDownloadStatus']/rid:map/rid:val[@name='Response']";
            $elements = $xpath->query($query);

            if (is_null($elements)) {
                continue;
            }

            foreach ($elements as $element) {
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                  $download_response = $node->nodeValue;
                }
            }

            if (is_null($download_response) || empty($download_response)){
                continue;
            }

            // Load XML from the Response which is in string CDATA format,
            // and get the URLs contain in the response XML
            $xml_dl_response = new SimpleXMLElement($download_response);
            foreach ($xml_dl_response->outputfile as $output_file) {
                $type = $output_file->attributes()->type;
                $url = $output_file->url;
                $result = false;

                switch($type) {
                  case 'profile':
                      $jobs[$key]['profilelink'] = $url->saveXML();
                      break;
                  case 'publication':
                      $jobs[$key]['publicationslink'] = $url->saveXML();
                      break;
                }
            }
        }
        return $jobs;
    }


    /**
     * Delete the Profile & Publications XML Content from RID jobs that are older than a specific period of time.
     *
     * @return boolean True when query is successful, false otherwise.
     */
    public static function cleanJobsXMLContent()
    {
        $db = DB_API::get();
        $log = FezLog::get();

        // Time range = 14 days ago
        $timerange = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-14, date('Y')));
        $search = array('key'=>'clean_rid_jobs', 'val' => $timerange);

        $sort = array('by' => 'rij_timefinished', 'order' => 'desc');
        $max = 500; // This number should be reasonable large

        // Get the records that have xml content AND older than 2 weeks
        $jobs = ResearcherID::getJobs(0, $max, $sort, $search);

        // Save date on GMT timezone
        $timecleaned = Date_API::getCurrentDateGMT();

        $clean_stmt = "";

        foreach ($jobs['list'] as $job){
            if (!isset($job['rij_id']) || empty($job['rij_id'])){
                continue;
            }
            // Query to empty XML content
            $clean_stmt[] = "UPDATE " . APP_TABLE_PREFIX . "rid_jobs
                                SET rij_response_profilexml = '',
                                    rij_response_publicationsxml = '',
                                    rij_time_xmlcleaned = ". $db->quote($timecleaned, 'DATE') ."
                                WHERE rij_id = ". $db->quote($job['rij_id'], 'INTEGER').";";
        }

        $stmt = implode(" ", $clean_stmt);

        try {
            $db->exec($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return true;
    }


    /**
     * Generate the WHERE statement for get RID Jobs query.
     *
     * @param array $search The search key & value pairs.
     * @return array Generated WHERE statement
     */
    private static function buildJobsQueryFilter($search = null)
    {
        $db = DB_API::get();

        $where_stmt = array();
        if (is_null($search) || !isset($search['key']) || !isset($search['val'])) {
            return $where_stmt;
        }

        switch ($search['key']){
            // Special filtering: filter based on Profile & Publications XML content.
            case 'rid':
                $where_stmt[] = "(
                                 rij_response_profilexml LIKE ".$db->quote('%<researcherID>'. $search['val'] .'%').
                                 " OR ".
                                 " rij_response_publicationsxml LIKE ".$db->quote('%<researcherID>'. $search['val'] .'%').
                                ")";

                break;

            // Special filtering: filter based on XML content.
            case 'xml':
                $where_stmt[] = "(
                                 rij_downloadrequest LIKE ".$db->quote('%'.$search['val'].'%').
                    " OR ".
                    " rij_lastresponse LIKE ".$db->quote('%'.$search['val'].'%').
                    " OR ".
                    " rij_response_profilexml LIKE ".$db->quote('%'.$search['val'].'%').
                    " OR ".
                    " rij_response_publicationsxml LIKE ".$db->quote('%'.$search['val'].'%').
                    ")";

                break;
            // Special filtering: filter jobs that store Profile & Publications response XML and older than a specific period of time.
            case 'clean_rid_jobs':
                $where_stmt[] = " rij_timefinished >= ". $db->quote($search['val'], 'DATE') ." AND
                                  ( rij_response_profilexml IS NOT NULL OR
                                    rij_response_publicationsxml IS NOT NULL
                                  )";
                break;
            default:
                $where_stmt[] = $db->quoteIdentifier($search['key']) . " = " . $db->quote($search['val'], 'STRING') . " ";
        }
        return $where_stmt;
    }


    /**
     * Method used to get ResearcherID download request jobs from database,
     * with sort by & record filtering set by parameter, if any
     *
     * @param int $current_row Current page
     * @param int $max Maximum number of records per page
     * @param array $sort Array of sort by and sort order
     * @param array $search Array of search key & value
     * @return array|string Array of records or empty string when db error occur.
     */
    public static function getJobs($current_row = 0, $max = 25, $sort = null, $search = null)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        // Where statement
        $where_operator = " AND ";
        $where_stmt = ResearcherID::buildJobsQueryFilter($search);

        // Sort statement
        $sort_stmt = "";
        if (!is_null($sort)) {
            $sort_stmt .= " ORDER BY " . $db->quoteIdentifier($sort['by']) . " " . ( stristr($sort['order'], 'DESC') ? 'DESC' : 'ASC') . " ";
        }

        // Limit offset
        $start = $current_row * $max;

        // The query statement
        $stmt = "SELECT
                    SQL_CALC_FOUND_ROWS  rij_id, rij_ticketno, rij_lastcheck, rij_status, rij_count, rij_timestarted, rij_timefinished
					rij_downloadrequest, rij_lastresponse, rij_response_profilelink, 'See DB, too big to show' as rij_response_profilexml,
					rij_response_publicationslink, 'See DB, too big to show' as rij_response_publicationsxml, rij_time_xmlcleaned
                 FROM
                    " . APP_TABLE_PREFIX . "rid_jobs ";

        if ( is_array($where_stmt) && sizeof($where_stmt)>0 ){
            $stmt .= " WHERE " . implode($where_operator, $where_stmt);
        }

        $stmt .= " " . $sort_stmt . "
                 LIMIT " . $db->quote($max, 'INTEGER') . " OFFSET " . $db->quote($start, 'INTEGER');

        try {
            $res = $db->fetchAll($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
            $stmt = "SELECT COUNT(rij_id)
                 FROM
                    " . APP_TABLE_PREFIX . "rid_jobs
                " . $where_stmt;
        } else {
            $stmt = 'SELECT FOUND_ROWS()';
        }

        try {
            $total_rows = $db->fetchOne($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        // Paging variables
        if (($start + $max) < $total_rows) {
            $total_rows_limit = $start + $max;
        } else {
            $total_rows_limit = $total_rows;
        }
        $total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;


        // Retrieve links from lastResponse XML
        $res = ResearcherID::getLinksFromLastResponse($res);

        // Convert value of datetime/timestamp fields with user's preferred timezone.
        // Note: datetime/timestamp fields are saved on GMT timezone.
        $timezone = Date_API::getPreferredTimezone();
        foreach ($res as $key => $row) {
            $res[$key] = ResearcherID::setJobFormattedDates($res[$key], $timezone);
        }

        // Format return output
        $output = array(
            "list" => $res,
            "list_info" => array(
                "current_page" => $current_row,
                "start_offset" => $start,
                "end_offset" => $total_rows_limit,
                "total_rows" => $total_rows,
                "total_pages" => $total_pages,
                "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page" => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page" => $last_page
            )
        );

        return $output;
    }


    /**
     * Formats datetime/timestamp value of a RID Job with user's preferred timezone.
     *
     * @param array $job A rid_job record
     * @param string $timezone
     * @return array An array of formatted datetime/timestamp.
     */
    protected static function setJobFormattedDates($job, $timezone)
    {
        $job["rij_lastcheck_formatted"] = "";
        $job["rij_timestarted_formatted"] = "";
        $job["rij_timefinished_formatted"] = "";
        $job["rij_time_xmlcleaned_formatted"] = "";

        if (!empty($job["rij_lastcheck"])){
            $job["rij_lastcheck_formatted"] = Date_API::getFormattedDate($job["rij_lastcheck"], $timezone);
        }
        if (!empty($job["rij_timestarted"])){
            $job["rij_timestarted_formatted"] = Date_API::getFormattedDate($job["rij_timestarted"], $timezone);
        }
        if (!empty($job["rij_timefinished"])){
            $job["rij_timefinished_formatted"] = Date_API::getFormattedDate($job["rij_timefinished"], $timezone);
        }
        if (!empty($job["rij_time_xmlcleaned"])){
            $job["rij_time_xmlcleaned_formatted"] = Date_API::getFormattedDate($job["rij_time_xmlcleaned"], $timezone);
        }
        return $job;
    }



    /**
     * Method used to get ResearcherID profile uploads responses.
     *
     * @param int $current_row Current page
     * @param int $max Maximum number of records per page
     * @param array $sort Array of sort by and sort order
     * @param array $search Array of search key & value
     * @return array|string Array of records or empty string when db error occur.
     */
    public static function getProfileUploads($current_row = 0, $max = 25, $sort = null, $search = null)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $where_stmt = "";
        if (!is_null($search) && isset($search['key']) && isset($search['val'])) {
            $where_stmt .= " WHERE " . $db->quoteIdentifier($search['key']) . " = " . $db->quote($search['val'], 'STRING') . " ";
        }

        $sort_stmt = "";
        if (!is_null($sort)) {
            $sort_stmt .= " ORDER BY " . $db->quoteIdentifier($sort['by']) . " " . ( stristr($sort['order'], 'DESC') ? 'DESC' : 'ASC') . " ";
        }

        $start = $current_row * $max;

        $stmt = "SELECT
                SQL_CALC_FOUND_ROWS  *
             FROM
                " . APP_TABLE_PREFIX . "rid_profile_uploads
            " . $where_stmt . "
            " . $sort_stmt . "
             LIMIT " . $db->quote($max, 'INTEGER') . " OFFSET " . $db->quote($start, 'INTEGER');

        try {
            $res = $db->fetchAll($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
            $stmt = "SELECT COUNT(*)
                 FROM
                    " . APP_TABLE_PREFIX . "rid_profile_uploads
                " . $where_stmt;
        } else {
            $stmt = 'SELECT FOUND_ROWS()';
        }

        try {
            $total_rows = $db->fetchOne($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        // Paging variables
        if (($start + $max) < $total_rows) {
            $total_rows_limit = $start + $max;
        } else {
            $total_rows_limit = $total_rows;
        }
        $total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;

        // Get formatted date value for datetime/timestamp fields with user's preferred timezone.
        // Note: datetime/timestamp fields are saved on GMT timezone.
        $timezone = Date_API::getPreferredTimezone();
        foreach ($res as $key => $row) {
            $res[$key]["rpu_created_date_formatted"] = Date_API::getFormattedDate($res[$key]["rpu_created_date"], $timezone);
            $res[$key]["rpu_updated_date_formatted"] = Date_API::getFormattedDate($res[$key]["rpu_updated_date"], $timezone);
        }

        // Format return output
        $output = array(
            "list" => $res,
            "list_info" => array(
                "current_page" => $current_row,
                "start_offset" => $start,
                "end_offset" => $total_rows_limit,
                "total_rows" => $total_rows,
                "total_pages" => $total_pages,
                "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page" => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page" => $last_page
            )
        );

        return $output;
    }


  /**
   * Method used to perform a service request
   *
   * @access  private
   * @param   string $post Data to POST to the service
   * @return  DOMDocument The XML returned by the service.
   */
  private static function doServiceRequest($post_fields)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    // Do the service request
    $header[] = "Content-type: text/xml";
    $ch = curl_init(RID_DL_SERVICE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);
    if (APP_HTTPS_CURL_CHECK_CERT == 'OFF') {
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


    /**
     * Method used to perform request to Researcher ID web service using cURL.
     * This is a similar method from the doServiceRequest with debugging information.
     *
     * @param string $xmlData Data to POST to the service
     * @return array An array containing full information of the transfer and success status.
     */
    private static function _callServiceRequest($xmlData)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $result = array();

        // Do the service request
        $header[] = "Content-type: text/xml";
        $ch = curl_init(RID_DL_SERVICE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (APP_HTTPS_CURL_CHECK_CERT == 'OFF') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData->saveXML());


        // Gather return data: success status, response of the transfer, info of the transfer and cURL error number
        $result['success']      = 1;
        $result['response']     = curl_exec($ch);
        $result['curl_info']    = curl_getinfo($ch);
        $result['curl_error']   = curl_errno($ch);

        // Hide password
        $items = $xmlData->getElementsByTagName("val");
        foreach ($items as $item){
            $name = $item->getAttribute('name');
            if ($name == 'Password'){
                $item->nodeValue = "{password_was_here}";
            }
        }
        $result['requestdata_to_rid'] = $xmlData->saveXML();

        // Check if the HTTP code started with 4 or 5, which indicates error.
        $pattern = "/^4|^5/";
        $errorHttpCode = preg_match($pattern, $result['curl_info']['http_code']);

        // Set failed status condition
        if (empty($result['response']) || $result['curl_error'] || $errorHttpCode == 1 ){
            $result['success'] = 0;
            $log->err('There is an error occurred on Researcher ID service request. The response is: ' . print_r($result,1));
        }

        // Close cURL session
        curl_close($ch);

        return $result;
    }


  /**
   * Method used to get the list of records publicly available in the
   * system.
   *
   * @access  public
   * @param   string $aut_id Author ID.
   * @param   string $set oai set collection (optional).
   * @param   integer $current_row The point in the returned results to start from.
   * @param   integer $max The maximum number of records to return
   * @param   bool $requireIsiLoc If set to true, only records with an Isi Loc will be returned
   * @return  array The list of records
   */
  private static function listAllRecordsByAuthorID(
      $aut_id, $identifier="", $order_by = 'Created Date', $filter=array(), $requireIsiLoc = false
  )
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $options = array();
    $max = 9999999;
    $current_row = 0;
    $return['list'] =array();

    $filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
    $filter["searchKey".Search_Key::getID("Author ID")] = $aut_id;
    $filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only

    // .. which are either journal articles and conference papers ..
    $filter["searchKey".Search_Key::getID("Display Type")] = array();
    $filter["searchKey".Search_Key::getID("Display Type")]['override_op'] = 'OR';
    $filter["searchKey".Search_Key::getID("Display Type")][] =
        XSD_Display::getXDIS_IDByTitleVersion('Journal Article', 'MODS 1.0');
    $filter["searchKey".Search_Key::getID("Display Type")][] =
        XSD_Display::getXDIS_IDByTitleVersion('Conference Paper', 'MODS 1.0');


    if ($requireIsiLoc) {
      $filter["manualFilter"] = " (isi_loc_t_s:[* TO *]) "; // require Isi Loc
    }

    $listing = Record::getListing($options, array(9, 10), $current_row, $max, $order_by, false, false, $filter);

    if (is_array($listing['list'])) {
      for ($i=0; $i<count($listing['list']); $i++) {
        $record = $listing['list'][$i];

        // Journal articles
        if ($record['rek_display_type'] == 179) {
          $record['rek_ref_type'] = 17;
        } elseif ($record['rek_display_type'] == 130) { //conference papers
          $record['rek_ref_type'] = 10;
        } else {
          $record['rek_ref_type'] = '';
        }

        if ( is_array($record['rek_isi_loc']) ) {
          $record['rek_isi_loc'] = $record['rek_isi_loc'][0];
        }

        // Replace double quotes with double double quotes
        if ( !empty($record['rek_title']) ) {
          $record['rek_title'] = str_replace('"', '""', $record['rek_title']);
        }

        // Set the secondary title from the book title if one exists
        if ( !empty($record['rek_book_title']) ) {
          $record['rek_secondary_title'] = $record['rek_book_title'];
        }

        // Set the secondary title from the journal name if one exists
        if ( !empty($record['rek_journal_name']) && $record['rek_display_type'] == 179 ) {
          $record['rek_secondary_title'] = $record['rek_journal_name'];
        }

        // Set the secondary title from the conference title if one exists
        if ( !empty($record['rek_conference_name']) && $record['rek_display_type'] == 130 ) {
          $record['rek_secondary_title'] = $record['rek_conference_name'];
        }

        // Replace double quotes with double double quotes
        if ( !empty($record['rek_secondary_title']) ) {
          $record['rek_secondary_title'] = str_replace('"', '""', $record['rek_secondary_title']);
        }

        // Get the Digital Object Identifier (DOI) for the publication if one exists in rek_link
        if ( is_array($record['rek_link']) ) {
          for ($j=0; $j<count($record['rek_link']); $j++) {
            if (preg_match('/^http:\/\/dx\.doi\.org\/(.*)$/i', $record['rek_link'][$j], $matches)) {
              $record['rek_electronic_resource_num'] = $matches[1];
            }
          }
        }

        // Set end page to be the start page, if only start page exists
        if ((!empty($list['rek_start_page'])) && empty($list['rek_end_page'])) {
          $list['rek_end_page'] = $list['rek_start_page'];
//          unset($list['rek_start_page']);
        }

        // Get the author details where an rek_author_id array is specified
        if (is_array($record['rek_author_id'])) {
          $record['rek_author_id_details'] = array();
          for ($j=0; $j<count($record['rek_author_id']); $j++) {
            $record['rek_author_id_details'][] = Author::getDetails($record['rek_author_id'][$j]);
          }
        }

        // We need at least one author and a title and the ref type
        if ( (! (is_array($record['rek_author_id_details'])
            && is_array($record['rek_author'])))
            || empty($record['rek_title'])
            || empty($record['rek_ref_type']) ) {
          // Record does not have required data
        } else {
          $return['list'][] = $record;
        }
      }
    }
    return $return;
  }

  /**
   * Method used to get the ResearcherID doc-type based on the record's display type
   *
   * @access  public
   * @param   string $display_type The display type to get the ref type for
   * @return  string The ref-type.
   */
  private static function getDocTypeByDisplayType($display_type)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                  tdm_doctype
               FROM
                  " . APP_TABLE_PREFIX . "thomson_doctype_mappings
               WHERE
                  tdm_xdis_id = ?";

    try {
      $res = $db->fetchOne($stmt, array($display_type));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '13'; // Return generic ref-type
    }

    if (! $res) {
      return '13';
    } else {
      return $res;
    }
  }

    /**
     * Method used to find a tickets date restrictions if any
     *
     * @access  public
     * @param   string $ticketNo Ticket number it ran under
     * @return  string Any date restrictions on the request
     */
    private static function getUpdateTime($ticketNo)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $ticketNo .= '%'; //Fore the like function

        $stmt = "SELECT
                  rij_downloadrequest
               FROM
                  " . APP_TABLE_PREFIX . "rid_jobs
               WHERE
                  rij_ticketno LIKE ?";

        try {
            $res = $db->fetchOne($stmt, array($ticketNo));
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        //Load in the XML and extract the date restriction if there is one and return it
        $xml_request_data = new DOMDocument();
        $xml_request_data->loadXML($res);
        $xpath = new DOMXPath($xml_request_data);
        $xpath->registerNamespace('rid', 'http://www.isinet.com/xrpc42');
        $d = $xpath->query("/rid:request/rid:fn[@name='AuthorResearch.downloadRIDData']/rid:list/rid:val")->item(0)->nodeValue;
        $cData = new DOMDocument();
        $cData->loadXML($d);
        $xpath = new DOMXPath($cData);
        $day = $xpath->query("/download-request/download-publications-added-since/day")->item(0)->nodeValue;
        $month = $xpath->query("/download-request/download-publications-added-since/month")->item(0)->nodeValue;
        $year = $xpath->query("/download-request/download-publications-added-since/year")->item(0)->nodeValue;
        return (!empty($day) && !empty($month) && !empty($year)) ? $year.'-'.$month.'-'.$day : false;
    }

    /**
     * Method used to update an authors last publication added date if it's newer
     *
     * @access  public
     * @param   string $ticketNo Ticket number it ran under
     */
    private static function authorUpdateTime($authorId, $date)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                  aut_rid_last_updated
               FROM
                  " . APP_TABLE_PREFIX . "author
               WHERE
                  aut_id = ".$db->quote($authorId);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if (empty($res) || $date > $res) {
            $stmt = "UPDATE " . APP_TABLE_PREFIX . "author SET aut_rid_last_updated=".$db->quote($date)." WHERE aut_id=".$db->quote($authorId);
            try {
                $db->exec($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
            }
        }

    }

}
