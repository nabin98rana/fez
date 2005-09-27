<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | eSpace - Digital Repository                                          |
// +----------------------------------------------------------------------+
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
// |																	  |
// | Some code and structure is derived from Eventum (GNU GPL - MySQL AB) |
// | http://dev.mysql.com/downloads/other/eventum/index.html			  |
// | Eventum is primarily authored by João Prado Maia <jpm@mysql.com>     |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class designed to handle all business logic related to the batch importing of records in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
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
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");



/**
  * Batch Import
  */
class BatchImport
{
var $pid;
var $externalDatastreams;


function handleEntireEprintsImport($pid, $collection_pid, $xmlObj) {
	$importArray = array();
	$xdis_id = 40; // standard fedora object
	$ret_id = 3; // standard record type id
	$sta_id = 1; // standard status type id
    $created_date = date("Y-m-d H:i:s");
	$updated_date = $created_date;
    $xsd_id = XSD_Display::getParentXSDID($xdis_id);
	$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
	$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
	$xsd_top_element_name = $xsd_details['xsd_top_element_name'];


	$config = array(
			'indent'      => true,
			'input-xml'   => true,
			'output-xml'  => true,
			'wrap'        => 200);

	$tidy = new tidy;
	$tidy->parseString($xmlObj, $config, 'utf8');
	$tidy->cleanRepair();
	$xmlObj = $tidy;

	$xmlDoc= new DomDocument();
	$xmlDoc->preserveWhiteSpace = false;
	$xmlDoc->loadXML($xmlObj);

	

	$xpath = new DOMXPath($xmlDoc);

	$recordNodes = $xpath->query('//eprintsdata/record');

		$authorArray = array();
		$editorArray = array();
		$keywordArray = array();


	foreach ($recordNodes as $recordNode) {
		$record_type = "";
		$eprint_id = "";
		//get the record type
		$type_fields = $xpath->query("./*[contains(@name, 'type')]", $recordNode);
		foreach ($type_fields as $type_field) {
			if  ($record_type == "") {
				$record_type = $type_field->nodeValue;				
			}
		}
		$id_fields = $xpath->query("./*[contains(@name, 'eprintid')]", $recordNode);
		foreach ($id_fields as $id_field) {
			if  ($eprint_id == "") {
				$eprint_id = $id_field->nodeValue;
			}
		}

		$keywordArray[$eprint_id] = array();
		$keyword_fields = $xpath->query("./*[contains(@name, 'keywords')]", $recordNode);
		foreach ($keyword_fields as $keyword_field) {
			$keyword_split = array();
			$keyword_split = explode(";", $keyword_field->nodeValue);
			foreach($keyword_split as $kw) {
				array_push($keywordArray[$eprint_id], trim($kw));
			}
		}

		$editorArray[$eprint_id] = array();
		$editor_fields = $xpath->query("./*[contains(@name, 'editors')]", $recordNode);
		foreach ($editor_fields as $editor_field) {
			$family_name = $xpath->query("./*[contains(@name, 'family')]", $editor_field);
			foreach ($family_name as $fname) {
				$family = $fname->nodeValue;
			}

			$given_name = $xpath->query("./*[contains(@name, 'given')]", $editor_field);
			foreach ($given_name as $gname) {
				$given = $gname->nodeValue;
			}
			
			array_push($editorArray[$eprint_id], $family.", ".$given);
		}

		
		$authorArray[$eprint_id] = array();
		$author_fields = $xpath->query("./*[contains(@name, 'authors')]", $recordNode);
		foreach ($author_fields as $author_field) {
			$family_name = $xpath->query("./*[contains(@name, 'family')]", $author_field);
			foreach ($family_name as $fname) {
				$family = $fname->nodeValue;
			}

			$given_name = $xpath->query("./*[contains(@name, 'given')]", $author_field);
			foreach ($given_name as $gname) {
				$given = $gname->nodeValue;			
			}
			
			array_push($authorArray[$eprint_id], $family.", ".$given);
		}
		
		$fieldNodes = $xpath->query("./*[string-length(normalize-space())>0 and not(contains(@name, 'type'))]", $recordNode); 
		$field = "";
		$fieldValue = "";
		foreach ($fieldNodes as $fieldNode) {
			$field = $fieldNode->getAttribute('name');
			$fieldValue = $fieldNode->nodeValue;
			if ($field != "" && $fieldValue != "" && $record_type != "" && $eprint_id != "") {
				if (!is_array($importArray[$record_type][$eprint_id][$field])) {
					$importArray[$record_type][$eprint_id][$field] = array();
				}
				array_push($importArray[$record_type][$eprint_id][$field], $fieldValue);
			}
		}
	}
//print_r($keywordArray);
//	print_r($importArray);
//	exit();
//print_r($authorArray);
	foreach ($importArray['confpaper'] as $key => $data_field) {
//		echo "key = ".$key;
//		echo "data field = ".$data_field;
//		$tempXML = BatchImport::ConvertMETSToFOXML($pid, $xmlImport, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id) {
		$oai_dc_url = "http://eprint.uq.edu.au/perl/oai2?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai%3Aeprint.uq.edu.au%3A".$key;
		$oai_dc_xml = Fedora_API::URLopen($oai_dc_url);


		$config = array(
				'indent'         => true,
				'input-xml'   => true,
				'output-xml'   => true,
				'wrap'           => 200);
	
		$tidy = new tidy;
		$tidy->parseString($oai_dc_xml, $config, 'utf8');
		$tidy->cleanRepair();
		$oai_dc_xml = $tidy;
	
		$xmlOAIDoc= new DomDocument();
		$xmlOAIDoc->preserveWhiteSpace = false;
		$xmlOAIDoc->loadXML($oai_dc_xml);
//		echo $oai_dc_xml;
		
	
		$oai_xpath = new DOMXPath($xmlOAIDoc);
		$oai_xpath->registerNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
		$oai_xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
		$oai_xpath->registerNamespace('d', 'http://www.openarchives.org/OAI/2.0/');

		$formatNodes = $oai_xpath->query('//d:OAI-PMH/d:GetRecord/d:record/d:metadata/oai_dc:dc/dc:format');
		$oai_ds = array();
		foreach ($formatNodes as $format) {
			$httpFind = "http://";
			if (is_numeric(strpos($format->nodeValue, $httpFind))) {
				array_push($oai_ds, substr($format->nodeValue, strpos($format->nodeValue, $httpFind)));
			}
		} 
	  $xmlEnd = "";
	  foreach($oai_ds as $ds) {
			if (is_numeric(strpos($ds, "/"))) {
				$short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
			}
			$short_ds = str_replace(" ", "_", $short_ds);
			$mimetype = Misc::get_content_type($ds);

			$xmlEnd.= '
		<foxml:datastream ID="'.$short_ds.'" CONTROL_GROUP="M" STATE="A">
			<foxml:datastreamVersion ID="'.$short_ds.'.0" MIMETYPE="'.$mimetype.'" LABEL="'.$short_ds.'">
				<foxml:contentLocation REF="'.$ds.'" TYPE="URL"/>
			</foxml:datastreamVersion>
		</foxml:datastream>';
	  }	  


		$xmlObj = '<?xml version="1.0" ?>
		<foxml:digitalObject PID="'.$pid.'"
		  fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
		  xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
		  <foxml:objectProperties>
			<foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
			<foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
			<foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="Batch Import ePrint Record '.$key.'"/>
		  </foxml:objectProperties>
		  <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
			<foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
				<foxml:xmlContent>
					<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
					  <dc:title>'.$importArray['confpaper'][$key]['title'][0].'</dc:title>
					  ';
					  if (is_array($authorArray[$key])) {
						  foreach ($authorArray[$key] as $author) {
			$xmlObj .= '<dc:creator>'.$author.'</dc:creator>
						';					    
						  }
					   }
					  if (is_array($importArray['confpaper'][$key]['subjects'])) {
						  foreach ($importArray['confpaper'][$key]['subjects'] as $subject) {
							  $xmlObj .= '
					  <dc:subject>'.$subject.'</dc:subject>
					  ';	    
						  }
					  }

		  $xmlObj .= '<dc:description>'.$importArray['confpaper'][$key]['abstract'][0].'</dc:description>
					  <dc:publisher>'.$importArray['confpaper'][$key]['publisher'][0].'</dc:publisher>
					  <dc:contributor/>
					  <dc:date>'.$importArray['confpaper'][$key]['datestamp'][0].'</dc:date>
					  <dc:type>Conference Paper</dc:type>
					  <dc:source/>
					  <dc:language>English</dc:language>
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
						<rel:isMemberOf rdf:resource="info:fedora/'.$collection_pid.'"/>
					  </rdf:description>
					</rdf:RDF>
				</foxml:xmlContent>
			</foxml:datastreamVersion>
		  </foxml:datastream>
		  <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
			<foxml:datastreamVersion MIMETYPE="text/xml" ID="eSpace1.0" LABEL="eSpace extension metadata">
				<foxml:xmlContent>
					<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
					  <xdis_id>'.$xdis_id.'</xdis_id>
					  <sta_id>'.$sta_id.'</sta_id>
					  <ret_id>'.$ret_id.'</ret_id>
					  <created_date>'.$created_date.'</created_date>					  
					  <updated_date>'.$updated_date.'</updated_date>
					  <publication>'.$importArray['confpaper'][$key]['publication'][0].'</publication>  
					  <copyright>'.$importArray['confpaper'][$key]['note'][0].'</copyright> 
					  ';
					  if (is_array($keywordArray[$key])) {
						  foreach ($keywordArray[$key] as $keyword) {
$xmlObj .= '
					   <keyword>'.$keyword.'</keyword>';
						  }
					  }
				  $xmlObj .= '
					</FezMD>
				</foxml:xmlContent>
			</foxml:datastreamVersion>
		  </foxml:datastream>
  		  <foxml:datastream ID="ConferencePaperMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
			<foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferencePaperMD1.0" LABEL="eSpace extension metadata for Conference Papers">
				<foxml:xmlContent>
					<ConferencePaperMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
					  <conference>'.$importArray['confpaper'][$key]['conference'][0].'</conference>
					  <conf_start_date/>
					  <conf_end_date/>
					  <confloc>'.$importArray['confpaper'][$key]['confloc'][0].'</confloc>
					  <conf_details>'.$importArray['confpaper'][$key]['confdates'][0].'</conf_details>
					</ConferencePaperMD>
				</foxml:xmlContent>
			</foxml:datastreamVersion>
		  </foxml:datastream>';
		$xmlObj .= $xmlEnd;

		  $xmlObj .= '
		</foxml:digitalObject>
		';
//	echo $xmlObj;
		$config = array(
				'indent'         => true,
				'input-xml'   => true,
				'output-xml'   => true,
				'wrap'           => 200);
	
		$tidy = new tidy;
		$tidy->parseString($xmlObj, $config, 'utf8');
		$tidy->cleanRepair();
		$xmlObj = $tidy;

		Fedora_API::callIngestObject($xmlObj);
		foreach($oai_ds as $ds) {
			$convert_check = Workflow::checkForImageFile($ds);
			if ($convert_check != false) {
				Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, "", "M");
				if (is_numeric(strpos($convert_check, "/"))) {
					$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
				}
				$convert_check = str_replace(" ", "_", $convert_check);
				Record::insertIndexMatchingField($pid, 122, 'varchar', $convert_check); // add the thumbnail to the espace index				
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
			Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the thumbnail to the espace index				
		}	  
//		$xmlnode = new DomDocument();
//		$xmlnode->loadXML($xmlObj);


		$array_ptr = array();
		$xsdmf_array = array();
//			echo $xmlObj;
		// want to do this on a per datastream basis, not the entire xml object
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
		foreach ($datastreamTitles as $dsValue) {
			$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
            if (isset($DSResultArray['stream'])) {
                $xmlDatastream = $DSResultArray['stream'];
                $xsd_id = XSD_Display::getParentXSDID($dsValue['xsdmf_xdis_id']);
                $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
                $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
                $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

                $xmlnode = new DomDocument();
                $xmlnode->loadXML($xmlDatastream);
                $array_ptr = array();
                Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
            }
		}
		foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
			if (!is_array($xsdmf_value) && !empty($xsdmf_value) && (trim($xsdmf_value) != "")) {					
				$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
				Record::insertIndexMatchingField($pid, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_value);					
			} elseif (is_array($xsdmf_value)) {
				foreach ($xsdmf_value as $xsdmf_child_value) {
					if ($xsdmf_child_value != "") {
						$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
						Record::insertIndexMatchingField($pid, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_child_value);
					}
				}
			}
		}

		


		$pid = Fedora_API::getNextPID(); // get a new pid for the next loop
	}
exit();
}

