<?php

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

/**
 * Handles the processes required for tracking HERDC Evidence follow-up.
 *
 * @version 1.0, April 2012
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Fez_Workflow_Herdc_EvidenceFollowUp
{
    protected $_log = null;
    protected $_pid = null;
    protected $_userDetails = array();
    protected $_emailTemplate = "workflow/emails/herdc_evidence_followup.tpl.html";
    protected $_subjectPrefix = "Evidence follow-up";  
    protected $_espaceEventumTeamId = 36;
    protected $_uposInfo = array();


    /**
     * Class constructor
     * @param string $pid 
     */
    public function __construct($pid)
    {
        $this->_pid = $pid;
        $this->_log = FezLog::get();
        
        $username = Auth::getUsername();
        $this->_userDetails = User::getDetails($username);
    }

    
    /** 
     * Lodge HERDC Evidence Follow-up tracking.
     * 
     * @return boolean
     */
    public function lodge($upoList)
    {
        foreach($upoList as $upo){
            $this->_uposInfo[] = User::getNameEmail($upo);
        }
        // Lodge an Eventum job
        $lodge = $this->_lodgeEventumJob();
        
        // Get out when failed to lodge Eventum job.
        if (!$lodge){
            $this->_log->err("HERDC Evidence follow-up failed for PID '". $this->_pid ."'.");
            return false;
        }
        // Add history to the PID
        // comment out since we cannot get Eventum to return the newly created issue ID.
        // $this->_addHistory();

        return true;        
    }

    
    /**
     * Returns link to Eventum search for this HERDC Evidence follow-up.
     * @return string 
     */
    public function getEventumLink()
    {
        $title = "Search for this PID Evidence follow-up in Eventum";
        
        $link = '<a href="' .
                        'https://helpdesk.library.uq.edu.au/' .
                        'list.php?' .
                        'keywords=' . $this->_buildEventumSearchKeyword() .
                        '&projects=' . $this->_espaceEventumTeamId . 
                        '&rows=25&sort_by=iss_id&sort_order=desc&hide_closed=1' .
                        '" ' .
                    'title="' . $title . '"' .
                '>' .
                $title .
                '</a>';

        return $link;
    }

    
    /**
     * Lodges an Eventum job
     * @return boolean 
     */
    protected function _lodgeEventumJob()
    {
        $emailSender  = $this->_getEventumEmailSender();
        $emailSubject = $this->_buildEventumEmailSubject();
        $emailContent = $this->_buildEventumEmailContent();
        
        if (empty($emailContent)){
            return false;
        }
        $mail = new Mail_API;
        if (APP_EVENTUM_SEND_EMAILS == 'ON') {
            $Bcc = APP_EVENTUM_NEW_JOB_EMAIL_ADDRESS;
            $mail->addBcc($Bcc);
        }
        $to ='';
        foreach($this->_uposInfo as $upo)
        {
            $to = $to.$upo['usr_email'].', ';
        }
        // Send the email.
        $mail->setHTMLBody($emailContent);
        $mail->send($emailSender, $to, $emailSubject, false);
        
        return true;
    }

    
    /**
     * Returns email address for lodging an Eventum job.
     * @return string 
     */
    protected function _getEventumEmailSender()
    {
        return $this->_userDetails['usr_email'];
    }
    
    
    /**
     * Returns the email subject for lodging an Eventum job
     * @return string 
     */
    protected function _buildEventumEmailSubject()
    {
        // Depositor Org Unit 
        $orgId   = Record::getSearchKeyIndexValue($this->_pid, "Depositor Affiliation", true);
        $orgUnit = Org_Structure::getTitle($orgId);

        // Published Year
        $publishedDate = Record::getSearchKeyIndexValue($this->_pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));

        $subject = $this->_subjectPrefix . " :: " . 
                   $this->_pid . " :: " . 
                   $publishedDate . " :: " . 
                   $orgUnit . " :: ".
                   "Requested by " . $this->_userDetails['usr_full_name'];
        
        return $subject;
    }
    
    
    /**
     * Returns the email content for lodging an Eventum job.
     * @return string 
     */
    protected function _buildEventumEmailContent()
    {
        // Get template
        $tplEmail = new Template_API();
        $tplEmail->setTemplate($this->_emailTemplate);

        // Assign variables
        // Subject
        $tplEmail->assign("subject", $this->_buildEventumEmailSubject());
        
        // Doc type
        $xdisId  = Record::getSearchKeyIndexValue($this->_pid,'Display Type');
        $xdisKey = array_keys($xdisId);
        $xdisId  = $xdisKey[0];
        $xdisTitle = XSD_Display::getTitle($xdisId);	
        $docType   = strtolower(str_replace(" ", "", $xdisTitle));
        $tplEmail->assign("doc_type", $docType);
        
        // PID URL
        $pidUrl = "http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $this->_pid;
        $tplEmail->assign("pidUrl", $pidUrl);
        
        $emailContent = $tplEmail->getTemplateContents();
        
        if (empty($emailContent)){
            $this->_log->err("Empty Eventum email content for HERDC Evidence follow-up. ".
                             " PID '". $this->_pid ."'. Doc Type: '" . $docType . "'.");
            return false;
        }
        
        return $emailContent;
    }
    
    
    /**
     * Returns search keyword for this evidence follow-up on Eventum.
     * 
     * @param boolean $urlencode
     * @return string 
     */
    protected function _buildEventumSearchKeyword($urlencode = true)
    {
        // Wrap keyword around double quotes '"' to get more refined search results on Eventum.
        $keyword = '"' . $this->_subjectPrefix . " :: " .  $this->_pid . '"';
        
        if ($urlencode){
            $keyword = urlencode($keyword);
        }
        
        return $keyword;
    }

    
    /**
     * Adds PID history.
     * 
     * @todo: Mary-Anne requested Eventum issue ID onto the history. Need more research if it is possible.
     * 
     * @param string $msg
     * @return boolean 
     */
    protected function _addHistory()
    {
        $message = "HERDC evidence follow-up has been sent to Eventum for PID '". $this->_pid ."'.";
        $extraMsg = null;

        History::addHistory($this->_pid, null, "", "", true, $message, $extraMsg);

        return true;
    }

}
