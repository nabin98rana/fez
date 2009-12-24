<?php
include_once(APP_INC_PATH.'class.record_general.php');
/**
 * RecordObject
 * Manages the interface to the database and fedora for records.
 * Stores local copies of record properties to save multiple accesses to the database.
 */
class RecordObject extends RecordGeneral
{
	var $created_date;
	var $updated_date;
	var $depositor;
	var $assign_grp_id;
	var $assign_usr_id;
	var $file_downloads; //for statistics of file datastream downloads from eserv.php
	var $default_xdis_id = 5;
	var $status;



	function RecordObject($pid=null, $createdDT=null)
	{
		RecordGeneral::RecordGeneral($pid, $createdDT);
	}

	/**
	 * getXmlDisplayId
	 * Retrieve the display id for this record
	 */
	function getObjectAdminMD() 
	{
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
		if (isset($xdis_array['created_date'][0])) {
			$this->created_date = $xdis_array['created_date'][0];
		} else {
			$this->created_date = NULL;
		}
		if (isset($xdis_array['updated_date'][0])) {
			$this->updated_date = $xdis_array['updated_date'][0];
		} else {
			$this->updated_date = NULL;
		}
		if (isset($xdis_array['depositor'][0])) {
			$this->depositor = $xdis_array['depositor'][0];
		} else {
			$this->depositor = NULL;
		}
		if (isset($xdis_array['grp_id'][0])) {
			$this->assign_grp_id = $xdis_array['grp_id'][0];
		} else {
			$this->assign_grp_id = NULL;
		}
		if (isset($xdis_array['usr_id'][0])) {
			if (!is_array($this->assign_usr_id)) {
				$this->assign_usr_id = array();
			}
			foreach ($xdis_array['usr_id'] as $assign_usr_id) {
				array_push($this->assign_usr_id, $assign_usr_id);
			}
		} else {
			$this->assign_usr_id = array();
		}
		if (isset($xdis_array['sta_id'][0])) {
			$this->status = $xdis_array['sta_id'][0];
		} else {
			$this->status = NULL;
		}
	}