function handleFOXMLImport($xmlObj) {
	// xml is already in fedora object xml format so just add it
	Fedora_API::callIngestObject($xmlObj); 

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

	Fedora_API::callIngestObject($xmlBegin);
	foreach($externalDatastreams as $ds) {
		$convert_check = Workflow::checkForImageFile($ds);
		if ($convert_check != false) {
			Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, "", "M");
			if (is_numeric(strpos($convert_check, "/"))) {
				$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
			}
			$convert_check = str_replace(" ", "_", $convert_check);
			Record::insertIndexMatchingField($pid, 122, 'varchar', $convert_check); // add the thumbnail to the espace index				
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
		Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the thumbnail to the espace index				
	}	  

	return $xmlBegin;

}

function handleStandardFileImport($pid, $full_name, $short_name, $xmlObj) {
	//Insert the generated foxml object
	Fedora_API::callIngestObject($xmlObj);
    $mimetype = Misc::mime_content_type($full_name);
	//Insert the standard file as a datastream to the new object
	$dsID = Fedora_API::getUploadLocationByLocalRef($pid, $full_name, $full_name, $full_name, $mimetype, "M");	
	// Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
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

	Record::insertIndexMatchingField($pid, 122,  'varchar', $short_name);
	
    Workflow::processIngestTrigger($pid, $dsID, $mimetype);
	
}


