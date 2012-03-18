<?php

/**
 * class RecordGeneral
 * For general record stuff - shared by collections and communities as well as records.
 */
class RecordGeneral
{
  var $pid;
  var $xdis_id;
  var $no_xdis_id = false;  // true if we couldn't find the xdis_id
  var $viewer_roles;
  var $lister_roles;
  var $editor_roles;
  var $creator_roles;
  var $deleter_roles;
  var $approver_roles;
  var $checked_auth = false;
  var $auth_groups;
  var $display;
  var $details;
  var $record_parents;
	var $doc;
	var $parentNode;
  var $status_array = array(
  Record::status_undefined => 'Undefined',
  Record::status_unpublished => 'Unpublished',
  Record::status_published => 'Published'
  );
  var $title;
  protected $_bgp;

  /**
   * Links this instance to a corresponding background process
   *
   * @param BackgroundProcess Object Instance $bgp
   */
  public function setBGP(&$bgp)
  {
    $this->_bgp = &$bgp;
  }
  
  /**
   * RecordGeneral
   * If instantiated with a pid, then this object is linked with the record with the pid, otherwise we are inserting
   * a record.
   *
   * @access  public
   * @param   string $pid The persistant identifier of the object
   * @param   string $createdDT (optional) Fedora timestamp of version to retrieve
   * @return  void
   */
  function RecordGeneral($pid=null, $createdDT=null)
  {
    $this->pid = $pid;
    $this->createdDT = $createdDT;
    $this->lister_roles = explode(',', APP_LISTER_ROLES);
    $this->viewer_roles = explode(',', APP_VIEWER_ROLES);
    $this->editor_roles = explode(',', APP_EDITOR_ROLES);
    $this->creator_roles = explode(',', APP_CREATOR_ROLES);
    $this->deleter_roles = explode(',', APP_DELETER_ROLES);
    $this->approver_roles = explode(',', APP_APPROVER_ROLES);
    $this->versionsViewer_roles = explode(',', APP_VIEW_VERSIONS_ROLES);
    //        $this->versionsReverter_roles = explode(',',APP_REVERT_VERSIONS_ROLES);
  }

  function getPid()
  {
    return $this->pid;
  }

  /**
   * refresh
   * Reset the status of the record object so that all values will be re-queried from the database.
   * Call this function if the database is expected to have changed in relation to this record.
   *
   * @access  public
   * @return  void
   */
  function refresh()
  {
    $this->checked_auth = false;
  }

  /**
   * getXmlDisplayId
   * Retrieve the display id for this record
   *
   * @access  public
   * @return  void
   */
  function getXmlDisplayId($getFromXML = false)
  {
    if (!$this->no_xdis_id) {
      if (empty($this->xdis_id) || ($getFromXML === true)) {
        if (!$this->checkExists()) {
          Error_Handler::logError("Record ".$this->pid." doesn't exist", __FILE__, __LINE__);
          return null;
        }
        if ($getFromXML === true) {
          $xdis_array = Fedora_API::callGetDatastreamContentsField(
              $this->pid, 'FezMD', array('xdis_id'), $this->createdDT
          );
          if (isset($xdis_array['xdis_id'][0])) {
            $xdis_id = $xdis_array['xdis_id'][0];
          } else {
            $this->no_xdis_id = true;
            return null;
          }
        } else {
          if(APP_FEDORA_BYPASS == 'ON')
          {
              $xdis_id = Record::getSearchKeyIndexValue($this->pid,'Display Type');
              $xdis_key = array_keys($xdis_id);
              $xdis_id = $xdis_key[0];
          }
          else 
          {
              $xdis_id = XSD_HTML_Match::getDisplayType($this->pid);
          }
        }
        if (isset($xdis_id)) {
          $this->xdis_id = $xdis_id;
        } else {
          $this->no_xdis_id = true;
          return null;
        }


      }
      return $this->xdis_id;
    }
    return null;
  }

