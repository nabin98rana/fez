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
var $externalDatastreams;

/*
			// if the text of the file has foxml:digitalObject its probably a fedora object xml so just try and import that, otherwise try and convert it (probably METS)
			if (!is_numeric(strpos($xmlImport, "foxml:digitalObject"))) {		
				$xmlObj = BatchImport::ConvertImportXMLFOXML($pid, $xmlImport, $collection_pid, $short_name, $xdis_id, $ret_id);
			} else {
				$xmlObj = $xmlImport;
			}
*/

function handleFOXMLImport($xmlObj) {
	// xml is already in fedora object xml format so just add it
//	Fedora_API::callIngestObject($xmlObj); 

}

function handleMETSImport($pid, $xmlObj, $xmlBegin) {


	$externalDatastreams = array();

//	  print_r($externalDatastreams);


// check for oai_dc, if so add it	  
	$oai_dc = BatchImport::getOAI_DC($xmlObj);
//	echo $oai_dc;
	if ($oai_dc != false) {
	  BatchImport::getExternalDatastreams($oai_dc, $externalDatastreams);
	  foreach($externalDatastreams as $ds) {
			if (is_numeric(strpos($ds, "/"))) {
				$short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
			}
			$short_ds = str_replace(" ", "_", $short_ds);
			$mimetype = Misc::get_content_type($ds);

			$xmlBegin .= '
				<foxml:datastream ID="'.$short_ds.'" CONTROL_GROUP="M" STATE="A">
			<foxml:datastreamVersion ID="'.$short_ds.'.0" MIMETYPE="'.$mimetype.'" LABEL="'.$short_ds.'">
				<foxml:contentLocation REF="'.$ds.'" TYPE="URL"/>
			</foxml:datastreamVersion>
		</foxml:datastream>';
	  }	  

	  
//	  echo "strip tag -> ";
	  $oai_dc = BatchImport::stripTag($oai_dc, "<dc:format>");
  	  $oai_dc = BatchImport::stripTag($oai_dc, "<dc:identifier>");
//	  echo "\n\n";
	  $xmlBegin .= '
	  <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
			<foxml:xmlContent>
			'.$oai_dc.'
			</foxml:xmlContent>
		</foxml:datastreamVersion>
	  </foxml:datastream>';
	 } else {
	 $xmlBegin .= '
	 <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
			<foxml:xmlContent>
				<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <dc:title>'.$short_name.'</dc:title>
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
	  </foxml:datastream>';
	  }
	  
	  $xmlBegin .= '	  
	</foxml:digitalObject>
	';

	  
//		echo $xmlBegin;

	Fedora_API::callIngestObject($xmlBegin);
	foreach($externalDatastreams as $ds) {
		$convert_check = Workflow::checkForImageFile($ds);
		if ($convert_check != false) {
			Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, "", "M");
			if (is_numeric(strpos($convert_check, "/"))) {
				$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
			}
			$convert_check = str_replace(" ", "_", $convert_check);
			Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $convert_check); // add the thumbnail to the espace index				
		}
		$presmd_check = Workflow::checkForPresMD($ds); // we are not indexing presMD so just upload the presmd if found
		if ($presmd_check != false) {
			Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
		}
	
	
		if (is_numeric(strpos($ds, "/"))) {
			$ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
		}
		$ds = str_replace(" ", "_", $ds);
//		echo $ds;
		Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $ds); // add the thumbnail to the espace index				
	}	  

	return $xmlBegin;

}

function handleStandardFileImport($pid, $full_name, $short_name, $xmlObj) {
	//Insert the generated foxml object
/*	Fedora_API::callIngestObject($xmlObj);
	//Insert the standard file as a datastream to the new object
	Fedora_API::getUploadLocationByLocalRef($pid, $full_name, $full_name, $full_name, "", "M");	
	// Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
	$convert_check = Workflow::checkForImageFile($full_name);
	if ($convert_check != false) {
		Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, "", "M");
		if (is_numeric(strpos($convert_check, "/"))) {
			$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
		}
		$convert_check = str_replace(" ", "_", $convert_check);
		Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $convert_check); // add the thumbnail to the espace index				
	}
	$presmd_check = Workflow::checkForPresMD($full_name); // we are not indexing presMD so just upload the presmd if found
	if ($presmd_check != false) {
		Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
	}
	
	
	// now add a resource index for the datastream file
	// lowercase the extension if necessary
	if (is_numeric(strpos($short_name, "."))) {
		$filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
		$short_name = substr($short_name, 0, strrpos($short_name, ".") + 1).$filename_ext;
	}

	Record::insertIndexMatchingField($pid, 122, NULL, NULL, 'varchar', $short_name);
*/
	
}


