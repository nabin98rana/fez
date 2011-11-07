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
 * Handles the processes required to confirm the submission for approval (SFA) of a thesis.
 * The SFA takes care of the following submission: student thesis & professional doctorate thesis.
 *
 * @version 1.0
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2011 The University of Queensland
 */

class Fez_Workflow_Sfa_Confirm{

    // A record object for the pid of the current workflow
    public $record = null;

    // The PID used by the current workflow we are working on
    public $pid = null;

    // An array of data (field & value pair) that can be used for front-end presentation
    public $display_data = array();

    // An array of the values submitted for approval
    public $submitted_values = array();

    // Array of field's input types that need to be excluded from front-end presentation
    public $excluded_inputs = array("xsdmf_id_ref", "xsd_ref", "author_suggestor", "dynamic", "hidden", "file_input");


    /**
     * Class constructor
     * @param string $pid An identifier of the current process
     */
    public function __construct( $pid=null )
    {
        $this->pid = $pid;
        $this->setRecord($this->pid);
    }


    /**
     * Set the record object based on the pid of the current workflow
     * @param string $pid
     * @return void
     */
    public function setRecord( $pid=null )
    {
        if ( is_null($pid) || empty($pid) ){
            return false;
        }

        $record = new RecordObject($pid);
        $record->getObjectAdminMD();
        $record->getDisplay();

        $this->record = $record;
    }


    /**
     * Return presentable record details, that will be outputted on confirmation ui and email.
     * @return void
     */
    public function getDisplayData()
    {

        // Get submitted values
        $this->submitted_values = $this->record->getDetails();

        // Get display fields.  XSD_DisplayObject
        $xsd_display_fields = $this->record->display->getMatchFieldsList(array("FezACML"), array());

        $this->output_records = $this->filterDisplayData($xsd_display_fields);

        // @debug
//        $this->displayDebugData($this->output_records);

        return $this->output_records;
    }


    /**
     * Return an array of filtered version of the data (field & value pair)
     * @param array $xsd_display_fields
     * @return array|null
     */
    protected function filterDisplayData( $xsd_display_fields=array() )
    {

        if ( !is_array($xsd_display_fields) && !is_object($xsd_display_fields) ){
            return null;
        }

        // Array container of the filtered fields
        $the_chosen_ones = array();

        // Add PID on the display data
        $pid = array("xsdmf_title" => "PID", "value" => $this->pid);
        $the_chosen_ones[] =$pid;

        foreach ($xsd_display_fields as $key => $field){

            // 1) Filter out invisible fields
            if ($field['xsdmf_invisible'] == 1){
                continue;
            }

            // 2) Filter out disabled fields
            if ($field['xsdmf_enabled'] != 1){
                continue;
            }

            // 3) Filter out fields with empty html input
            if ( empty($field['xsdmf_html_input'])) {
                continue;
            }

            // 4) Filter out fields that match excluded list
            if (in_array($field['xsdmf_html_input'], $this->excluded_inputs)){
                continue;
            }

            // 5) Filter out Files description input placeholder
            if ($field['xsdmf_id'] == 6904){
                continue;
            }

            // 6) Static html_input filtering - based on view_metadata workflow
            // When it is a STATIC html_input,
            // the field need to be viewable, xsdmf_show_in_view = 1 AND
            // the static text is not empty, xsdmf_static_text
            // otherwise, filter them out.
            if (  $field['xsdmf_html_input'] == 'static' &&
                  ( $field['xsdmf_show_in_view'] !=1 || empty($field['xsdmf_static_text']) )
                )
            {
                continue;
            }


            // 7) 'xsd_loop_subelement' html_input filtering
            if ( $field['xsdmf_html_input'] == 'xsd_loop_subelement' && $field['xsdmf_show_in_view'] != 1 ) {
                continue;
            }

            // Get the value
            $field['value'] = $this->_getDisplayValue($field);

            // 8) Empty Static html_input filtering
            // Filter out any static fields that do not have any value
            if ( $field['xsdmf_html_input'] == 'static' && empty($field['value']) ) {
                continue;
            }

            // If a field survives until this stage, it is the chosen one.
            $the_chosen_ones[] = $field;
        }

        // Return the filtered fields
        return $the_chosen_ones;
    }


    /**
     * Returns presentable submitted value of a field
     * Handles the display value differently based on the html_input type
     * @param $field
     * @return array|bool|string
     */
    protected function _getDisplayValue( $field )
    {
        $value = $this->submitted_values[$field['xsdmf_id']];
        $display_value = '';

        switch($field['xsdmf_html_input']){
            case 'depositor_org':
                $display_value = $this->_getDepositorOrgValue($value);
                break;
            case 'dual_multiple':
                $display_value = $this->_getDualMultipleValue($value);
                break;
            default:
                $display_value = $value;
        }
        return $display_value;
    }