  function getXmlDisplayIdUseIndex()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp = APP_TABLE_PREFIX;
    if (!$this->no_xdis_id) {
      if (empty($this->xdis_id)) {
        $stmt = "SELECT rek_display_type FROM ".$dbtp."record_search_key
						WHERE rek_pid = ".$db->quote($this->pid);
        try {
          $res = $db->fetchOne($stmt);
          $this->xdis_id = $res;
        }
        catch(Exception $ex) {
          $log->err($ex);
          $this->xdis_id = null;
          $this->no_xdis_id = true;
        }
      }
      return $this->xdis_id;
    }
    return null;
  }

  /**
   * getImageFezACML
   * Retrieve the FezACML image details eg copyright message and watermark boolean settings
   *
   * @access  public
   * @return  void
   */
  function getImageFezACML($dsID)
  {
    if (!empty($dsID)) {
      $xdis_array = Fedora_API::callGetDatastreamContentsField(
          $this->pid, 'FezACML'.$dsID.'.xml', array('image_copyright', 'image_watermark'), $this->createdDT
      );
      if (isset($xdis_array['image_copyright'][0])) {
        $this->image_copyright[$dsID] = $xdis_array['image_copyright'][0];
      }
      if (isset($xdis_array['image_watermark'][0])) {
        $this->image_watermark[$dsID] = $xdis_array['image_watermark'][0];
      }
    }
  }

  /**
   * getAuth
   * Retrieve the authroisation groups allowed for this record with the current user.
   *
   * @access  public
   * @return  void
   */
  function getAuth()
  {
    if (!$this->checked_auth) {
      $this->getXmlDisplayId();
      $this->auth_groups = Auth::getAuthorisationGroups($this->pid);
      $this->checked_auth = true;
    }

    return $this->auth_groups;
  }

  /**
   * checkAuth
   * Find out if the current user can perform the given roles for this record
   *
   * @param  array $roles The allowed roles to access the object
   * @param  $redirect
   * @access  public
   * @return  void
   */
  function checkAuth($roles, $redirect=true)
  {
    $this->getAuth();
    $ret_url = $_SERVER['REQUEST_URI'];
    /*	        $ret_url = $_SERVER['PHP_SELF'];
     if (!empty($_SERVER['QUERY_STRING'])) {
     $ret_url .= "?".$_SERVER['QUERY_STRING'];
     } */
    return Auth::checkAuthorisation($this->pid, "", $roles, $ret_url, $this->auth_groups, $redirect);
  }

  /**
   * canView
   * Find out if the current user can view this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canView($redirect=true)
  {
    if (Auth::isAdministrator()) {
      return true;
    }
    if ($this->getPublishedStatus() == 2) {
      return $this->checkAuth($this->viewer_roles, $redirect);
    } else {
      return $this->canCreate($redirect); // changed this so that creators can view the 
                                          // objects even when they are not published
    }
  }

  /**
   * canList
   * Find out if the current user can list this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canList($redirect=true)
  {
    if (Auth::isAdministrator()) {
      return true;
    }
    if ($this->getPublishedStatus() == 2) {
      return $this->checkAuth($this->lister_roles, $redirect);
    } else {
      return $this->canCreate($redirect); // changed this so that creators can view the objects even 
                                          // when they are not published
    }
  }

  /**
   * canEdit
   * Find out if the current user can edit this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canEdit($redirect=false)
  {
    if (Auth::isAdministrator()) {
      return true;
    }
    return $this->checkAuth($this->editor_roles, $redirect);
  }


  /**
   * canDelete
   * Find out if the current user can edit this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canDelete($redirect=false)
  {
    if (Auth::isAdministrator()) {
      return true;
    }
    return $this->checkAuth($this->deleter_roles, $redirect);
  }

  /**
   * canApprove
   * Find out if the current user can publish this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canApprove($redirect=false)
  {
    if (Auth::isAdministrator()) {
      return true;
    }
    return $this->checkAuth($this->approver_roles, $redirect);
  }

  /**
   * canCreate
   * Find out if the current user can create this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canCreate($redirect=false)
  {
    return $this->checkAuth($this->creator_roles, $redirect);
  }

  /**
   * canViewVersions
   * Find out if the current user can view versions of this record
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  function canViewVersions($redirect=false)
  {
//    if(APP_VERSION_UPLOADS_AND_LINKS != "ON") return false; // turned off only showing version button when file and link versioning was on so metadata versioning could still be shown
    return $this->checkAuth($this->versionsViewer_roles, $redirect);
  }

  /**
   * canRevertVersions
   * Find out if the current user can revert this record to an earlier version
   *
   * @access  public
   * @param  $redirect
   * @return  void
   */
  //    function canRevertVersions($redirect=false) {
  //		  if(APP_VERSION_UPLOADS_AND_LINKS != "ON") return false;
  //        return $this->checkAuth($this->versionsReverter_roles, $redirect);
  //    }

  function getPublishedStatus($astext = false)
  {
    if(APP_FEDORA_BYPASS == 'ON') {
        $do = new DigitalObject;
        return $do->isPublished($this->pid);

    } else {
        $this->getDisplay();
        $this->display->getXSD_HTML_Match();
        $this->getDetails();
        //$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!sta_id", $this->xdis_id);
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!sta_id');
        $status = $this->details[$xsdmf_id];

        if (!$astext) {
          return $status;
        } else {
          return $this->status_array[$status];
        }
    }
  }

  function getRecordType()
  {
    $this->getDisplay();
    $this->getDetails();
    $this->display->getXSD_HTML_Match();

    //$this->getXmlDisplayId();
    if (!empty($this->xdis_id)) {
      //$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!ret_id", $this->xdis_id);
      //echo $xsdmf_id;
      $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!ret_id');
      $ret_id = $this->details[$xsdmf_id];
      return $ret_id;
    } else {
      return null;
    }
  }


  /**
   * setStatusID
   * Used to assocaiate a display for this record
   *
   * @access  public
   * @param  integer $sta_id The new Status ID of the object
   * @return  void
   */
  function setStatusId($sta_id)
  {
        if (APP_FEDORA_BYPASS == 'ON') {

            $searchKeyData = array();
            $details = Record::getDetailsLite($this->pid);
            
            // $searchKeyData[0] for 1-to-1 search keys
            $searchKeyData[0]['status'] = array('xsdmf_id' => $details[0]['rek_status_xsdmf_id'], 'xsdmf_value' => $sta_id);
            $searchKeyData[0]['updated_date'] = array('xsdmf_id' => $details[0]['rek_updated_date_xsdmf_id'], 'xsdmf_value' => Date_API::getFedoraFormattedDateUTC());
            
            // Update the search keys for this PID with new value
            Record::updateSearchKeys($this->pid, $searchKeyData);
            
            Record::updateSearchKeysShadow($this->pid);
            
        } else {
            
            // Update the XML for FezMD datastream, 
            // which contains the record status ID and other status flags 
            // - for list of fields see $this->setFezMD_Datastream() function comment
    $this->setFezMD_Datastream('sta_id', $sta_id);
            
            // Get a list of related XSD DisplayObjects for this record - based on record's XSD DisplayObject
    $this->getDisplay();
            /* Sample results:
             * XSD_DisplayObject Object
                (
                    [xdis_id] => 174
                    [xsd_html_match] => XSD_HTML_MatchObject Object
                        (
                            [xdis_str] => 207,172,84,16,111,174
                        )

                    [exclude_list] => 
                    [specify_list] => 
                )
             */
            
            // Update Index of XSDMF fields from the record XSD Display
    $this->setIndexMatchingFields();
        }
    return 1;
  }

  /**
   * setFezMD_Datastream
   * Used to associate a display for this record.
   * Store new XML for FezMD datastream, with the new value specified by $key and $value parameters
   *
   * @access  public
   * @param  $key
   * @param  $value
   * @return  void
   */
  function setFezMD_Datastream($key, $value)
  {
    $items = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
    /* Value of $item on Fedora version: */
    /*
        ITEMS = Array
        (
            [xdis_id]   => Array ( [0] => 174 )
            [sta_id]    => Array ( [0] => 4 )
            [ret_id]    => Array ( [0] => 3 )
            [usr_id]    => Array ( [0] => 1142 )
            [grp_id]    => Array ( [0] => )
            [copyright] => Array ( [0] => on )
            [created_date]          => Array ( [0] => 2012-02-20T05:16:27Z )
            [updated_date]          => Array ( [0] => 2012-02-20T05:16:27Z )
            [depositor]             => Array ( [0] => 1142 )
            [depositor_affiliation] => Array ( [0] => 786 )
            [additional_notes]      => Array ( [0] => )
            [refereed]              => Array ( [0] => off )
            [reference_text]        => Array ( [0] => )
            [follow_up]             => Array ( [0] => )
            [follow_up_imu]         => Array ( [0] => )
            [scopus_doc_type]       => Array ( [0] => )
            [wok_doc_type]          => Array ( [0] =>  )
            [herdc_status]          => Array ( [0] => 453220 )
            [institutional_status]  => Array ( [0] => 453225 )
        )
     */
    
    $newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
    $foundElement = false;
    foreach ($items as $xkey => $xdata) {
      foreach ($xdata as $xinstance) {
        if ($xkey == $key) {
          $foundElement = true;
          $newXML .= "<".$xkey.">".$value."</".$xkey.">";
        } elseif ($xinstance != "") {
          $newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
        }
      }
    }
    if ($foundElement != true) {
      $newXML .= "<".$key.">".$value."</".$key.">";
    }
    $newXML .= "</FezMD>";

    
    /*
     * Value of $newXML on Fedora version:
     *  
     NEWXML   
       <FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <xdis_id>174</xdis_id>
            <sta_id>2</sta_id>
            <ret_id>3</ret_id>
            <usr_id>1142</usr_id>
            <copyright>on</copyright>
            <created_date>2012-02-20T04:50:57Z</created_date>
            <updated_date>2012-02-20T04:50:57Z</updated_date>
            <depositor>1142</depositor>
            <depositor_affiliation>786</depositor_affiliation>
            <refereed>off</refereed>
            <herdc_status>453220</herdc_status>
            <institutional_status>453225</institutional_status>
       </FezMD>
     *
     */
    
    /**
     * On FEDORA BYPASS version, the value should be stored on database as follows:
     
       <FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <xdis_id>174</xdis_id>                              ==  fez_record_search_key.rek_display_type
            <sta_id>2</sta_id>                                  ==  fez_record_search_key.rek_status
            <ret_id>3</ret_id>                                  ==  fez_record_search_key.object_type
            <usr_id>1142</usr_id>                               ==  ... 
            <copyright>on</copyright>                           ==  fez_record_search_key.rek_copyright
            <created_date>2012-02-20T04:50:57Z</created_date>   ==  fez_record_search_key.rek_created_date
            <updated_date>2012-02-20T04:50:57Z</updated_date>   ==  fez_record_search_key.rek_updated_date
            <depositor>1142</depositor>                         ==  fez_record_search_key.rek_depositor
            <depositor_affiliation>786</depositor_affiliation>  ==  fez_record_search_key.rek_depositor_affiliation
            <refereed>off</refereed>                            ==  fez_record_search_key.rek_created_date
            <herdc_status>453220</herdc_status>                 ==  fez_record_search_key_herdc_status.rek_herdc_status
            <institutional_status>453225</institutional_status> ==  fez_record_search_key_institutional_status.rek_institutional_status
       </FezMD>
     */
    
    //Error_handler::logError($newXML,__FILE__,__LINE__);
    
    
    // Update the XML Datastream with $newXML
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", "inherit"
      );
    }
  }

  /**
   * _Datastream
   * Used to associate a display for this record
   *
   * @access  public
   * @param  $key
   * @param  $value
   * @return  void
   */
  function updateRELSEXT($key, $value, $removeCurrent = true)
  {
    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'RELS-EXT', true);

    if (empty($xmlString) || !is_string($xmlString)) {
      return -3;
    }

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);
    $fieldNodeList = $xpath->query("/rdf:RDF//rel:isMemberOf");

    if ($fieldNodeList->length == 0) {
      /*
       * There was a point in time when incorrect RELS-EXT xml
       * was created, with an incorrect namespace 'rdf:isMemberOf'
       * instead of 'rel:isMemberOf'.
       */
      $fieldNodeList = $xpath->query("/rdf:RDF//rdf:isMemberOf");
      if ($fieldNodeList->length == 0) {
        return -2;
      }
    }


    foreach ($fieldNodeList as $fieldNode) { // first delete all the isMemberOfs
      $parentNode = $fieldNode->parentNode;
      if ( $removeCurrent ) {
        $parentNode->removeChild($fieldNode);
      }
    }
    $newNode = $doc->createElementNS('info:fedora/fedora-system:def/relations-external#', 'rel:isMemberOf');
    $newNode->setAttribute('rdf:resource', 'info:fedora/'.$value);
    $parentNode->appendChild($newNode);
    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "RELS-EXT", "A", "Relationships to other objects", $newXML, "text/xml", "inherit"
      );
      Record::setIndexMatchingFields($this->pid);
      return 1;
    }

    return -1;
  }

  function getDatastreamNameDesc($sek_title)
  {
      $log = FezLog::get();
      $db = DB_API::get();
			
      $stmt = "SELECT xsdsel_title as datastreamname, x3.xsdmf_static_text as datastreamdesc
			FROM 
			" . APP_TABLE_PREFIX . "xsd_display_matchfields x1
			INNER JOIN " . APP_TABLE_PREFIX . "search_key ON x1.xsdmf_sek_id = sek_id AND sek_title = ".$db->quote($sek_title)."
			INNER JOIN " . APP_TABLE_PREFIX . "xsd_relationship ON xsdrel_xdis_id = x1.xsdmf_xdis_id
			INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields x2 ON x2.xsdmf_id = xsdrel_xsdmf_id
			INNER JOIN " . APP_TABLE_PREFIX . "xsd_loop_subelement ON x2.xsdmf_xsdsel_id = xsdsel_id
			INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_display_type = x2.xsdmf_xdis_id
			INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields x3 ON x3.xsdmf_xsdsel_id = xsdsel_id AND x3.xsdmf_element = '!datastream!datastreamVersion!LABEL'
			WHERE rek_pid = ".$db->quote($this->pid);
      
      try {
          $res = $db->fetchRow($stmt);
      }
      catch (Exception $ex) {
          $log->err($ex);
          return false;
      }
      return $res;

  }


  function addSearchKeyValueList(
      $search_keys=array(), $values=array(), 
      $removeCurrent=true, $history="was added based on Links AMR Service data"
  )
  {
		$datastreams = array();
		$search_keys_added = array();
		foreach ($search_keys as $s => $sk) {
			$dsDetails = $this->getDatastreamNameDesc($sk);
			$datastreamName = $dsDetails['datastreamname'];
			$datastreamDesc = $dsDetails['datastreamdesc'];
			if (!array_key_exists($datastreamName, $datastreams)) {
	    		$datastreams[$datastreamName] = Fedora_API::callGetDatastreamContents($this->pid, $datastreamName, true);
			}
	    if (is_array($datastreams[$datastreamName]) || $datastreams[$datastreamName] == "") {
	       echo "\n**** PID ".$this->pid." without a ".$datastreamName.
	            " datastream was found, this will need content model changing first **** \n";
	    }
	    $doc = DOMDocument::loadXML($datastreams[$datastreamName]);
      $tempdoc = $this->addSearchKeyValue($doc, $sk, $values[$s], $removeCurrent);
      if ($tempdoc !== false) {
        if (!empty($values[$s])) {
          $search_keys_added[$sk] = $values[$s];
        }
        $datastreams[$datastreamName] = $tempdoc->saveXML();;
      }
    }
    
    if (count($search_keys_added) > 0) {
			foreach ($datastreams as $datastreamName => $newXML) {
				if ($newXML != "") {
					$dsExists = Fedora_API::datastreamExists($this->pid, $datastreamName);
					if ($dsExists !== true) {
						Fedora_API::getUploadLocation($this->pid, $datastreamName, $newXML, $datastreamDesc,
								"text/xml", "X",null,"true");
					} else {
			      Fedora_API::callModifyDatastreamByValue(
			          $this->pid, $datastreamName, "A", $datastreamDesc, $newXML, "text/xml", "inherit"
			      );
					}	
				}
			}
      $historyDetail = "";
      foreach ($search_keys_added as $hkey => $hval) {
        if ($historyDetail != "") {
          $historyDetail .= ", ";
        }
        $historyDetail .= $hkey.": ".$hval;
      }
      $historyDetail .= " " . $history;
      History::addHistory($this->pid, null, "", "", true, $historyDetail);
      $this->setIndexMatchingFields();
      return 1;
    }
    return -1;

  }

	function buildXMLWithXPATH($xpath_query, $value, $lookup_value, $recurseLevel) {
	    $xpath = new DOMXPath($this->doc);
			// echo "\n IN WITH THIS: ".$this->doc->saveXML();
			// ob_flush();
	    $pre_xpath_query = substr($xpath_query, 0, (strrpos($xpath_query, "/")));
			if ($pre_xpath_query == "" ) {
				return false;
			}
       // echo "\n parent pre element query is $pre_xpath_query <br /> \n";
	    $parentNodeList = $xpath->query($pre_xpath_query);
			$parentNode = NULL;
      foreach ($parentNodeList as $fieldNode) {
				// echo "\n FOUND initial $pre_xpath_query for parentNode will just add to this \n";
        $parentNode = $fieldNode;
      }

			$goingDown = 0;
      if (is_null($parentNode)) {
				$goingDown = 1;
				// echo "the query $xpath_query found nothing so recursing down $recurseLevel \n";
				$this->buildXMLWithXPATH($pre_xpath_query, $value, $lookup_value, ($recurseLevel+1));
//				$parentNode = $this->buildXMLWithXPATH($pre_xpath_query, $doc, $value, $lookup_value, ($recurseLevel+1));
	    		//check the base has now been added ok
	
					// echo "\n BACK FROM recursion and now doc is ".$this->doc->saveXML(); echo "\n";
					
					$this->doc = DOMDocument::loadXML($this->doc->saveXML());
					
					// echo "Searching it now for ".$pre_xpath_query."...\n";
					$xpath2 = new DOMXPath($this->doc);
					$parentNodeList = $xpath2->query($pre_xpath_query);
					
					foreach ($parentNodeList as $fieldNode) {
						// echo "FOUND ONE !\n";
					  $parentNode = $fieldNode;
					}
   				// echo "current parent node after recurse is ".$parentNode->node_name() . "\n";

			}
			if (!is_null($parentNode)) {
			  // echo "found a $pre_xpath_query so going to add $xpath_query to it \n";

				// If we have had to dig down then we have to build the foundations up
			  $element = substr($xpath_query, (strrpos($xpath_query, "/") + 1));

				// echo "in recursion $recurseLevel adding $element \n";

		    $attributeStartPos = strpos($element, "[");
		    $attributeEndPos = strpos($element, "]") + 1;
		    $attribute = "";
		    if (is_numeric($attributeStartPos) && is_numeric($attributeEndPos)) {
		      $attribute = substr($element, $attributeStartPos, ($attributeEndPos - $attributeStartPos));
		      $element = substr($element, 0, $attributeStartPos);
		    }
		    $attributeNameStartPos = strpos($attribute, "[@") + 2;
		    $attributeNameEndPos = strpos($attribute, " =");
		    $attributeValueStartPos = strpos($attribute, "= ") + 2;
		    $attributeValueEndPos = strpos($attribute, "]");
		    $attributeName = substr($attribute, $attributeNameStartPos, ($attributeNameEndPos - $attributeNameStartPos));
		    $attributeValue = substr($attribute, $attributeValueStartPos, ($attributeValueEndPos - $attributeValueStartPos));
		    $attributeValue = str_replace("'", "", $attributeValue);
   				// echo "current parent node appending/setting is ".$parentNode->node_name() . "\n";
		  	if (substr($element, 0, 1) == "@") {
		        // echo "\n element: ".$element;
		        // echo "\n value: ".$value;
		        // echo "\n substr = ".substr($element, 1);
		      $parentNode->setAttribute(substr($element, 1), $value);
					// if this is an ID value, and we have a lookup value, set the element to the lookup value to keep the xml clean
					if ($lookup_value != "" && $recurseLevel == 2) {
						$parentNode->nodeValue = $lookup_value;
					}
		    } else {
					// if this is an ID on an attribute xpath like mods:subject/@ID then put the subject lookup value into the element (recurse level 2) 
					if ($recurseLevel == 2 && $lookup_value != "") {
			      $newNode = $this->doc->createElement($element, $lookup_value);
					} elseif ($recurseLevel == 1) {
		      	$newNode = $this->doc->createElement($element, $value);					
					} else {
			      $newNode = $this->doc->createElement($element);
					}

		      $newNode->setAttribute($attributeName, $attributeValue);
		      $parentNode->appendChild($newNode);
		    }
				// echo "\n created this: ".$this->doc->saveXML();
			}
			return true;
	}

  // Experimental function - like a swiss army knife for adding abitrary values to datastreams
  function addSearchKeyValue($doc, $sek_title, $value, $removeCurrent = true)
  {
    $newXML = "";
    $xdis_id = $this->getXmlDisplayId();
    $xpath_query = XSD_HTML_Match::getXPATHBySearchKeyTitleXDIS_ID($sek_title, $xdis_id);
		$sekDetails = Search_Key::getDetailsByTitle($sek_title);
		$sekID = $sekDetails['sek_id'];
		$lookup_value = "";
		if ($sekDetails['sek_lookup_function'] != "")  {
			eval("\$lookup_value = ".$sekDetails["sek_lookup_function"]."(".$value.");");
		} 

    if (empty($value)) {
      return false;
    }
    if (!$xpath_query) {
      echo "\n**** PID ".$this->pid." has no search key ".$sek_title.
           " so it will need content model changing first **** \n";
      return false;
    }
    $xpath = new DOMXPath($doc);
    $fieldNodeList = $xpath->query($xpath_query);
    $element = substr($xpath_query, (strrpos($xpath_query, "/") + 1));
    $attributeStartPos = strpos($element, "[");
    $attributeEndPos = strpos($element, "]") + 1;
    $attribute = "";
    if (is_numeric($attributeStartPos) && is_numeric($attributeEndPos)) {
      $attribute = substr($element, $attributeStartPos, ($attributeEndPos - $attributeStartPos));
      $element = substr($element, 0, $attributeStartPos);
    }
    if ( $removeCurrent && (substr($element, 0, 1) != "@") ) {
      foreach ($fieldNodeList as $fieldNode) { // first delete all the isMemberOfs
        $parentNode = $fieldNode->parentNode;
        $parentNode->removeChild($fieldNode);
      }
    }
    
		$this->doc = $doc;
		$xsdmf_id = XSD_HTML_Match::getXSDMFIDBySearchKeyTitleXDIS_ID($sek_title, $xdis_id);
		$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
		
		if ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') {
			$xsdmf_ref_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
			$this->buildXMLWithXPATH($xsdmf_ref_details['xsdmf_xpath'], $lookup_value, $lookup_value, 1);
			$xmlString = $this->doc->saveXML();
			$xmlString = str_replace('<mods:topic></mods:topic>', '', $xmlString); // BEGONE, FOUL EMPTY ELEMENT
			$xmlString = str_replace('<mods:topic/>', '', $xmlString); // AND YE!
			$this->doc = DOMDocument::loadXML($xmlString);
		}
		
		$this->buildXMLWithXPATH($xpath_query, $value, $lookup_value, 1);
	    $xmlString = $this->doc->saveXML();
	    $this->doc = DOMDocument::loadXML($xmlString);
	    
	    return $this->doc;
  }




  /**
   * Remove record from collection
   *
   * @param string $collection  the pid of the collection
   *
   * @return bool  TRUE if removed OK. FALSE if not removed.
   *
   * @access public
   * @since Method available since RC1
   */
  function removeFromCollection($collection)
  {
    if ($collection == "") {
      return false;
    }

    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'RELS-EXT', true);

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    $fieldNodeList = $xpath->query("//rel:isMemberOf[@rdf:resource='info:fedora/$collection']");

    if ($fieldNodeList->length == 0) {
      return false;
    }

    $collectionNode   = $fieldNodeList->item(0);
    $parentNode       = $collectionNode->parentNode;
    $parentNode->removeChild($collectionNode);

    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "RELS-EXT", "A", "Relationships to other objects", $newXML, "text/xml", "inherit"
      );
      Record::setIndexMatchingFields($this->pid);
      if (APP_SOLR_INDEXER == "ON") {
        FulltextQueue::singleton()->add($this->pid);
        FulltextQueue::singleton()->commit();
      }
      return true;
    }

    return false;
  }




  /**
   * Strips isi_loc from a record
   *
   * @return bool  TRUE if stripped OK. FALSE if not stripped.
   *
   * @access public
   */
  function stripIsiLoc()
  {
    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'MODS', true);

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    //$fieldNodeList = $xpath->query("//rel:isMemberOf[@rdf:resource='info:fedora/$collection']");
    $fieldNodeList = $xpath->query("/mods:mods/mods:identifier[@type = 'isi_loc']");

    if ($fieldNodeList->length == 0) {
      return false;
    }

    $collectionNode   = $fieldNodeList->item(0);
    $parentNode       = $collectionNode->parentNode;
    $parentNode->removeChild($collectionNode);

    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit"
      );
      $historyDetail = "Isi_loc was stripped in preparation of Links AMR Service data import";
      History::addHistory($this->pid, null, "", "", true, $historyDetail);
      Record::setIndexMatchingFields($this->pid);
      /*if( APP_SOLR_INDEXER == "ON" ) {
       FulltextQueue::singleton()->add($this->pid);
       FulltextQueue::singleton()->commit();
       }*/
      return true;
    }

    return false;
  }


  /**
   * Strips scopus ID (EID) from a record
   *
   * @return bool  TRUE if stripped OK. FALSE if not stripped.
   *
   * @access public
   */
  function stripScopusID()
  {
    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'MODS', true);

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    //$fieldNodeList = $xpath->query("//rel:isMemberOf[@rdf:resource='info:fedora/$collection']");
    $fieldNodeList = $xpath->query("/mods:mods/mods:identifier[@type = 'scopus']");

    if ( $fieldNodeList->length == 0 ) {
      return false;
    }

    $collectionNode   = $fieldNodeList->item(0);
    $parentNode       = $collectionNode->parentNode;
    $parentNode->removeChild($collectionNode);

    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit"
      );
      $historyDetail = "Scopus ID (EID) was stripped";
      History::addHistory($this->pid, null, "", "", true, $historyDetail);
      Record::setIndexMatchingFields($this->pid);
      /*if( APP_SOLR_INDEXER == "ON" ) {
       FulltextQueue::singleton()->add($this->pid);
       FulltextQueue::singleton()->commit();
       }*/
      return true;
    }

    return false;
  }

  /**
   * Replaces authors in a record using the authors in ESTI
   *
   * @return bool  TRUE if replaced OK. FALSE if not replaced.
   *
   * @access public
   */
  function replaceAuthorsFromEsti()
  {
    $log = FezLog::get();

    $newXML = "";

    $ut = Record::getIsiLocFromIndex($this->pid);
    if (is_array($ut)) {
      $ut = $ut[0];
    }

    if (empty($ut)) {
      return false;
    }

    $records_xml = EstiSearchService::retrieve($ut);

    $record = null;
    if ($records_xml) {
      foreach ($records_xml->REC as $_record) {
        $record = $_record;
      }
    }

    if (! $record) {
      return false;
    }

    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'MODS', true);
    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    $field_node_list = $xpath->query("/mods:mods/mods:name");
    $count = $field_node_list->length;
    if ($count > 0) {
      for ($i = 0; $i < $count; $i++) {
        $collection_node   = $field_node_list->item($i);
        $parent_node       = $collection_node->parentNode;
        $parent_node->removeChild($collection_node);
      }
    }

    $mods = '
				<mods:name ID="%s" authority="%s">
		        	<mods:namePart type="personal">%s</mods:namePart>
		            <mods:role>
		            	<mods:roleTerm type="text">%s</mods:roleTerm>
		            </mods:role>
		        </mods:name>
		';

    $authors = '<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
    $authors .= sprintf($mods, '0', APP_ORG_NAME, $record->item->authors->primaryauthor, 'author');
    foreach ($record->item->authors->author as $author) {
      $authors .= sprintf($mods, '0', APP_ORG_NAME, $author, 'author');
    }
    $authors .= '</mods:mods>';

    $authors_doc = new DOMDocument;
    $authors_doc->loadXML($authors);
    $author_nodes = $authors_doc->getElementsByTagName("name");

    $count = $author_nodes->length;
    if ($count > 0) {
      for ($i = 0; $i < $count; $i++) {
        $node = $doc->importNode($author_nodes->item($i), true);
        $doc->documentElement->appendChild($node);
      }
    }

    $newXML = $doc->SaveXML();

    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit"
      );
      $historyDetail = "Authors were replaced using ESTI";
      History::addHistory($this->pid, null, "", "", true, $historyDetail);
      Record::setIndexMatchingFields($this->pid);
      return true;
    }
    return false;
  }

  /**
   * Attempt to match $aut_id with an author on $this->pid. This is a multi-step process in order
   * to establish a percentage value of how likely this author is on this pub (probablistic matching)
   * and also whether we have identified the correct author on the pub based on some rules (deterministic
   * matching).
   *
   * The following steps are used to establish the percentage:
   *
   * 1. Match their name, or other names they've published under, with the authors listed on this pub.
   *
   * 2. For all other authors on the pub with an author ID set, determine how many pubs they have been
   *    co-authors on with $aut_id. This step should only be completed where there are 10 or less authors
   *    on this pub.
   *
   *    For any of the matched pubs above, optionally check the keywords on both pubs to determine if we're
   *    dealing with similarly categorised pubs.
   *
   * The following rules determine whether we have found the author on this pub:
   *
   * 1. In 1 above, if only one match is found based on Levenshtein distance (@see matchAuthorNameByLev)
   *
   * 2. In 2 above, if at least one co-authored pub is found AND both pubs share at least one keyword.
   *
   * 3. There's 10 or less authors on the pub.
   *
   * If all of these rules are satisifed, the pub is automatically amended with the author's ID ($update = TRUE),
   * else it returns a percentage of how likely a match was found. If $known is set to TRUE, only rule #1 needs
   * to be satisfied.
   *
   * @param int  $aut_id    The author ID we are attempting to match on this pub
   * @param bool $update    (optional) Whether you want the pub to be automatically amended with the author's ID
   *                        if a match is found. Default is TRUE, which will amend the pub.
   * @param bool $known     (optional) Set to TRUE if you are certain that the author $aut_id is listed
   *                        on this pub, otherwise leave as FALSE. Default is FALSE.
   * @param int  $threshold (optional) If the resulting percentage value is greater than the threshold then
   *                        a matching author has been identified on this pub. Default is 1 (100%).
   * @param int  $keywords  (optional) Whether to include keywords when matching on co-authors. Default is TRUE.
   *
   * @return mixed
   */
  function matchAuthor($aut_id, $update = TRUE, $known = FALSE, $threshold = 1, $keywords = TRUE)
  {
    $log = FezLog::get();
     
    $rule1 = FALSE;
    $rule2 = FALSE;
    $rule3 = FALSE;

    // Step 1: Match on name
    $authors = $this->getAuthors();
    $orig_authors = $authors;
    $known_authors = array();
     
    $aut_details = Author::getDetails($aut_id);
    $aut_alt_names = Author::getAlternativeNamesList($aut_id);
    $exact_match_count = 0;
    $match_index = 0;
    $percent_1 = 0;
    // Message to add to the record if we find an author and update
    $message = 'Author ID '.$aut_id.' inserted using author matching';

    // Get only authors missing a author ID and make sure the author isn't already set on the pub
    $unknown_authors = array();
    $co_aut_ids = array();
    for ($i = 0; $i < count($authors); $i++) {
      $authors[$i]['pos'] = $i;
      if ($authors[$i]['aut_id'] == 0) {
        $unknown_authors[] = $authors[$i];
      } else if ($authors[$i]['aut_id'] == $aut_id) {
        // Nothing to do, the author ID has already been set
        return array(TRUE, 'Already set');
      } else {
        $known_authors[] = $authors[$i];
        $co_aut_ids[] = $authors[$i]['aut_id'];
      }
    }
    $authors = $unknown_authors;
    $authors_count = count($authors);
    
    for ($i = 0; $i < $authors_count; $i++) {
      $authors[$i]['match'] = FALSE;
      $percent = 0;      
      if ($aut_details['aut_org_username']) {        
        if ($known) {
          // Last name match first, if we have $authors[$i]['name'] in the format LName, F
          $name_parts = explode(',', $authors[$i]['name']);
          $percent = $this->matchAuthorNameByLev($name_parts[0], $aut_details['aut_lname'], $percent_1);
          if ($percent == 1) {
            $exact_match_count++;
            $match_index = $i;
            $authors[$i]['match'] = $percent;
          } else {
            if( $this->_bgp ) {
              $this->_bgp->setStatus("FAILED to match ".$name_parts[0]." against Manage Authors known last name: ".$aut_details['aut_lname']);
            }
          }
        }
        // No exact match above found        
        if ($percent < 1) {
          $percent = $this->matchAuthorNameByLev($authors[$i]['name'], $aut_details['aut_display_name'], $percent_1);      
          if ($percent == 1) {
            $exact_match_count++;
            $match_index = $i;
            $authors[$i]['match'] = $percent;
          } else {

            if( $this->_bgp ) {
              $this->_bgp->setStatus("FAILED to match by lev distance (".$percent." percent) ".$name_parts[0]." against Manager Authors display name words: ".implode("|",$aut_details['aut_display_name']));
            }


            $authors[$i]['match'] = $percent;
            // Attempt to match on other names for this author we know about
            foreach ($aut_alt_names as $aut_alt_name => $count) {
              $percent = $this->matchAuthorNameByLev($authors[$i]['name'], $aut_alt_name, $percent_1);
              if ($percent == 1) {
                $exact_match_count++;
                $match_index = $i;
                break;
              }
              $authors[$i]['match'] = $percent;
            }
          }
        }
      }
    }

    if ($exact_match_count == 1) {
      // One match found
      $rule1 = TRUE;
      $pos = $authors[$match_index]['pos'];
      $orig_authors[$pos]['aut_id'] = $aut_id;
      if ($known == TRUE) {
        // Nothing more to do, we have a match so update the author on the pub
        // with the found aut_id
        $message .= ' (known)';
        $this->replaceAuthors($orig_authors, $message);
        return array(TRUE, 'Inserted');
      }
    } else {
      // Multiple matches found
      if ($known == TRUE) {
        if( $this->_bgp ) {
          $this->_bgp->setStatus("FOUND TOO MANY (".$exact_match_count.") matches (so won't save any of them) for this author name: ".implode("|",$aut_details['aut_display_name']));
        }
        return array(FALSE, 'Multiple');
      }
    }

    // Step 2: Co-authored pubs
    $percent_2 = 0;
    if ($authors_count <= 10) {
      if (count($co_aut_ids) > 0) {
        $pids = $this->coAuthored($aut_id, $co_aut_ids, $keywords);
        $pid_count = count($pids);
        if ($pid_count > 0) {
          $percent_2 = 1 - (1 / $pid_count++);
          // To satisfy rule 2 we must have used keywords
          if ($keywords) {
            $rule2 = TRUE;
          }
        }
      }
    } else {
      // Too many authors to perform step 2
      if( $this->_bgp ) {
        $this->_bgp->setStatus("FOUND TOO MANY CO-AUTHORS (".$authors_count.") so can't use co-authored logic on author name: ".implode("|",$aut_details['aut_display_name']));
      }
    }

    // Step 3: Less than 10 authors on the pub
    if ($authors_count <= 10) {
      $rule3 = TRUE;
    }

    // Collate results
    $final_percent = (($percent_1*0.6) + ($percent_2*0.4));
    $matched = FALSE;
    if ($rule1 && $rule2 && $rule3) {
      $message .= ' (deterministic match)';
      $matched = TRUE;
    } else if ($final_percent >= $threshold) {
      $message .= ' (probablistic match)';
      $matched = TRUE;
    }

    if ($matched && $update) {
      $this->replaceAuthors($orig_authors, $message);
    }

    return array($matched, $final_percent, $aut_details, $orig_authors, $pids);
  }

  /**
   * Returns the pubs where author $aut_id has co-authored on pubs with authors in $aut_id_list.
   * Can optionally require there be shared keywords between this pub and co-authored pubs
   *
   * @param int   $aut_id
   * @param array $aut_id_list
   * @param array $keywords (optional)
   *
   *
   * @return array
   */
  function coAuthored($aut_id, $aut_id_list, $keywords=FALSE)
  {
    $log = FezLog::get();
    $db = DB_API::get();
     
    $sql =  "SELECT DISTINCT a1.rek_author_id_pid ";
    if ($keywords) {
      //$sql .= ", k1.rek_keywords ";
    }
    $sql .= "FROM " . APP_TABLE_PREFIX . "record_search_key_author_id a1 ".
            "JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id a2 ".
            "ON a1.rek_author_id_pid = a2.rek_author_id_pid ";

    if ($keywords) {
      $sql .= "LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_keywords k1 ".
              "ON a1.rek_author_id_pid=k1.rek_keywords_pid ";
    }
    $sql .= "WHERE a1.rek_author_id = ? AND a2.rek_author_id IN (".Misc::arrayToSQLBindStr($aut_id_list).") ";
    if ($keywords) {
      $sql .=  "AND k1.rek_keywords IN (".
               "SELECT k2.rek_keywords FROM " . APP_TABLE_PREFIX . "record_search_key_keywords k2 ".
               "WHERE k2.rek_keywords_pid=?)";
    }

    try {
      if ($keywords) {
        $params = array_merge(array($aut_id), $aut_id_list, array($this->pid));
      } else {
        $params = array_merge(array($aut_id), $aut_id_list);
      }
      $res = $db->fetchAll($sql, $params);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return FALSE;
    }
    return $res;
  }

  /**
   * Match $name with author in $aut_details using Levenschtein distance. A comparison
   * percentage is assigned to the $percent referenced variable if one is found in
   * this match that is higher than what is was previously set to
   *
   * @param string $name
   * @param array  $aut_details
   * @param float  $percent
   *
   * @return float Percentage for this match
   */
  function matchAuthorNameByLev($name, $name_to_match, &$percent)
  {
    $log = FezLog::get();
    
    $rpercent = 0;
    
    if ($name_to_match == $name) {
      // exact match
      $percent = 1;
      return 1;
    } else {      
      // An exact match without spaces, commas or full stops
      $accept_distance = 1;
      $pattern = '/[\s,.]/';
      $name_to_match = strtolower(preg_replace($pattern, '', $name_to_match));
      $name = strtolower(preg_replace($pattern, '', $name));
      $distance = levenshtein($name_to_match, $name);
      $_percent = 1 - ($distance / (max(strlen($name_to_match), strlen($name))));
      if ($distance < $accept_distance) {
        // matched within acceptable distance
        $percent = 1;
        return 1;
      }
    }
    if ($_percent > $percent) {
      $percent = $_percent;
    }
    if ($_percent > $rpercent) {
      $rpercent = $_percent;
    }
    

    return $rpercent;
  }

  /**
   * Returns an assoc array of authors and their author IDs for this record
   *
   * @return mixed
   */
  function getAuthors()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $authors = array();
    $stmt =  "SELECT a.rek_author as name, i.rek_author_id as aut_id
              FROM " . APP_TABLE_PREFIX . "record_search_key_author a
              LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id i
                 ON a.rek_author_order=i.rek_author_id_order AND a.rek_author_pid=i.rek_author_id_pid
              WHERE a.rek_author_pid=?
			  ORDER BY a.rek_author_order ASC";
    try {
      $res = $db->fetchAll($stmt, $this->pid, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return FALSE;
    }
    return $res;
  }

  /**
   * Replaces authors on a record
   *
   * @param array  $authors The list of authors to replace authors on this pub with
   * @param string $message A message about why the authors were replaced
   *
   * @return bool  TRUE if replaced OK. FALSE if not replaced.
   *
   * @access public
   */
  function replaceAuthors($authors_list, $message)
  {
    $log = FezLog::get();

    $newXML = "";

    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'MODS', TRUE);
    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    $field_node_list = $xpath->query("/mods:mods/mods:name");
    $count = $field_node_list->length;
    if ($count > 0) {
      for ($i = 0; $i < $count; $i++) {
        $collection_node = $field_node_list->item($i);
        $parent_node = $collection_node->parentNode;
        $parent_node->removeChild($collection_node);
      }
    }

    $mods = '<mods:name ID="%d" authority="%s">
               <mods:namePart type="personal">%s</mods:namePart>
               <mods:role>
                 <mods:roleTerm type="text">%s</mods:roleTerm>
               </mods:role>
             </mods:name>';
    $authors = '<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
    foreach ($authors_list as $author) {
      $authors .= sprintf($mods, $author['aut_id'], APP_ORG_NAME, $author['name'], 'author');
    }
    $authors .= '</mods:mods>';

    $authors_doc = new DOMDocument;
    $authors_doc->loadXML($authors);
    $author_nodes = $authors_doc->getElementsByTagName("name");

    $count = $author_nodes->length;
    if ($count > 0) {
      for ($i = 0; $i < $count; $i++) {
        $node = $doc->importNode($author_nodes->item($i), TRUE);
        $doc->documentElement->appendChild($node);
      }
    }
    $newXML = $doc->SaveXML();

    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", true
      );
      History::addHistory($this->pid, null, "", "", TRUE, $message);
      Record::setIndexMatchingFields($this->pid);
      return TRUE;
    } else {
      return FALSE;
    }
  }


  /**
   * Strips abstracts from a record
   *
   * @return bool  TRUE if stripped OK. FALSE if not stripped.
   *
   * @access public
   */
  function stripAbstract()
  {
    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'MODS', true);

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);

    $fieldNodeList = $xpath->query("/mods:mods/mods:abstract");

    if ($fieldNodeList->length == 0 ) {
      return false;
    }

    $collectionNode   = $fieldNodeList->item(0);
    $parentNode       = $collectionNode->parentNode;
    $parentNode->removeChild($collectionNode);

    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit"
      );
      $historyDetail = "Abstract was stripped from ESTI imported record";
      History::addHistory($this->pid, null, "", "", true, $historyDetail);      	
      Record::setIndexMatchingFields($this->pid);
      return true;
    }
    return false;
  }

  /**
   * updateFezMD_User
   * Used to assign this record to a user
   *
   * @access  public
   * @param  $key
   * @param  $value
   * @return  void
   */
  function updateFezMD_User($key, $value)
  {
    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD', true);
    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);
    $fieldNodeList = $xpath->query("//usr_id");
    if ($fieldNodeList->length > 0) {
      foreach ($fieldNodeList as $fieldNode) { // first delete all the existing user associations
        $parentNode = $fieldNode->parentNode;
        $parentNode->removeChild($fieldNode);
      }
    } else {
      $parentNode = $doc->lastChild;
    }
    $newNode = $doc->createElement('usr_id');
    $newNode->nodeValue = $value;
    $parentNode->insertBefore($newNode);
    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "FezMD", "A", "Fez Admin Metadata", $newXML, "text/xml", "inherit"
      );
      Record::setIndexMatchingFields($this->pid);
    }
  }

  /**
   * assignGroupFezMD
   * Used to assign this record to a group
   *
   * @access  public
   * @param  $key
   * @param  $value
   * @return  void
   */
  function updateFezMD_Group($key, $value)
  {

    $newXML = "";
    $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD', true);
    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);
    $fieldNodeList = $xpath->query("//grp_id");
    if ($fieldNodeList->length > 0) {
      foreach ($fieldNodeList as $fieldNode) { // first delete all the existing group associations
        $parentNode = $fieldNode->parentNode;
        Error_Handler::logError($fieldNode->nodeName.$fieldNode->nodeValue, __FILE__, __LINE__);
        $parentNode->removeChild($fieldNode);
      }
    } else {
      $parentNode = $doc->lastChild;
    }
    $newNode = $doc->createElement('grp_id');
    $newNode->nodeValue = $value;
    $parentNode->insertBefore($newNode);
    //		Error_Handler::logError($doc->SaveXML(),__FILE__,__LINE__);
    $newXML = $doc->SaveXML();
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue(
          $this->pid, "FezMD", "A", "Fez Admin Metadata", $newXML, "text/xml", "inherit"
      );
      Record::setIndexMatchingFields($this->pid);
    }
  }

  /**
   * Function can update a single xsdmf in the XML but doesn't work for sublooping elements.
   * @param integer $xsdmf_id the mapping to update
   * @param string $value what to set the element to
   * @param integer $idx the index of the item if this is a multiple item
   * @return boolean true on success, false on failure.
   */
  function setValue($xsdmf_id, $value, $idx)
  {
    $this->getDisplay();
    $this->display->getXSD_HTML_Match();
    $cols = $this->display->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
    // which datastream to get XML for?
    // first find the xdis id that the xsdmf_id matches in (not the base xdis_id since this will be in a
    // refered display)
    $xdis_id = $cols['xsdmf_xdis_id'];
    $xsd_id = XSD_Display::getParentXSDID($xdis_id);
    $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
    $dsID = $xsd_details['xsd_title'];
    if ($dsID == 'OAI DC') {
      $dsID = 'DC';
    }
    //Error_Handler::logError($dsID,__FILE__,__LINE__);
    $xsdmf_element = $cols['xsdmf_element'];
    $steps = explode('!', $xsdmf_element);
    // get rid of blank on the front
    array_shift($steps);
    $doc = DOMDocument::loadXML($xsd_details['xsd_file']);
    $xsd_array = array();
    Misc::dom_xsd_to_referenced_array($doc, $xsd_details['xsd_top_element_name'], $xsd_array, "", "", $doc);
    $sXml = Fedora_API::callGetDatastreamContents($this->pid, $dsID, true);
    if (!empty($sXml) && $sXml != false) {
      $doc = DOMDocument::loadXML($sXml);
      // it would be good if we could just do a xpath query here but unfortunately, the xsdmf_element
      // is missing information like namespaces and attribute '@' thing.
      if (
          $this->setValueRecurse(
              $value, $doc->documentElement, $steps, 
              $xsd_array[$xsd_details['xsd_top_element_name']], $idx
          )
      ) {
        Fedora_API::callModifyDatastreamByValue(
            $this->pid, $dsID, "A", "setValue", $doc->saveXML(), "text/xml", "inherit"
        );
        Record::setIndexMatchingFields($this->pid);
        return true;
      }
    } else {
      return false;
    }
  }

  function setValueRecurse($value, $node, $remaining_steps, $xsd_array, $vidx, $current_idx=0)
  {
    $next_step = array_shift($remaining_steps);
    $next_xsd_array = $xsd_array[$next_step];
    $theNode = null;
    if (isset($next_xsd_array['fez_nodetype']) && $next_xsd_array['fez_nodetype'] == 'attribute') {
      $node->setAttribute($next_step, $value);
      return true;
    } else {
      $use_idx = false;  // should we look the element that matches vidx?  Only if this is the end of the path
      $att_step = $remaining_steps[0];
      $att_xsd = $next_xsd_array[$att_step];
      if (isset($att_xsd['fez_nodetype']) && $att_xsd['fez_nodetype'] == 'attribute') {
        $use_idx = true;
      }
      if (count($remaining_steps) == 0) {
        $use_idx = true;
      }
      $idx = 0;
      foreach ($node->childNodes as $childNode) {
        // remove namespace
        $next_step_name = $next_step;
        if (!strstr($next_step_name, '!dc:')) {
          $next_step_name = preg_replace('/![^:]+:/', '!', $next_step_name);
        }
        if ($childNode->nodeName == $next_step_name) {
          if ($use_idx) {
            if ($idx == $vidx) {
              $theNode = $childNode;
              break;
            }
            $idx++;
          } else {
            $theNode = $childNode;
            break;
          }
        }
      }
    }
    if (is_null($theNode)) {
      $theNode = $node->ownerDocument->createElement($next_step);
      $node->appendChild($theNode);
    }
    if (count($remaining_steps)) {
      if ($this->setValueRecurse($value, $theNode, $remaining_steps, $next_xsd_array, $vidx, $idx)) {
        return true;
      }
    } else {
      if (!empty($value)) {
        $theNode->nodeValue = $value;
      } else {
        $theNode->parentNode->removeChild($theNode);
      }
      return true;
    }
    return false;
  }

  /**
   * getDisplay
   * Get a display object for this record
   *
   * @access  public
   * @return  array $this->details The display of the object, or null
   */
  function getDisplay()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->getXmlDisplayId();
    if (!empty($this->xdis_id)) {
      if (is_null($this->display)) {
        $this->display = new XSD_DisplayObject($this->xdis_id);
        $this->display->getXSD_HTML_Match();
      }
      return $this->display;
    } else {
      // if it has no xdis id (display id) log an error and return a null
      $log->err(
          array(
              "The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object ".
              "is currently in an erroneous state.", __FILE__, __LINE__
          )
      );
      return null;
    }
  }

  function getDocumentType()
  {
    /*if(!$this->display)
    {
        $this->getXmlDisplayIdUseIndex();
    }*/
    $this->getDisplay();
    return $this->display->getTitle();
  }

  /**
   * getDetails
   * Users a more object oriented approach with the goal of storing query results so that we don't need to make
   * so many queries to view a record.
   *
   * @access  public
   * @return  array $this->details The details of the object
   */
  function getDetails($dsID = "", $xdis_id = "")
  {
    
    $log = FezLog::get();
    $db = DB_API::get();

    if (is_null($this->details) || $dsID != "") {
      // Get the Datastreams.
      if ($xdis_id == "") {
        $this->getDisplay();
      } else {
        $this->display = new XSD_DisplayObject($xdis_id);
        $this->display->getXSD_HTML_Match();
      }
      if ($this->display) {
        if ($dsID != "") {
          $this->details = $this->display->getXSDMF_Values_Datastream($this->pid, $dsID, $this->createdDT);
        } else {
          $this->details = $this->display->getXSDMF_Values($this->pid, $this->createdDT);
        }
      } else {
        $log->err(
            array(
                "The PID ".$this->pid." has an error getting it's display details. This object ".
                " is currently in an erroneous state.", __FILE__, __LINE__
            )
        );
      }
    }

    return $this->details;
  }


  /**
   * Clear the cached details in this record.  Used when the record has been altered to force
   * details to be reparsed from the fedora object.
   */
  function clearDetails()
  {
    $this->details = null;
  }

  /**
   * getFieldValueBySearchKey
   * Get the value or values of a metadata field that matches a given search key
   *
   * @access  public
   * @param $sek_title string - The name of the search key to get the field value for, e.g. 'Title'
   * @return  array $this->details[$xsdmf_id] The Dublin Core title of the object
   */
  function getFieldValueBySearchKey($sek_title)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->getDetails();

    if (!empty($this->xdis_id)) {
      $sek_id = Search_Key::getID($sek_title);
      if (!$sek_id) {
        return null;
      }
      $res = array();

      foreach ($this->display->xsd_html_match->getMatchCols() as $xsdmf ) {
        if ($xsdmf['xsdmf_sek_id'] == $sek_id) {
          $res[] = $this->details[$xsdmf['xsdmf_id']];
        }
      }
      return $res;
    } else {
      // if it has no xdis id (display id) log an error and return a null
      $log->err(
          array(
              "The PID ".$this->pid." does not have an display id (FezMD->xdis_id). ".
              "This object is currently in an erroneous state.", __FILE__, __LINE__
          )
      );
      return null;
    }
  }

  /**
   * getTitle
   * Get the dc:title for the record
   *
   * @access  public
   * @return  array $this->details[$xsdmf_id] The Dublin Core title of the object
   */
  function getTitle()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->title = Record::getTitleFromIndex($this->pid);
    if (empty($this->title)) {
      $log->debug('Title is empty');
      $this->getDetails();
      $this->getXmlDisplayId();
      if (!empty($this->xdis_id)) {
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID("!dc:title");
        //$xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title');
        $this->title = $this->details[$xsdmf_id];
      } else {
        // if it has no xdis id (display id) log an error and return a null
        $log->err(
            array(
                "Fez cannot display PID " . $this->pid . 
                " because it does not have a display id (FezMD/xdis_id).", __FILE__, __LINE__
            )
        );
        return null;
      }
    }
    return $this->title;
  }

  /**
   * getDCType
   * Get the dc:type for the record
   *
   * @access  public
   * @return  array $this->details[$xsdmf_id] The Dublin Core type of the object
   */
  function getDCType()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->getDetails();
    $this->getXmlDisplayId();
    if (!empty($this->xdis_id)) {
      $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:type');
    } else {
      // if it has no xdis id (display id) log an error and return a null
      $log->err(
          array(
              "The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object ".
              "is currently in an erroneous state.", __FILE__, __LINE__
          )
      );
      return null;
    }
    return $this->details[$xsdmf_id];
  }

  function getXSDMF_ID_ByElement($xsdmf_element)
  {
    $this->getDisplay();
    $this->display->getXSD_HTML_Match();
    return $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID($xsdmf_element);
  }

  /**
   * getDetailsByXSDMF_element
   *
   * Returns the value of an element in a datastream addressed by element
   *
   * @param string $xsdmf_element - The path to the XML element in a datastream.
   *      Use XSD_HTML_Match::escapeXPath to convert an xpath - /oai_dc:dc/dc:title to an xsdmf_element string !dc:title
   * @param string $xsdmf_title - option field to use when xsdmf_element is ambiguous
   * @returns mixed - Array of values or single value for each element match in XML tree
   */
  function getDetailsByXSDMF_element($xsdmf_element, $xsdmf_title="")
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->getDetails();

    $this->getXmlDisplayId();
    if (!empty($this->xdis_id)) {
      $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID($xsdmf_element, $xsdmf_title);
      return @$this->details[$xsdmf_id];
    } else {
      // if it has no xdis id (display id) log an error and return a null
      $log->err(
          array(
              "The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object is ". 
              "currently in an erroneous state.", __FILE__, __LINE__
          )
      );
      return null;
    }
  }

  function getDetailsByXSDMF_ID($xsdmf_id)
  {
    $this->getDetails();
    return @$this->details[$xsdmf_id];
  }

  /**
   * getXSDMFDetailsByElement
   *
   * Returns XSDMF values to describe how the element should be treated in a HTML form or display
   *
   * @param string $xsdmf_element - The path to the XML element in a datastream.
   *      Use XSD_HTML_Match::escapeXPath to convert an xpath - /oai_dc:dc/dc:title to an xsdmf_element string !dc:title
   * @returns array - Keypairs from the XSDMF table for the element on this record and record type to
   *      describe how the element should be treated in a HTML form or display.
   */
  function getXSDMFDetailsByElement($xsdmf_element)
  {
    $this->getDisplay();
    $this->display->getXSD_HTML_Match();
    return $this->display->xsd_html_match->getDetailsByElement($xsdmf_element);
  }

  /**
   * isCollection
   * Is the record a Collection
   *
   * @access  public
   * @return  boolean
   */
  function isCollection()
  {
    return ($this->getRecordType() == 2) ? true : false;
  }

  /**
   * isCommunity
   * Is the record a Community
   *
   * @access  public
   * @return  boolean
   */
  function isCommunity()
  {
    return ($this->getRecordType() == 1) ? true : false;
  }


  /**
   * function getParents()
   * getParents
   * Get the parent pids of an object
   *
   * @access  public
   * @return  array list of parents
   */
  function getParents()
  {
    if (!$this->record_parents) {
      $this->record_parents = Record::getParents($this->pid);
    }
    return $this->record_parents;
  }

  function getWorkflowsByTrigger($trigger)
  {
    $this->getParents();
    $triggers = WorkflowTrigger::getListByTrigger($this->pid, $trigger);
    foreach ($this->record_parents as $ppid) {
      $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger($ppid, $trigger));
    }
    // get defaults
    $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger(-1, $trigger));
    return $triggers;
  }

  function getWorkflowsByTriggerAndRET_IDAndXDIS_ID($trigger, $ret_id, $xdis_id, $strict=false)
  {
    $this->getParents();
    $triggers = WorkflowTrigger::getListByTriggerAndRET_IDAndXDIS_ID($this->pid, $trigger, $ret_id, $xdis_id, $strict);
    foreach ($this->record_parents as $ppid) {
      $triggers = array_merge(
          $triggers, WorkflowTrigger::getListByTriggerAndRET_IDAndXDIS_ID($ppid, $trigger, $ret_id, $xdis_id, $strict)
      );
    }
    // get defaults
    $triggers = array_merge(
        $triggers, WorkflowTrigger::getListByTriggerAndRET_IDAndXDIS_ID(-1, $trigger, $ret_id, $xdis_id, $strict)
    );
    return $triggers;
  }


  function getWorkflowsByTriggerAndXDIS_ID($trigger, $xdis_id, $strict=false)
  {
    $this->getParents();
    $triggers = WorkflowTrigger::getListByTriggerAndXDIS_ID($this->pid, $trigger, $xdis_id, $strict);
    foreach ($this->record_parents as $ppid) {
      $triggers = array_merge(
          $triggers, WorkflowTrigger::getListByTriggerAndXDIS_ID($ppid, $trigger, $xdis_id, $strict)
      );
    }
    // get defaults
    $triggers = array_merge($triggers, WorkflowTrigger::getListByTriggerAndXDIS_ID(-1, $trigger, $xdis_id, $strict));
    return $triggers;
  }

  function getWorkflowsByTriggerAndRET_ID($trigger, $ret_id, $strict=false)
  {
    $this->getParents();
    $triggers = WorkflowTrigger::getListByTriggerAndRET_ID($this->pid, $trigger, $ret_id, $strict);
    foreach ($this->record_parents as $ppid) {
      $triggers = array_merge(
          $triggers, WorkflowTrigger::getListByTriggerAndRET_ID($ppid, $trigger, $ret_id, $strict)
      );
    }
    // get defaults
    $triggers = array_merge($triggers, WorkflowTrigger::getListByTriggerAndRET_ID(-1, $trigger, $ret_id, $strict));
    return $triggers;
  }

  function getFilteredWorkflows($options)
  {
    $this->getParents();
    $triggers = WorkflowTrigger::getFilteredList($this->pid, $options);
    foreach ($this->record_parents as $ppid) {
      $triggers = array_merge($triggers, WorkflowTrigger::getFilteredList($ppid, $options));
    }
    // get defaults
    $triggers = array_merge($triggers, WorkflowTrigger::getFilteredList(-1, $options));
    return $triggers;
  }


  function getChildrenPids($clearcache=false, $searchKey='isMemberOf')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $pid = $this->pid;
    $sek_title = Search_Key::makeSQLTableName($searchKey);
    $stmt = "SELECT ".APP_SQL_CACHE."
					m1.rek_".$sek_title."_pid
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1
				 WHERE m1.rek_".$sek_title." = ".$db->quote($pid);
    try {
      $res = $db->fetchCol($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return $res;
  }

  function export()
  {
    return Fedora_API::export($this->pid);
  }

  function getObjectXML()
  {
    return Fedora_API::getObjectXMLByPID($this->pid);
  }

  function getDatastreams($dsState='A')
  {
    return Fedora_API::callGetDatastreams($this->pid, null, $dsState);
  }
  
  function checkExists()
  {
    return Fedora_API::objectExists($this->pid);
  }
  
  function getDatastreamContents($dsID, $filehandle=null)
  {
    return Fedora_API::callGetDatastreamContents($this->pid, $dsID, false, $filehandle);
  }
  
  function setIndexMatchingFields($opts=null)
  {
    $log = FezLog::get();
    
    if(is_null($opts))
    {
        // careful what you do with the record object - don't want to use the index while reindexing
        $pid = $this->pid;
        $xdis_id = $this->getXmlDisplayId();
        if (!is_numeric($xdis_id)) {
          $xdis_id = XSD_Display::getXDIS_IDByTitle('Generic Document');
        }
    }
    else 
    {
        $pid = $opts['pid'];
        $xdis_id = $opts['xdis_id'];
    }
    
    $display = new XSD_DisplayObject($xdis_id);
    $xsdmf_array = $display->getXSDMF_Values($pid, null, true);
    
    $searchKeyData = array();

    foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
      $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
      if ($xsdmf_details['xsdmf_sek_id'] != "") {
        $sekDetails = Search_Key::getBasicDetails($xsdmf_details['xsdmf_sek_id']);

        if ($sekDetails['sek_data_type'] == 'int' && $sekDetails['sek_html_input'] == 'checkbox') {
          if ($xsdmf_value == 'on') {
            $xsdmf_value = 1;
          } else {
            $xsdmf_value = 0;
          }
        }

        if ($sekDetails['sek_data_type'] == 'date') {
          if (!empty($xsdmf_value)) {
            if (is_numeric($xsdmf_value) && strlen($xsdmf_value) == 4) {
              // It appears we've just been fed a year. We'll pad this,
              // so it can be added to the index.
              $xsdmf_value = $xsdmf_value . "-01-01 00:00:00";
            } elseif (strlen($xsdmf_value) == 7) {
              // YYYY-MM. We could arguably write some better string inspection stuff here,
              // but this will do for now.
              $xsdmf_value = $xsdmf_value . "-01 00:00:00";
            }
            // Looks like a regular fully-formed date.
            $xsdmf_value = strtotime($xsdmf_value);
            //$xsdmf_value = date('Y-m-d T', $xsdmf_value);
            $xsdmf_value = date('Y-m-d', $xsdmf_value);

            if (
                $xsdmf_value == "0000-01-01 00:00:00" || $xsdmf_value == "0000-00-00 00:00:00" || 
                $xsdmf_value == "0-01-01 00:00:00"
            ) {
              $xsdmf_value = "NULL";
            }
          } else {
            $xsdmf_value = "NULL";
          }
        }

        if (@empty($searchKeyData[$sekDetails['sek_relationship']][$sekDetails['sek_title_db']]['xsdmf_value'])) {
          $searchKeyData[$sekDetails['sek_relationship']][$sekDetails['sek_title_db']] = array(
              "xsdmf_id" => $xsdmf_id,
              "xsdmf_value" => $xsdmf_value,
          );
        }
      }
    }
    
    Record::removeIndexRecord($pid, false); // clean out the SQL index, but do not remove from Solr, 
                                                // the solr entry will get updated in updateSearchKeys
                                                
    Record::updateSearchKeys($pid, $searchKeyData);
    if (APP_FEDORA_BYPASS == 'ON') {
      Record::updateSearchKeys($pid, $searchKeyData, true); // Update shadow tables
    }

  }
  
  //To delete ??
  function getXSDMFByTitle($pid, $xdis_id)
  {
      $db = DB_API::get();
      
      $sql = "SELECT mf.xsdmf_id FROM fez_xsd_display_matchfields mf, fez_search_key sk, fez_record_search_key rsk" 
            . " WHERE sk.sek_id = mf.xsdmf_sek_id AND mf.xsdmf_id = rsk.rek_display_type_xsdmf_id "
            . "AND sk.sek_title = 'Display Type' "
            . "AND rsk.rek_pid = :pid"
            . "AND rsk.rek_display_type = :disp";
      $row = $db->fetchRow($sql, array(':pid' => $pid, ':disp' => $xdis_id));
      
      return $row["xsdmf_id"];
  }
  
  /**
   * Convert xsd_display_fields array in the POST array 
   * to an array suitable for insertion or update of a record.
   * @param <array> $xsdFields
   */
  function setDisplayFields($xsdFields)
  {
      $db = DB_API::get();
      $log = FezLog::get();
      $xsdmf_ids = array_keys($xsdFields);
      
      $sekFields = array();
      
      try
      {
          $sql = "SELECT mf.xsdmf_id, TRIM(LOWER(REPLACE(sk.sek_title,\" \",\"_\"))) AS sek_title, "
          . "sk.sek_relationship, sk.sek_cardinality FROM " . APP_TABLE_PREFIX . "search_key sk, " 
          . APP_TABLE_PREFIX . "xsd_display_matchfields mf WHERE sk.sek_id = "
          . "mf.xsdmf_sek_id AND mf.xsdmf_id IN (?)";
          
          $query = str_replace('?', substr(str_repeat('?, ', count($xsdmf_ids)), 0, -2), $sql);
          
          $stmt = $db->query($query, $xsdmf_ids);
          $fields = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
          $log->err($e->getMessage());
      }
      
      foreach($fields as $field)
      {
          if(is_array($xsdFields[$field['xsdmf_id']]) && isset($xsdFields[$field['xsdmf_id']]['Year']))
          {
              $xsdFields[$field['xsdmf_id']] = Misc::MySQLDate($xsdFields[$field['xsdmf_id']]);
          }
          
          //1 - 1 relationship values should not have array values.
          if($field['sek_cardinality'] == '0' && is_array($xsdFields[$field['xsdmf_id']]))
          {
              $valueKeys = array_keys($xsdFields[$field['xsdmf_id']]); //Not all keys are numeric.
              $xsdFields[$field['xsdmf_id']] = $xsdFields[$field['xsdmf_id']][$valueKeys[0]];
          }
          
          $sekFields[$field['sek_relationship']][$field['sek_title']] = array(
          	'xsdmf_id' => $field['xsdmf_id'], 
          	'xsdmf_value' => $xsdFields[$field['xsdmf_id']]);
      }
      
      return $sekFields;
  }

  /**
   * copyToNewPID
   * This makes a copy of the fedora object with the current PID to a new PID.  The getNextPID call on fedora is
   * used to get the new PID. All datastreams are extracted from the original object and reingested to the new object.
   * Premis history is not brought across, the first entry in the new premis history identifies the PID of the
   * source object.   The $new_xdis_id specifies a change of content model.  If $new_xdis_id is null, then the
   * xdis_id of the source object is used.  If $is_succession is true, the RELS-EXT will have a isSuccessor element
   * pointing back to the sourec object.
   * @param integer $new_xdis_id - optional new content model
   * @param boolean $is_succession - optional link back to original
   * @return string - the new PID for success, false for failure.  Calls Error_Handler::logError if there is a problem.
   */
  function copyToNewPID(
      $new_xdis_id = null, $is_succession = false, $clone_attached_datastreams=false, $collection_pid=null
  )
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($this->pid)) {
      return false;
    }
    if (empty($new_xdis_id)) {
      $new_xdis_id = $this->getXmlDisplayIdUseIndex();
    }
    $pid = $this->pid;
    $new_pid = Fedora_API::getNextPID();
    $new_xml = "";
    // need to get hold of a copy of the fedora XML, and substitute the PIDs in it then ingest it.
    $xml_str = Fedora_API::getObjectXMLByPID($pid);
    $xml_str = str_replace($pid, $new_pid, $xml_str);  // change to new pid
    // strip off datastreams - we'll add them later.  This gets rid of the internal fedora audit datastream
    $doc = DOMDocument::loadXML($xml_str);
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('foxml', 'info:fedora/fedora-system:def/foxml#');
    $xpath->registerNamespace('fedoraxsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xpath->registerNamespace('audit', 'info:fedora/fedora-system:def/audit#');
    $nodes = $xpath->query('/foxml:digitalObject/foxml:datastream');
    foreach ($nodes as $node) {
      $node->parentNode->removeChild($node);
    }

    if (APP_FEDORA_VERSION == "3") { // Fedora 3 doesn't have the VERSION attribute so remove it or it will cause an error
      $new_xml = $doc->saveXML();
      $nodes = $xpath->query('/foxml:digitalObject');
      foreach ($nodes as $node) {
        $node->removeAttribute('VERSION');
      }
    }
    $new_xml = $doc->saveXML();

    if (APP_FEDORA_VERSION == "3") {
      Fedora_API::callIngestObject($new_xml, $new_pid);
    } else {
      Fedora_API::callIngestObject($new_xml);
    }


    $datastreams = Fedora_API::callGetDatastreams($pid); // need the full get datastreams to get the controlGroup etc
    if (empty($datastreams)) {
      $log->err(
          array(
              "The PID ".$pid." doesn't appear to be in the fedora repository, perhaps it was not ingested correctly.".
                    "Please let the Fez admin know so that the Fez index can be repaired.", __FILE__, __LINE__
          )
      );
      return false;
    }

    // exclude these prefixes if we're not cloning the binaries
    $exclude_prefix = array('presmd','thumbnail','web','preview', 'stream');

    foreach ($datastreams as $ds_key => $ds_value) {
      if (!$clone_attached_datastreams) {
        // don't process derived datastreams if we're not copying the binaries
        if (in_array(substr($ds_value['ID'], 0, strpos($ds_value['ID'], '_')), $exclude_prefix)) {
          continue;
        }
      }
      switch ($ds_value['ID']) {
        case 'DC':
          $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
          Fedora_API::callModifyDatastreamByValue(
              $new_pid, $ds_value['ID'], $ds_value['state'],
              $ds_value['label'], $value, $ds_value['MIMEType'], $ds_value['versionable']
          );
          
          if (!Misc::in_multi_array("MODS", $datastreams)) {
            // transform the DC into a MODS datastream and attach it
            $dc_to_mods_xsl = APP_INC_PATH . "xslt/dc_to_mods.xsl";
            $xsl_dom = DOMDocument::load($dc_to_mods_xsl);
            $dc_dom = DOMDocument::loadXML($value);
            // transform the DC to MODS with the XSLT
            $proc = new XSLTProcessor();
            $proc->importStyleSheet($xsl_dom);
            $transformResult = $proc->transformToXML($dc_dom);
            $transformResult = self::clearMODSIdentifiers($transformResult);
            Fedora_API::getUploadLocation(
                $new_pid, "MODS", $transformResult, "Metadata Object Description Schema", 
                "text/xml", "X", "MODS", 'true'
            );
          }
            break;
        case 'BookMD':
            break;

        case 'FezMD':
          // let's fix up a few things in FezMD
          $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
          $doc = DOMDocument::loadXML($value);
          XML_Helper::setElementNodeValue(
              $doc, '/FezMD', 'created_date',
              Date_API::getFedoraFormattedDateUTC()
          );
          XML_Helper::setElementNodeValue(
              $doc, '/FezMD', 'updated_date',
              Date_API::getFedoraFormattedDateUTC()
          );
          XML_Helper::setElementNodeValue($doc, '/FezMD', 'depositor', Auth::getUserID());
          XML_Helper::setElementNodeValue($doc, '/FezMD', 'xdis_id', $new_xdis_id);
          $value = $doc->saveXML();
          Fedora_API::getUploadLocation(
              $new_pid, $ds_value['ID'], $value, $ds_value['label'],
              $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']
          );
            break;
        case 'RELS-EXT':
          // set the successor thing in RELS-EXT
          $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
          $value = str_replace($pid, $new_pid, $value);
          if ($is_succession || !empty($collection_pid)) {
            $doc = DOMDocument::loadXML($value);
            //    <rel:isDerivationOf rdf:resource="info:fedora/MSS:379"/>
            if ($is_succession) {
              $node = XML_Helper::getOrCreateElement(
                  $doc, '/rdf:RDF/rdf:description', 'rel:isDerivationOf',
                  array(
                      'rdf'=>"http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                      'rel'=>"info:fedora/fedora-system:def/relations-external#"
                  )
              );
              $node->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", 'resource', $pid);
            }
            if (!empty($collection_pid)) {
              $node = XML_Helper::getOrCreateElement(
                  $doc, '/rdf:RDF/rdf:description', 'rel:isMemberOf',
                  array(
                      'rdf'=>"http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                      'rel'=>"info:fedora/fedora-system:def/relations-external#"
                  )
              );
              $node->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", 'resource', $collection_pid);
            }
            $value = $doc->saveXML();
          }
          Fedora_API::getUploadLocation(
              $new_pid, $ds_value['ID'], $value, $ds_value['label'],
              $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']
          );
            break;
        default:
          if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'X') {
            $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
            $value = str_replace($pid, $new_pid, $value);
            if ($ds_value['ID'] == 'MODS')
            $value = self::clearMODSIdentifiers($value);
            Fedora_API::getUploadLocation(
                $new_pid, $ds_value['ID'], $value, $ds_value['label'],
                $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']
            );
          } else if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M'
          && $clone_attached_datastreams) {
            $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
            Fedora_API::getUploadLocation(
                $new_pid, $ds_value['ID'], $value, $ds_value['label'],
                $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']
            );
          } else if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'R'
          && $clone_attached_datastreams) {
            $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
            Fedora_API::callAddDatastream(
                $new_pid, $ds_value['ID'], $value, $ds_value['label'],
                $ds_value['state'], $ds_value['MIMEType'], $ds_value['controlGroup'], $ds_value['versionable']
            );
          }
            break;
      }
    }
    Record::setIndexMatchingFields($new_pid);

    return $new_pid;
  }

  /**
   * Clears any mods:identifiers from the xml string or object
   *
   * @param string $xmlString the xml to modify
   * @return string
   */
  public function clearMODSIdentifiers($xmlString)
  {
    // load xml document
    $xml = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($xml);
    $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');

    // clear the mods:identifier fields but leave them in the xml
    $nodes = $xpath->query('/mods:mods/mods:identifier');
    foreach ($nodes as $node) {
      $node->nodeValue = '';
    }
    $newXml = $xml->saveXML();

    // remove <?xml version="1.0" line coming from saveXML function
    $firstNewLine = strpos($newXml, "\n");
    $newXml = substr($newXml, $firstNewLine + 1, strlen($newXml));

    return $newXml;
  }

  /**
   * Generate a string which is a citation for this record.  Uses a citation template.
   */
  function getCitation()
  {
    $details = $this->getDetails();
    $xsdmfs = $this->display->xsdmf_array;

    return Citation::renderCitation($this->xdis_id, $details, $xsdmfs);
  }
  /**
   * Mark the fedora state of the record as deleted.  This keeps the record around in case we want to undelete it
   * later. We tell the Fez indexer not to index Fedora Deleted objects.
   */
  function markAsDeleted()
  {
    return Record::markAsDeleted($this->pid);
  }

  /**
   * Mark the fedora state of the record as active.  Also restores the fez index of the object.
   */
  function markAsActive($do_index = true)
  {
    return Record::markAsActive($this->pid, $do_index);
  }

  function isDeleted()
  {
    return Record::isDeleted($this->pid);
  }



  function getLock($context=self::CONTEXT_NONE, $extra_context=null)
  {
    return RecordLock::getLock($this->pid, Auth::getUserID(), $context, $extra_context);
  }

  function releaseLock()
  {
    return RecordLock::releaseLock($this->pid);
  }

  function getLockOwner()
  {
    return RecordLock::getOwner($this->pid);
  }

  function isLocked()
  {
    return RecordLock::getOwner($this->pid) > 0 ? true : false;
  }

    /**
     * A static method that returns an array of fields that are to be displayed on the Spyglass hover next to publication title.
     * The conditions rules for Spyglass Fields:
     * - The XSD field should have setting "Show In View Details" = OFF
     * - The XSD field's title should match the expected fields specified on $searchFields.
     *
     * How to add more fields to the spyglass hover:
     * - Insert the field's title on $searchFields array in an expected display order.
     *
     * Example of usage:
     * <code>
     * <?php
     *  RecordGeneral::getSpyglassHoverFields($xsd_display_fields);
     * ?>
     * </code>
     *
     * Example usage on template rendering: view_inverse_metadata.tpl.html
     *
     * @param Array $xsd_display_fields An array of XSD display fields.
     * @return Array $showFields An array of filtered XSD fields to be displayed on spyglass hover.
     */
    public function getSpyglassHoverFields($xsd_display_fields = array(), $details = array())
    {

        // List of display fields title in ORDER.
        $searchFields = array('ISI LOC', 'Scopus ID', 'Scopus Doc Type', 'WoK Doc Type', 'Refereed?', 'HERDC Notes', 'eSpace Follow-up Flags', 'IMU Follow-up Flags');

        $spyglassFields = array();

        foreach ($xsd_display_fields as $field) {
            // Check if this XSD field match our search fields
            $matchedIndex = array_search($field['xsdmf_title'], $searchFields);

            if ($field['xsdmf_show_in_view'] == 0 && $matchedIndex !== false) {
                $spyglassFields[$matchedIndex] = $field;

                // Assign field's value, if any
                $spyglassFields[$matchedIndex]['value'] = RecordGeneral::getSpyglassHoverValue($field, $details);
            }
        }
        return $spyglassFields;
    }

    /**
     * Returns the value for an XSD field for Spyglass hover display.
     *
     * @param Array $field XSD field array
     * @param Array $details Record details
     * @return String Value of the XSD field or empty if not found.
     */
    public function getSpyglassHoverValue($field = array(), $details = array())
    {
        $value = '';
        if (isset($details[$field['xsdmf_id']])) {
            $value = $details[$field['xsdmf_id']];
        }
        return $value;
    }
}