function insert() {
	global $HTTP_POST_VARS;	
//	echo $HTTP_POST_VARS['objectimport']; echo $HTTP_POST_VARS['directory'];
	if ((!empty($HTTP_POST_VARS['objectimport'])) && (!empty($HTTP_POST_VARS['directory']))) {
		//open the current directory
		$xdis_id = 5; // standard fedora object
		$ret_id = 3; // standard record type id
		$sta_id = 1; // standard status type id
		$xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
		$xmlDatastream = $DSResultArray['stream'];
		$xsd_id = XSD_Display::getParentXSDID($xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
		$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
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
			$pid = Fedora_API::getNextPID();
			// Also need to add the espaceMD and RELS-EXT - espaceACML probably not necessary as it can be inhereted
			// and the espaceMD can have status - 'freshly uploaded' or something.

			$filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
			if ($filename_ext == "xml") {
//				echo "found an xml file!";
				$xmlObj = file_get_contents($full_name);
//				echo $xmlObj;
				if (is_numeric(strpos($xmlObj, "foxml:digitalObject"))) {
//					echo "found a foxml object!";
					BatchImport::handleFOXMLImport($xmlObj);
				} elseif (is_numeric(strpos($xmlObj, "METS:mets"))) {
//					echo "founc a mets object!";
					$xmlBegin = BatchImport::ConvertMETSToFOXML($pid, $xmlObj, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id);
					$xmlObj = BatchImport::handleMETSImport($pid, $xmlObj, $xmlBegin);
					
				} else { // just add it as a normal file if it is not foxml or mets
//					echo "found a standard xml file";
					$xmlObj = BatchImport::GenerateSingleFOXMLTemplate($pid, $parent_pid, $full_name, $xdis_id, $ret_id, $sta_id);
					BatchImport::handleStandardFileImport($pid, $full_name, $short_name, $xmlObj);
				}
			} else {
//				echo "found a standard file";
				$xmlObj = BatchImport::GenerateSingleFOXMLTemplate($pid, $parent_pid, $full_name, $xdis_id, $ret_id, $sta_id);
				BatchImport::handleStandardFileImport($pid, $full_name, $short_name, $xmlObj);
			}
//			echo $xmlObj;
			// @@@ CK - 8/8/2005 - Also need to add details of this record into the espace resource index	
			// Get the xsdmf details to save in the resource index
			$xmlnode = new DomDocument();
			$xmlnode->loadXML($xmlObj);

			$array_ptr = array();
			$xsdmf_array = array();
//			echo $xmlObj;
			Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
//			print_r($array_ptr);
//			print_r($xsdmf_array);
			
			foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
				if (!is_array($xsdmf_value) && !empty($xsdmf_value) && (trim($xsdmf_value) != "")) {
					Record::insertIndexMatchingField($pid, $xsdmf_id, NULL, NULL, 'varchar', $xsdmf_value);
				}
			}
		}
	}
}


function GenerateSingleFOXMLTemplate($pid, $parent_pid, $filename, $xdis_id, $ret_id, $sta_id) {

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
				  <sta_id/>'.$sta_id.'
  				  <ret_id>'.$ret_id.'</ret_id>
				</eSpaceMD>
			</foxml:xmlContent>
		</foxml:datastreamVersion>
	  </foxml:datastream>
	</foxml:digitalObject>
	';
	return $xmlObj;


}

function getOAI_DC($xmlObj) {
	$tagOpen = '<oai_dc:dc';
	$tagClose = "</oai_dc:dc>";
//	$httpFind = "http://";

	$IDPos = stripos($xmlObj, $tagOpen); // stripos is a php5 function
	if (is_numeric($IDPos)) {
		$searchScopeEnd = strpos($xmlObj, $tagClose, $IDPos);
		if (is_numeric($searchScopeEnd)) {
			$startCut = ($IDPos);
			$xmlCut = substr($xmlObj, $startCut, ($searchScopeEnd-$startCut+strlen($tagClose)));
			return $xmlCut;
		} else {
			return false;
		}
	} else {
		return false;
	}

}

function stripTag($xmlObj, $tag) {
	$tagClose = "\<\/".substr($tag, 1);
	$xmlObj = preg_replace("/(\\".$tag.")(.*?)(".$tagClose.")/", "", $xmlObj);
	return $xmlObj;

}

function getExternalDatastreams($xmlObj, &$externalDatastreams) {
	// get all the URLs - especially from ePrints exported METS objects
	// checks DC:Format for http:// urls and returns the urls as an array
	// finds the first one if can and then 
	$tagOpen = '<dc:format>';
	$tagClose = "</dc:format>";
	$httpFind = "http://";

	$IDPos = stripos($xmlObj, $tagOpen); // stripos is a php5 function
	if (is_numeric($IDPos)) {
		$searchScopeEnd = strpos($xmlObj, $tagClose, $IDPos);
		if (is_numeric($searchScopeEnd)) {
			$startCut = ($IDPos+strlen($tagOpen));
			$xmlCut = substr($xmlObj, $startCut, ($searchScopeEnd-$startCut));
			$httpPos = strpos($xmlCut, $httpFind);
			if (is_numeric($httpPos)) { // found a url
				$url = substr($xmlCut, $httpPos);
				array_push($externalDatastreams, $url);
			}
			//Remove the used tag from the xml
			$xmlObj = str_replace($tagOpen.$xmlCut.$tagClose, "", $xmlObj);			
			// find any others
			BatchImport::getExternalDatastreams($xmlObj, $externalDatastreams);
		}
	} 
}

function ConvertMETSToFOXML($pid, $xmlImport, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id) {


//	$pid = $this->pid;

//	$externalDatastreams = array();

	$xmlObj = '<?xml version="1.0" ?>	
	<foxml:digitalObject PID="'.$pid.'"
	  fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
	  xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	  <foxml:objectProperties>
		<foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
		<foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
		<foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="Batch Import '.$short_name.'"/>
	  </foxml:objectProperties>
  	  <foxml:datastream ID="RELS-EXT" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="RELS-EXT.0" LABEL="Relationships to other objects">
			<foxml:xmlContent>
				<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
				  xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <rdf:description rdf:about="info:fedora/'.$pid.'">
					<rel:isMemberOf rdf:resource="info:fedora/'.$collection_pid.'"/>
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
				  <sta_id>'.$sta_id.'</sta_id>
  				  <ret_id>'.$ret_id.'</ret_id>
				</eSpaceMD>
			</foxml:xmlContent>
		</foxml:datastreamVersion>
	  </foxml:datastream>
';


	 
	 

	return $xmlObj;


}


}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Batch Import Class');
}

?>