	/**
	 * updateAdminDatastream
	 * Used to associate a display for this record
	 *
	 * @access  public
	 * @param  integer $xdis_id The XSD Display ID of the object
	 * @return  void
	 */
	function updateAdminDatastream($xdis_id) 
	{
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
		$this->xdis_id = $xdis_id;
		$newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
		$foundElement = false;
		foreach ($xdis_array as $xkey => $xdata) {
			foreach ($xdata as $xinstance) {
				if ($xkey == "xdis_id") {
					$foundElement = true;
					$newXML .= "<".$xkey.">".$this->xdis_id."</".$xkey.">";
				} elseif ($xinstance != "") {
					$newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
				}
			}
		}
		if ($foundElement != true) {
			$newXML .= "<xdis_id>".$this->xdis_id."</xdis_id>";
		}
		$newXML .= "</FezMD>";
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", "inherit");
			/*			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!xdis_id", 15);
			 Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			 Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, $this->xdis_id);
			 */
			$this->setIndexMatchingFields();
		}
	}

	/**
	 * fedoraInsertUpdate
	 * Process a submitted record insert or update form
	 *
	 * @access  public
	 * @return  void
	 */
	function fedoraInsertUpdate($exclude_list=array(), $specify_list=array(), $params = array())
	{
		$log = FezLog::get();
		
		if (!empty($params)) {
			// dirty double hack as this function and all the ones it calls assumes this is
			// to do with a form submission
			$_POST = $params;
		}

		// If pid is null then we need to ingest the object as well
		// otherwise we are updating an existing object
		$ingestObject = false;
		$existingDatastreams = array();
		if (empty($this->pid)) {
			$this->pid = Fedora_API::getNextPID();
			$ingestObject = true;
			$this->created_date = Date_API::getFedoraFormattedDateUTC();
			$this->updated_date = $this->created_date;
			$this->depositor = Auth::getUserID();
			$this->assign_usr_id = array(Auth::getUserID());
			$existingDatastreams = array();
		} else {
			$existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
			if (APP_VERSION_UPLOADS_AND_LINKS != "ON" && !in_array("FezACML", $specify_list)) {
				Misc::purgeExistingLinks($this->pid, $existingDatastreams);
			}
			$this->getObjectAdminMD();
			if (empty($this->created_date)) {
				$this->created_date = Date_API::getFedoraFormattedDateUTC();
			}
			$this->updated_date = Date_API::getFedoraFormattedDateUTC();
			$this->getXmlDisplayId();
		}
		$pid = $this->pid;


		if (empty($this->xdis_id)) {
			$this->xdis_id = $_POST["xdis_id"];
		}
		$xdis_id = $this->xdis_id;
		$this->getDisplay();
		$display = &$this->display;
		list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema)
		= $display->getXsdAsReferencedArray();
		$xmlObj = '<?xml version="1.0"?>'."\n";
		$xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
		$xmlObj .= $xml_schema;
		$xmlObj .= ">\n";
		// @@@ CK - 6/5/2005 - Added xdis so xml building could search using the xml display ids
		$indexArray = array();

		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $this->created_date, $this->updated_date, $this->depositor, $this->assign_usr_id, $this->assign_grp_id);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";

		$datastreamTitles = $display->getDatastreamTitles($exclude_list, $specify_list);
		Record::insertXML($pid, compact('datastreamTitles', 'exclude_list', 'specify_list', 'xmlObj', 'indexArray', 'existingDatastreams', 'xdis_id'), $ingestObject);
		$this->clearDetails();  // force the details to be refreshed from fedora.
		return $pid;
	}

	function getIngestTrigger($mimetype)
	{
		$this->getXmlDisplayId();
		$trigger = WorkflowTrigger::getIngestTrigger($this->pid, $this->xdis_id, $mimetype);
		if (!$trigger) {
			$this->getParents();
			if (is_array($this->record_parents)) {
				foreach ($this->record_parents as $ppid) {
					$trigger = WorkflowTrigger::getIngestTrigger($ppid, $this->xdis_id, $mimetype);
					if ($trigger) {
						break;
					}
				}
			}
			if (!$trigger) {
				// get defaults
				$trigger = WorkflowTrigger::getIngestTrigger(-1, $this->xdis_id, $mimetype);
			}
		}
		return $trigger;
	}

	function regenerateImages()
	{
		$pid = $this->pid;

		// get a list of datastreams from the object
		$ds = Fedora_API::callGetDatastreams($pid);

		// ingest the datastreams
		foreach ($ds as $dsKey => $dsTitle) {
			$dsIDName = $dsTitle['ID'];
			if ($dsTitle['controlGroup'] == 'M'
			&& (is_numeric(strpos($dsTitle['MIMEType'],"image/")) || is_numeric(strpos($dsTitle['MIMEType'],"video/")) || is_numeric(strpos($dsTitle['MIMEType'],"audio/")))
			&& !Misc::hasPrefix($dsIDName, 'preview_')
			&& !Misc::hasPrefix($dsIDName, 'web_')
			&& !Misc::hasPrefix($dsIDName, 'stream_')
			&& !Misc::hasPrefix($dsIDName, 'thumbnail_')
			)
			{
				// first extract the image and save temporary copy
				$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsIDName;
				//              copy($urldata,APP_TEMP_DIR.$dsIDName);
				/*$urlReturn = Misc::ProcessURL($urldata);
				 $handle = fopen(APP_TEMP_DIR.$dsIDName, "w");
				 fwrite($handle, $urlReturn[0]);
				 fclose($handle);*/

				//				$urlReturn = Misc::ProcessURL($urldata);
				$handle = fopen(APP_TEMP_DIR.$dsIDName, "w");
				Misc::processURL($urldata, false, $handle);
				fclose($handle);
				$new_dsID = Foxml::makeNCName($dsIDName);
				if( $new_dsID != $dsIDName ){
					// delete and re-ingest - need to do this because sometimes the object made it
					// into the repository even though it's dsID is illegal.
					Fedora_API::callPurgeDatastream($pid, $dsIDName);
				}
				$versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';
				Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, APP_TEMP_DIR.$dsIDName, $dsIDName,
				$dsTitle['MIMEType'], "M", null, $versionable);
				// preservation metadata
				$presmd_check = Workflow::checkForPresMD($new_dsID);

				if ($presmd_check != false) {
					// strip directory off the name
					$pres_dsID = basename($presmd_check);
					if (Fedora_API::datastreamExists($pid, $pres_dsID)) {
						//$presData = Misc::ProcessURL($presmd_check);
						//						$xml = $presData[0];
						//($pid, $dsID, $dsLabel, $dsLocation=NULL, $mimetype) {
						//						Fedora_API::callModifyDatastreamByReference($pid, $pres_dsID,
						//                                "Preservation Metadata", $presmd_check, "text/xml");
						//                        Fedora_API::callModifyDatastreamByValue($pid, $pres_dsID, "A",
						//                                "Preservation Metadata", $xml, "text/xml", "inherit");
						Fedora_API::callPurgeDatastream($pid, $pres_dsID);
						Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                                "text/xml", "M");

					} else {
						Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                                "text/xml", "M");
					}
					if (is_file($presmd_check)) {
						$deleteCommand = APP_DELETE_CMD." ".$presmd_check;
						exec($deleteCommand);
					}
				}
				Exiftool::saveExif($pid, $dsIDName);
				// process it's ingest workflows
				Workflow::processIngestTrigger($pid, $dsIDName, $dsTitle['MIMEType']);
				//clear the managed content file temporarily saved in the APP_TEMP_DIR
				if (is_file(APP_TEMP_DIR.$dsIDName)) {
					$deleteCommand = APP_DELETE_CMD." ".APP_TEMP_DIR.$dsTitle['ID'];
					exec($deleteCommand);
				}
			}
		}
		Record::setIndexMatchingFields($pid);
	} // end of function
} // end of class
