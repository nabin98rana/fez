<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Record Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.Record.php 1.114 04/01/19 15:15:25-00:00 jpradomaia $
//


/**
 * Class designed to handle all business logic related to the Records in the
 * system, such as adding or updating them or listing them in the grid mode.
 *
 * @author  João Prado Maia <jpm@mysql.com>
 * @version $Revision: 1.114 $
 */

include_once(APP_INC_PATH . "class.validation.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");

include_once(APP_INC_PATH . "class.workflow.php");

include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");



/**
  * Record
  * Static class for accessing record related queries
  * See RecordObject for an object oriented representation of a record.
  */
class BatchImport
{
var $pid;

function insert() {
	global $HTTP_POST_VARS;
	
//	echo $HTTP_POST_VARS['objectimport']; echo $HTTP_POST_VARS['directory'];
	if ((!empty($HTTP_POST_VARS['objectimport'])) && (!empty($HTTP_POST_VARS['directory']))) {
		//open the current directory
		$xdis_id = 5; // standard fedora object
		$ret_id = 3; // standard record type id
		$xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
		$xmlDatastream = $DSResultArray['stream'];
		$xsd_id = XSD_Display::getParentXSDID($xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
//		echo $xsd_top_element_name;
//		echo $xsd_element_prefix;
//		$xsd_element_prefix = "oai_dc";
		$collection_pid = $HTTP_POST_VARS['collection_pid'];
		$dir_name = APP_SAN_IMPORT_DIR."/".$HTTP_POST_VARS['directory'];
		$directory = opendir($dir_name);
	    while (false !== ($file = readdir($directory))) { 
			if (is_file($dir_name."/".$file)) {
				$filenames[$dir_name."/".$file] = $file;
			}
		}
//		print_r($filenames);
		foreach ($filenames as $full_name => $short_name) {
//			echo $full_name; echo "\n\n<br />";
//			echo $short_name; echo "\n\n<br />";
//			$pid = "UQL-Fed:100";
			$pid = Fedora_API::getNextPID();
			// Also need to add the espaceMD and RELS-EXT - espaceACML probably not necessary as it can be inhereted
			// and the espaceMD can have status - 'freshly uploaded' or something.
			$xmlObj = BatchImport::GenerateSingleFOXMLTemplate($pid, $collection_pid, $short_name, $xdis_id, $ret_id);
/*            $config = array(
                    'indent'         => true,
                    'input-xml'   => true,
                    'output-xml'   => true,
                    'wrap'           => 200);

            $tidy = new tidy;
            $tidy->parseString($xmlObj, $config, 'utf8');
            $tidy->cleanRepair();
            $xmlObj = $tidy; */

//			echo $xmlObj; echo "\n\n<br />";
			// Kill any existing resource indexes for that pid
			Record::removeIndexRecord($pid);


			Fedora_API::callIngestObject($xmlObj);

//			Fedora_API::getUploadLocation($pid, $full_name, $full_name, $full_name, "", "M");
			Fedora_API::getUploadLocationByLocalRef($pid, $full_name, $full_name, $full_name, "", "M");


			// Now check for post upload workflow events like thumbnail resizing of images
			$convert_check = Workflow::checkForImageFile($full_name);
			if ($convert_check != false) {
//				echo $convert_check;
				Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, "", "M");
				if (is_numeric(strpos($convert_check, "/"))) {
					$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
				}
				$convert_check = str_replace(" ", "_", $convert_check);
				Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $convert_check); // add the thumbnail to the espace index				
			}
			$presmd_check = Workflow::checkForPresMD($full_name);
			if ($presmd_check != false) {
//				echo $presmd_check;
				Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
			}
			// @@@ CK - 8/8/2005 - Also need to add details of this record into the espace resource index
			// get the xsdmf_ids - manual xdis_id = 5 at the moment for testing

				
				$xmlnode = new DomDocument();
				$xmlnode->loadXML($xmlObj);

				$array_ptr = array();
				$xsdmf_array = array();
//				echo $xmlObj;
				Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
//				print_r($array_ptr);
//				print_r($xsdmf_array);

				
				foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
					if (!is_array($xsdmf_value) && !empty($xsdmf_value) && (trim($xsdmf_value) != "")) {
						Record::insertIndexMatchingField($pid, $xsdmf_id, NULL, NULL, 'varchar', $xsdmf_value);
					}
				}
				// now add a resource index for the datastream 
				// lowercase the extension if necessary
				if (is_numeric(strpos($short_name, "."))) {
					$filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
					$short_name = substr($short_name, 0, strrpos($short_name, ".") + 1).$filename_ext;
				}

				Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $short_name);
//			}

		}
	}


//	$record = new RecordObject();
//	$record->fedoraInsertUpdate();
}


function GenerateSingleFOXMLTemplate($pid, $parent_pid, $filename, $xdis_id, $ret_id) {

/*	if (empty($this->pid)) {
		$this->pid = Fedora_API::getNextPID();
	}*/
//	$pid = $this->pid;

	
	$xmlObj = '<?xml version="1.0" ?>	
	<foxml:digitalObject PID="'.$pid.'"
	  fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
	  xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	  <foxml:objectProperties>
		<foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
		<foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
		<foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="Batch Import '.$filename.'"/>
	  </foxml:objectProperties>
	  <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
			<foxml:xmlContent>
				<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <dc:title>'.$filename.'</dc:title>
				  <dc:creator/>
				  <dc:subject/>
				  <dc:description/>
				  <dc:publisher/>
				  <dc:contributor/>
				  <dc:date/>
				  <dc:type/>
				  <dc:source/>
				  <dc:language/>
				  <dc:relation/>
				  <dc:coverage/>
				  <dc:rights/>
				</oai_dc:dc>
			</foxml:xmlContent>			
		</foxml:datastreamVersion>
	  </foxml:datastream>
	  <foxml:datastream ID="RELS-EXT" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="RELS-EXT.0" LABEL="Relationships to other objects">
			<foxml:xmlContent>
				<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
				  xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <rdf:description rdf:about="info:fedora/'.$pid.'">
					<rel:isMemberOf rdf:resource="info:fedora/'.$parent_pid.'"/>
				  </rdf:description>
				</rdf:RDF>
			</foxml:xmlContent>
		</foxml:datastreamVersion>
	  </foxml:datastream>
  	  <foxml:datastream ID="eSpaceMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="eSpace1.0" LABEL="eSpace extension metadata">
			<foxml:xmlContent>
				<eSpaceMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <xdis_id>'.$xdis_id.'</xdis_id>
				  <sta_id/>
  				  <ret_id>'.$ret_id.'</ret_id>
				</eSpaceMD>
			</foxml:xmlContent>
		</foxml:datastreamVersion>
	  </foxml:datastream>
	</foxml:digitalObject>
	';
	return $xmlObj;


}

}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Batch Import Class');
}

?>