    /**
     * Retrieves the value of Depositor Org based on the id(s) defined on the param.
     * Returns value in string. Implode the return value when it is in an array.
     * @param string|array $value  Value can be in array or string
     * @return string
     */
    protected function _getDepositorOrgValue($value = '')
    {

        if ( is_array($value) || is_object($value) ){
            foreach ($value as $oneValue){
                $display_value[] = Org_Structure::getTitle($oneValue);
            }
            return $display_value;
        }

        return Org_Structure::getTitle($value);
    }


    /**
     * Retrieves the value of Dual Multiple type based on the id(s) defined on the param.
     * Returns value in string. Implode the return value when it is in an array.
     * @param string|array $value  Value can be in array or string
     * @return string
     */
    protected function _getDualMultipleValue($value = '')
    {
        if ( is_array($value) || is_object($value) ){
            foreach ($value as $oneValue){
                $display_value[] = Record::getTitleFromIndex($oneValue);
            }
            return $display_value;
        }

        return Record::getTitleFromIndex($value);
    }


    /**
     * Debug function for outputting filtered fields & submitted values referred to the fields
     * @param array $theChosenOnes
     * @return void
     */
    public function displayDebugData( $theChosenOnes = array() )
    {
        $details = $this->record->getDetails();

        // Let's see who are the chosen ones
        echo "<table style='vertical-align: top; font-size: 12px; font-family: arial;'>";
        foreach($theChosenOnes as $key => $field){
            $display_value = $details[$field['xsdmf_id']];

            // Retrieve value from IDs
            if ( $field['xsdmf_html_input'] == 'depositor_org' ){
                $display_value= Org_Structure::getTitle($display_value);
            }

            if  ($field["xsdmf_html_input"] == 'dual_multiple') {
                $display_value = Record::getTitleFromIndex($display_value);
            }

            echo "<tr>";
                echo "<th style='text-align:right'>". $field['xsdmf_title'] ." : </th>";
                echo "<td style='text-align:left'>";
                if ( is_array($display_value) ){
                    echo "<pre>". print_r($display_value,1) ."</pre>";
                } else {
                    echo $display_value;
                }
                echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "<pre>The Chosen Ones". print_r($theChosenOnes,1) ."</pre>";

    }

    /**
     * Returns url for viewing the record
     * @return string
     */
    public function getViewUrl()
    {
        $view_record_url = 'http://'. APP_HOSTNAME . APP_RELATIVE_URL . "view/". $this->pid;

        if (Misc::isValidPid($this->pid)){
            if ( $this->record->isCommunity() ) {
                $view_record_url = APP_RELATIVE_URL . "community/" . $this->pid;
            } elseif ( $this->record->isCollection() ) {
                $view_record_url = APP_RELATIVE_URL . "collection/" . $this->pid;
            }
        }

        return $view_record_url;
    }

        
    /**
     * Return the record title used for front end output
     * @return string
     */
    public function getRecordTitle()
    {
        $record_title = $this->pid;

        if (Misc::isValidPid($this->pid)) {
            $record_title = $this->record->getTitle();
        }

        return $record_title;
    }


    /**
     * Retrieves files associated with a PID
     * @return void
     */
    public function getAttachedFiles()
    {
        $datastreams = $this->_getFilesViaFedoraDatastreams();
        $output = array();

        // Allow the following file types: PDF, Image and Word Doc.
        // The reason we are using the search value as array key is because searching on array keys has faster performance than searching on the array values.
        // Ref: http://ilia.ws/archives/12-PHP-Optimization-Tricks.html
        $accepted_file_types = array("application/pdf" => true, "image/png" => true, "application/x-zip" =>true, "application/zip" =>true);

        $c=0;

        foreach ($datastreams as $datastream){

            // Ignore datasteam that does not have M controlgroup AND not set as Lister
            if ($datastream['controlGroup'] !== "M" || $datastream['isLister'] != 1){
                continue;
            }

            // Filename
            if ($datastream['isViewer'] ==1 && isset($accepted_file_types[$datastream['MIMEType']]) ){
                $output[$c]['filename'] =$datastream['ID'];
            }

            // Checksum
            $output[$c]['checksumType'] = $datastream['checksumType'];
            $output[$c]['checksum'] = $datastream['checksum'];

            // Everything else (Description, MIME, Size, D/Ls)
            $output[$c]['label'] = $datastream['label'];
            $output[$c]['MIMEType'] = $datastream['MIMEType'];
            $output[$c]['archival_size'] = $datastream['archival_size'];
            $output[$c]['downloads'] = $datastream['downloads'];

            $c++;

        }

        return $output;
    }


    /**
     * Returns files associated with a PID with Fedora Datastreams.
     * Source code is copied from view2.php
     * @return array
     */
    protected function _getFilesViaFedoraDatastreams()
    {
        include_once(APP_INC_PATH . "class.jhove.php");

        $datastreams = Fedora_API::callGetDatastreams($this->pid);

        $datastreamsAll = $datastreams;
		$datastreams = Misc::cleanDatastreamListLite($datastreams, $this->pid);

        // @debug Temporary logging for monitoring the attached files
        $log = FezLog::get();
        $log->warn("Thesis Files. PID=" . $this->pid .  ". DataStreamsAll= " . sizeof($datastreamsAll));
        $log->warn("Thesis Files. PID=" . $this->pid .  ". DataStreamsClean= " . sizeof($datastreams));


        $linkCount = 0;
        $fileCount = 0;
		foreach ($datastreams as $ds_key => $ds) {

		    if ($datastreams[$ds_key]['controlGroup'] == 'R') {
				$linkCount++;
			}

			if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] != 'DOI') {
				$datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);

				// Check for APP_LINK_PREFIX and add if not already there add it to a special ezyproxy link for it
				if (APP_LINK_PREFIX != "") {
					if (!is_numeric(strpos($datastreams[$ds_key]['location'], APP_LINK_PREFIX))) {
						$datastreams[$ds_key]['prefix_location'] = APP_LINK_PREFIX.$datastreams[$ds_key]['location'];
						$datastreams[$ds_key]['location'] = str_replace(APP_LINK_PREFIX, "", $datastreams[$ds_key]['location']);
					} else {
						$datastreams[$ds_key]['prefix_location'] = "";
					}
				} else {
					$datastreams[$ds_key]['prefix_location'] = "";
				}

			} elseif ($datastreams[$ds_key]['controlGroup'] == 'M') {

			    $fileCount++;
				$datastreams[$ds_key]['exif'] = Exiftool::getDetails($this->pid, $datastreams[$ds_key]['ID']);

				if (APP_EXIFTOOL_SWITCH != "ON" || !is_numeric($datastreams[$ds_key]['exif']['exif_file_size'])) { //if Exiftool isn't on then get the datastream info from JHOVE (which is a lot slower than EXIFTOOL)


	                if (is_numeric(strrpos($datastreams[$ds_key]['ID'], "."))) {
					    $Jhove_DS_ID = "presmd_".substr($datastreams[$ds_key]['ID'], 0, strrpos($datastreams[$ds_key]['ID'], ".")).".xml";
	                } else {
	                    $Jhove_DS_ID = "presmd_".$datastreams[$ds_key]['ID'].".xml";
	                }

					foreach ($datastreamsAll as $dsa) {

						if ($dsa['ID'] == $Jhove_DS_ID) {
							$Jhove_XML = Fedora_API::callGetDatastreamDissemination($this->pid, $Jhove_DS_ID);

							if(!empty($Jhove_XML['stream'])) {
	    						$jhoveHelp = new Jhove_Helper($Jhove_XML['stream']);

	    						$fileSize = $jhoveHelp->extractFileSize();

	    						$datastreams[$ds_key]['archival_size'] =  Misc::size_hum_read($fileSize);
	    						$datastreams[$ds_key]['archival_size_raw'] = $fileSize;

	    						$spatialMetrics = $jhoveHelp->extractSpatialMetrics();

	    						if( is_numeric($spatialMetrics[0]) && $spatialMetrics[0] > 0 ) {
	                                $tpl->assign("img_width", $spatialMetrics[0]);
	    						}

	    						if( is_numeric($spatialMetrics[1]) && $spatialMetrics[1] > 0 ) {
	                                $tpl->assign("img_height", $spatialMetrics[1]);
	    						}

	    						unset($jhoveHelp);
	    						unset($Jhove_XML);
							}
						}
					}
				}	else {
					$datastreams[$ds_key]['MIMEType'] =  $datastreams[$ds_key]['exif']['exif_mime_type'];
					$datastreams[$ds_key]['archival_size'] =  $datastreams[$ds_key]['exif']['exif_file_size_human'];
					$datastreams[$ds_key]['archival_size_raw'] = $datastreams[$ds_key]['exif']['exif_file_size'];
				}
				$origami_switch = "OFF";
				if (APP_ORIGAMI_SWITCH == "ON" && ($datastreams[$ds_key]['MIMEType'] == 'image/jpeg' ||
		             $datastreams[$ds_key]['MIMEType'] == 'image/tiff' ||
		             $datastreams[$ds_key]['MIMEType'] == 'image/tif' ||
		             $datastreams[$ds_key]['MIMEType'] == 'image/jpg')) {
					 $origami_path = Origami::getTitleHome() . Origami::getTitleLocation($this->pid, $datastreams[$ds_key]['ID']);
					 if (is_dir($origami_path)) {
						$origami_switch = "ON";
					 }
				}
				$datastreams[$ds_key]['origami_switch'] = $origami_switch;

				$datastreams[$ds_key]['FezACML'] = Auth::getAuthorisationGroups($this->pid, $datastreams[$ds_key]['ID']);
				$datastreams[$ds_key]['downloads'] = Statistics::getStatsByDatastream($this->pid, $ds['ID']);
				$datastreams[$ds_key]['base64ID'] = base64_encode($ds['ID']);

				if (APP_FEDORA_DISPLAY_CHECKSUMS == "ON") {
					$datastreams[$ds_key]['checksumType'] = $ds['checksumType'];
					$datastreams[$ds_key]['checksum'] = $ds['checksum'];
				}

				Auth::getAuthorisation($datastreams[$ds_key]);
			}

            if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] == 'DOI') {
                $datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);
            }
		}

        return $datastreams;
    }

}