function insert() {
	global $HTTP_POST_VARS;	
	if ((!empty($HTTP_POST_VARS['objectimport'])) && (!empty($HTTP_POST_VARS['directory']))) {
		//open the current directory
		$xdis_id = $HTTP_POST_VARS['xdis_id']; 
		$ret_id = 3; // standard record type id
		$sta_id = 1; // standard status type id
		$xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
		$xsd_id = XSD_Display::getParentXSDID($xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
		$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
        $parent_pid = $collection_pid;
		$dir_name = APP_SAN_IMPORT_DIR."/".$HTTP_POST_VARS['directory'];
		$directory = opendir($dir_name);
	    while (false !== ($file = readdir($directory))) { 
			if (is_file($dir_name."/".$file)) {
				$filenames[$dir_name."/".$file] = $file;
			}
		}
		foreach ($filenames as $full_name => $short_name) {
			$pid = Fedora_API::getNextPID();
			// Also need to add the espaceMD and RELS-EXT - espaceACML probably not necessary as it can be inhereted
			// and the espaceMD can have status - 'freshly uploaded' or something.

			$filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
			if ($filename_ext == "xml") {
				$xmlObj = file_get_contents($full_name);
				if (is_numeric(strpos($xmlObj, "foxml:digitalObject"))) {
					BatchImport::handleFOXMLImport($xmlObj);
				} elseif (is_numeric(strpos($xmlObj, "<eprintsdata>"))) {
					BatchImport::handleEntireEprintsImport($pid, $collection_pid, $xmlObj);
				} elseif (is_numeric(strpos($xmlObj, "METS:mets"))) {
					$xmlBegin = BatchImport::ConvertMETSToFOXML($pid, $xmlObj, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id);
					$xmlObj = BatchImport::handleMETSImport($pid, $xmlObj, $xmlBegin);
					
				} else { // just add it as a normal file if it is not foxml or mets
                    $xmlObj = BatchImport::GenerateSingleFOXMLTemplate($pid, $parent_pid, $full_name, 
                            $xdis_id, $ret_id, $sta_id);
                    BatchImport::handleStandardFileImport($pid, $full_name, $short_name, $xmlObj);
				}
			} else {

				echo "found a standard file $full_name<br/>";
                $xmlObj = BatchImport::GenerateSingleFOXMLTemplate($pid, $parent_pid, $full_name, 
                        $xdis_id, $ret_id, $sta_id);
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
					Record::insertIndexMatchingField($pid, $xsdmf_id, 'varchar', $xsdmf_value);
				}
			}
		}
	}
}


function GenerateSingleFOXMLTemplate($pid, $parent_pid, $filename, $xdis_id, $ret_id, $sta_id) {
	
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
  	  <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="eSpace1.0" LABEL="eSpace extension metadata">
			<foxml:xmlContent>
				<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <xdis_id>'.$xdis_id.'</xdis_id>
				  <sta_id/>'.$sta_id.'
  				  <ret_id>'.$ret_id.'</ret_id>
				</FezMD>
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
   	  <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
		<foxml:datastreamVersion MIMETYPE="text/xml" ID="eSpace1.0" LABEL="eSpace extension metadata">
			<foxml:xmlContent>
				<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
				  <xdis_id>'.$xdis_id.'</xdis_id>
				  <sta_id>'.$sta_id.'</sta_id>
  				  <ret_id>'.$ret_id.'</ret_id>
				</FezMD>
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
