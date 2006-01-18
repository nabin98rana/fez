<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
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
include_once(APP_INC_PATH . "class.background_process.php");
include_once(APP_INC_PATH . "class.foxml.php");



/**
  * Batch Import
  */
class BatchImport
{
    var $pid;
    var $externalDatastreams;
    var $bgp; // background process object for keeping track of status since batchimport runs in background

    function setBackgroundObject($bgp)
    {
        $this->bgp = $bgp;
    }

    /**
     * Method used to import an entire ePrints "export_xml" xml file.
     * It uses OAI lookups to get the format DC field for the files actually attached to the records
     * 
     * Developer Note: This function works, but probably needs more work to make more user friendly, eg making the xdis_id's ret_ids etc more dynamic
     *
     * @access  public
     * @param   string $pid The current persistent identifier
     * @param   string $collection_pid The collection pid the records will be imported into as members
     * @param   string $xmlObj The string read from the eprints export_xml xml file
     * @return  void
     */
    function handleEntireEprintsImport($pid, $collection_pid, $xmlObj) {
        $importArray = array();
        $created_date = date("Y-m-d H:i:s");
        $updated_date = $created_date;

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

            $fieldNodes = $xpath->query("./field[string-length(normalize-space())>0 and not(contains(@name, 'type'))]", $recordNode); 
            $field = "";
            $fieldValue = "";
            foreach ($fieldNodes as $fieldNode) {
                $field = $fieldNode->getAttribute('name');
                $fieldValue = $fieldNode->nodeValue;
                if ($field != "" && $fieldValue != "" && $record_type != "" && $eprint_id != "") {
                    if (!is_array(@$importArray[$record_type][$eprint_id][$field])) {
                        $importArray[$record_type][$eprint_id][$field] = array();
                    }
                    array_push($importArray[$record_type][$eprint_id][$field], $fieldValue);
                }
            }
        }
        $num_records = $recordNodes->length;
        $eprint_record_counter = 0;
        foreach ($importArray as $document_type => $eprint_record) {	
            foreach ($eprint_record as $key => $data_field) {			
                $eprint_record_counter++;
                $xmlDocumentType = '';
                switch ($document_type) {
                    case 'confpaper':
                        $xdis_title = "Conference Paper";
                        $xmlDocumentType = '<foxml:datastream ID="ConferencePaperMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferencePaperMD1.0" LABEL="Fez extension metadata for Conference Papers">
                            <foxml:xmlContent>
                            <ConferencePaperMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <conference>'.htmlspecialchars($importArray[$document_type][$key]['conference'][0]).'</conference>
                            <conf_start_date/>
                            <conf_end_date/>
                            <confloc>'.htmlspecialchars($importArray[$document_type][$key]['confloc'][0]).'</confloc>
                            <conf_details>'.htmlspecialchars($importArray[$document_type][$key]['confdates'][0]).'</conf_details>
                            </ConferencePaperMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'journale':
                        $xdis_title = "Online Journal Article";
                        $xmlDocumentType = '<foxml:datastream ID="OnlineJournalArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="OnlineJournalArticleMD1.0" LABEL="Fez extension metadata for Online Journal Articles">
                            <foxml:xmlContent>
                            <OnlineJournalArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <journal>'.htmlspecialchars($importArray[$document_type][$key]['publication'][0]).'</journal>
                            <volume>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</volume>
                            <number>'.htmlspecialchars($importArray[$document_type][$key]['number'][0]).'</number>
                            </OnlineJournalArticleMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'journalp':
                        $xdis_title = "Journal Article";
                        $xmlDocumentType = '<foxml:datastream ID="JournalArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="JournalArticleMD1.0" LABEL="Fez extension metadata for Journal Articles">
                            <foxml:xmlContent>
                            <JournalArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <journal>'.htmlspecialchars($importArray[$document_type][$key]['publication'][0]).'</journal>
                            <volume>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</volume>
                            <number>'.htmlspecialchars($importArray[$document_type][$key]['number'][0]).'</number>
                            <pages>'.htmlspecialchars($importArray[$document_type][$key]['pages'][0]).'</pages>
                            </JournalArticleMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'other':
                        $xdis_title = "Generic Document";
                        break;
                    case 'preprint':
                        $xdis_title = "Generic Document";
                        break;
                    case 'thesis':
                        $xdis_title = "Thesis";
                        $xmlDocumentType = '<foxml:datastream ID="ThesisMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ThesisMD1.0" LABEL="Fez extension metadata for Theses">
                            <foxml:xmlContent>
                            <ThesisMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <schooldeptcentre>'.htmlspecialchars($importArray[$document_type][$key]['department'][0]).'</schooldeptcentre>
                            <institution>'.htmlspecialchars($importArray[$document_type][$key]['institution'][0]).'</institution>
                            <thesis_type>'.htmlspecialchars($importArray[$document_type][$key]['thesistype'][0]).'</thesis_type>
                            </ThesisMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'newsarticle':
                        $xdis_title = "Newspaper Article";
                        $xmlDocumentType = '<foxml:datastream ID="NewspaperArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="NewspaperArticleMD1.0" LABEL="Fez extension metadata for Newspaper Articles">
                            <foxml:xmlContent>
                            <NewspaperArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <newspaper>'.htmlspecialchars($importArray[$document_type][$key]['publication'][0]).'</newspaper>
                            <edition>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</edition>
                            <number>'.htmlspecialchars($importArray[$document_type][$key]['number'][0]).'</number>
                            <pages>'.htmlspecialchars($importArray[$document_type][$key]['pages'][0]).'</pages>
                            </NewspaperArticleMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'book':
                        $xdis_title = "Book";
                        $xmlDocumentType = '<foxml:datastream ID="BookMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="BookMD1.0" LABEL="Fez extension metadata for Books">
                            <foxml:xmlContent>
                            <BookMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <edition>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</edition>
                            <series>'.htmlspecialchars($importArray[$document_type][$key]['series'][0]).'</series>
                            <place_of_publication/>
                            </BookMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 						
                        break;
                    case 'bookchapter':
                        $xdis_title = "Book Chapter";
                        $xmlDocumentType = '<foxml:datastream ID="BookChapterMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="BookChapterMD1.0" LABEL="Fez extension metadata for Book Chapters">
                            <foxml:xmlContent>
                            <BookChapterMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <edition>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</edition>
                            <series>'.htmlspecialchars($importArray[$document_type][$key]['series'][0]).'</series>
                            <place_of_publication/>
                            </BookChapterMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 						
                        break;
                    case 'techreport':
                        $xdis_title = "Department Technical Report";
                        $xmlDocumentType = '<foxml:datastream ID="DeptTechReportMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="DeptTechReportMD1.0" LABEL="Fez extension metadata for Departmental Technical Reports">
                            <foxml:xmlContent>
                            <DeptTechReportMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <schooldeptcentre>'.htmlspecialchars($importArray[$document_type][$key]['department'][0]).'</schooldeptcentre>
                            <institution>'.htmlspecialchars($importArray[$document_type][$key]['institution'][0]).'</institution>								  
                            <edition>'.htmlspecialchars($importArray[$document_type][$key]['volume'][0]).'</edition>
                            <series>'.htmlspecialchars($importArray[$document_type][$key]['series'][0]).'</series>
                            </DeptTechReportMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 						
                        break;
                    case 'proceedings':
                        $xdis_title = "Conference Proceedings";
                        $xmlDocumentType = '<foxml:datastream ID="ConferenceProceedingsMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferenceProceedingsMD1.0" LABEL="Fez extension metadata for Conference Proceedings">
                            <foxml:xmlContent>
                            <ConferenceProceedingsMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <conference>'.htmlspecialchars($importArray[$document_type][$key]['conference'][0]).'</conference>
                            <conf_start_date/>
                            <conf_end_date/>
                            <confloc>'.htmlspecialchars($importArray[$document_type][$key]['confloc'][0]).'</confloc>
                            <conf_details>'.htmlspecialchars($importArray[$document_type][$key]['confdates'][0]).'</conf_details>
                            <paper_presentation_date/>
                            <page_numbers>'.htmlspecialchars($importArray[$document_type][$key]['pages'][0]).'</page_numbers>
                            </ConferenceProceedingsMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    case 'confposter':
                        $xdis_title = "Conference Poster";
                        $xmlDocumentType = '<foxml:datastream ID="ConferencePostersMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferencePostersMD1.0" LABEL="Fez extension metadata for Conference Posters">
                            <foxml:xmlContent>
                            <ConferencePostersMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                            <conference>'.htmlspecialchars($importArray[$document_type][$key]['conference'][0]).'</conference>
                            <conf_start_date/>
                            <conf_end_date/>
                            <confloc>'.htmlspecialchars($importArray[$document_type][$key]['confloc'][0]).'</confloc>
                            <conf_details>'.htmlspecialchars($importArray[$document_type][$key]['confdates'][0]).'</conf_details>
                            <poster_presentation_date/>
                            </ConferencePostersMD>
                            </foxml:xmlContent>
                            </foxml:datastreamVersion>
                            </foxml:datastream>'; 
                        break;
                    default:
                        $xdis_title = "Generic Document";	
                        break;
                }
                $xdis_id = XSD_Display::getXDIS_IDByTitle($xdis_title);

                $ret_id = 3; // standard record type id
                $sta_id = 1; // unpublished status type id
                $xsd_id = XSD_Display::getParentXSDID($xdis_id);
                $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
                $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
                $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

                $oai_dc_url = EPRINTS_OAI.$key; // This gets the EPRINTS OAI DC feed for the Eprints DC record. This is neccessary because the Eprints export_xml does not give the URL for the attached PDFs etc
                $oai_dc_xml = Fedora_API::URLopen($oai_dc_url);
                $config = array(
                        'indent' => true,
                        'input-xml' => true,
                        'output-xml' => true,
                        'wrap' => 200);

                $tidy = new tidy;
                $tidy->parseString($oai_dc_xml, $config, 'utf8');
                $tidy->cleanRepair();
                $oai_dc_xml = $tidy;

                $xmlOAIDoc= new DomDocument();
                $xmlOAIDoc->preserveWhiteSpace = false;
                $xmlOAIDoc->loadXML($oai_dc_xml);

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
                    $short_ds = $ds;
                    if (is_numeric(strpos($ds, "/"))) {
                        $short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
                    }
                    // ID must start with _ or letter
                    $short_ds = Foxml::makeNCName($short_ds);
                    $mimetype = Misc::get_content_type($ds);

                    $xmlEnd.= '
                        <foxml:datastream ID="'.$short_ds.'" CONTROL_GROUP="M" STATE="A">
                        <foxml:datastreamVersion ID="'.$short_ds.'.0" MIMETYPE="'.$mimetype.'" LABEL="'.$short_ds.'">
                        <foxml:contentLocation REF="'.htmlspecialchars($ds).'" TYPE="URL"/>
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
                    <dc:title>'.htmlspecialchars($importArray[$document_type][$key]['title'][0]).'</dc:title>
                    ';
                if (is_array($authorArray[$key])) {
                    foreach ($authorArray[$key] as $author) {
                        $xmlObj .= '<dc:creator>'.htmlspecialchars($author).'</dc:creator>
                            ';					    
                    }
                }
                if (is_array($importArray[$document_type][$key]['subjects'])) {
                    foreach ($importArray[$document_type][$key]['subjects'] as $subject) {
                        $xmlObj .= '
                            <dc:subject>'.htmlspecialchars($subject).'</dc:subject>
                            ';	    
                    }
                }

                $xmlObj .= '<dc:description>'.htmlspecialchars($importArray[$document_type][$key]['abstract'][0]).'</dc:description>
                    <dc:publisher>'.htmlspecialchars($importArray[$document_type][$key]['publisher'][0]).'</dc:publisher>
                    <dc:contributor/>
                    <dc:date>'.htmlspecialchars($importArray[$document_type][$key]['datestamp'][0]).'</dc:date>
                    <dc:type>'.$xdis_title.'</dc:type>
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
                    <rel:isMemberOf rdf:resource="info:fedora/'.$collection_pid.'"/>
                    </rdf:description>
                    </rdf:RDF>
                    </foxml:xmlContent>
                    </foxml:datastreamVersion>
                    </foxml:datastream>
                    <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                    <foxml:datastreamVersion MIMETYPE="text/xml" ID="Fez1.0" LABEL="Fez extension metadata">
                    <foxml:xmlContent>
                    <FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                    <xdis_id>'.$xdis_id.'</xdis_id>
                    <sta_id>'.$sta_id.'</sta_id>
                    <ret_id>'.$ret_id.'</ret_id>
                    <created_date>'.$created_date.'</created_date>                      
                    <updated_date>'.$updated_date.'</updated_date>
                    <publication>'.htmlspecialchars($importArray[$document_type][$key]['publication'][0]).'</publication>  
                    <copyright>'.htmlspecialchars($importArray[$document_type][$key]['note'][0]).'</copyright>
                    ';
                if (is_array($keywordArray[$key])) {
                    foreach ($keywordArray[$key] as $keyword) {
                        $xmlObj .= '
                            <keyword>'.htmlspecialchars($keyword).'</keyword>';
                    }
                }
                $xmlObj .= '
                    </FezMD>
                    </foxml:xmlContent>
                    </foxml:datastreamVersion>
                    </foxml:datastream>';

                $xmlObj .= $xmlDocumentType; 

                $xmlObj .= $xmlEnd;

                $xmlObj .= '
                    </foxml:digitalObject>
                    ';
                $config = array(
                        'indent'         => true,
                        'input-xml'   => true,
                        'output-xml'   => true,
                        'wrap'           => 200);

                $tidy = new tidy;
                $tidy->parseString($xmlObj, $config, 'utf8');
                $tidy->cleanRepair();
                $xmlObj = $tidy;

                //echo "\n$xmlObj\n";
                Fedora_API::callIngestObject($xmlObj);
                foreach($oai_ds as $ds) {
                    $presmd_check = Workflow::checkForPresMD($ds); // we are not indexing presMD so just upload the presmd if found
                    if ($presmd_check != false) {
                       Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, 
                                $presmd_check, "text/xml", "X");
                    }			

                    if (is_numeric(strpos($ds, "/"))) {
                        $ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
                    }
                    $ds = str_replace(" ", "_", $ds);
                    //Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the file attachment to the fez index	// this is now done in Record::setIndexMatchingFields more dynamically
                    // Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
                }	  

                // process ingest trigger after all the datastreams are in
                foreach($oai_ds as $ds) {
                    $mimetype = Misc::get_content_type($ds);
                    Workflow::processIngestTrigger($pid, $ds, $mimetype);
                }

                $array_ptr = array();
                $xsdmf_array = array();

                Record::removeIndexRecord($pid); // remove any existing index entry for that PID			
                Record::setIndexMatchingFields($xdis_id, $pid);

                if ($this->bgp) {
                    $this->bgp->setProgress(intval(100*$eprint_record_counter/$num_records)); 
                    $this->bgp->setStatus($importArray[$document_type][$key]['title'][0]); 
                }
                
                $pid = Fedora_API::getNextPID(); // get a new pid for the next loop
            }
        }
        $this->bgp->setStatus("Imported $eprint_record_counter Records"); 
    }

    /**
     * Method used to ingest a FOXML object xml string into fedora.
     * 
     * Developer Note: This was separated into a seperate function as it will be made more complicated in future.
     *
     * @access  public
     * @param   string $xmlObj The string read from the eprints export_xml xml file
     * @return  void
     */
    function handleFOXMLImport($xmlObj) {
        // xml is already in fedora object xml format so just add it
        Fedora_API::callIngestObject($xmlObj); 

    }

    /**
     * Method used to import a METS xml file.
     * 
     * Developer Note: This function works, but probably needs more work to make more user friendly and more testing and work done with METS files for import in general.
     *
     * @access  public
     * @param   string $pid The current persistent identifier
     * @param   string $xmlObj The string read from the eprints export_xml xml file
     * @param   string $xmlBegin The already started xml string for ingestion (the FOXML headers already generated).
     * @param   string $xdis_id The XSD Display ID
     * @return  void
     */
    function handleMETSImport($pid, $xmlObj, $xmlBegin, $xdis_id) {
        $externalDatastreams = array();
        // check for oai_dc, if so add it	  
        $oai_dc = $this->getOAI_DC($xmlObj);
        if ($oai_dc != false) {
            $this->getExternalDatastreams($oai_dc, $externalDatastreams);
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

            $oai_dc = $this->stripTag($oai_dc, "<dc:format>");
            $oai_dc = $this->stripTag($oai_dc, "<dc:identifier>");
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
            $presmd_check = Workflow::checkForPresMD($ds); // we are not indexing presMD so just upload the presmd if found
            if ($presmd_check != false) {
                Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
            }		
            if (is_numeric(strpos($ds, "/"))) {
                $ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
            }
            $ds = str_replace(" ", "_", $ds);
            //Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the thumbnail to the Fez index				
            $mimetype = Misc::get_content_type($ds);
            Workflow::processIngestTrigger($pid, $ds, $mimetype);
        }	  	
        Record::removeIndexRecord($pid); // remove any existing index entry for that PID			
        Record::setIndexMatchingFields($xdis_id, $pid);

        return $xmlBegin;
    }

    /**
     * Method used to import a standard file as a datastream to a new FOXML object.
     * 
     * Developer Note: This function works, but probably needs more work to make more user friendly and more testing.
     *
     * @access  public
     * @param   string $pid The current persistent identifier
     * @param   string $full_name The full directory path inclusive filename 
     * @param   string $short_name The basic filename without the directory path
     * @param   string $xdis_id The XSD Display ID the object will have.	
     * @return  void
     */
    function handleStandardFileImport($pid, $full_name, $short_name, $xdis_id) {

        $mimetype = Misc::get_content_type($full_name);
        //Insert the standard file as a datastream to the new object
        $dsID = Fedora_API::getUploadLocationByLocalRef($pid, $full_name, $full_name, $full_name, $mimetype, "M");	

        // we are not indexing presMD so just upload the presmd if found
        $presmd_check = Workflow::checkForPresMD($full_name); 
        if ($presmd_check != false) {
            Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
        }

        // now add a resource index for the datastream file
        // lowercase the extension if necessary
        if (is_numeric(strpos($short_name, "."))) {
            $filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
            $short_name = substr($short_name, 0, strrpos($short_name, ".") + 1).$filename_ext;
        }
        //		Record::insertIndexMatchingField($pid, 122,  'varchar', $short_name);
        Record::removeIndexRecord($pid); // remove any existing index entry for that PID			
        Record::setIndexMatchingFields($xdis_id, $pid);

        // Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
        Workflow::processIngestTrigger($pid, $full_name, $mimetype);

    }

    /**
     * The main method for batch importing. It opens up each file in the specified directory, scans for content type and imports accordingly.
     * 
     * @access  public
     * @param   array $dsarray 
     * @return  void
     */
    function insert($directory, $xdis_id, $collection_pid, $dsarray=null) {
        //open the current directory
        $ret_id = 3; // standard record type id
        $sta_id = 1; // standard status type id
        $xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));
        $xsd_id = XSD_Display::getParentXSDID($xdis_id);
        $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
        $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
        $xsd_top_element_name = $xsd_details['xsd_top_element_name'];
        $datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
        $parent_pid = $collection_pid;
        $dir_name = APP_SAN_IMPORT_DIR."/".$directory;
        $directory_h = opendir($dir_name);
        while (false !== ($file = readdir($directory_h))) { 
            if (is_file($dir_name."/".$file)) {
                $filenames[$dir_name."/".$file] = $file;
            }
        }
        closedir($directory_h);
        $counter = 0;
        foreach ($filenames as $full_name => $short_name) {
           $counter++;
            $handled_as_xml = false;
            $pid = Fedora_API::getNextPID();
            // Also need to add the FezMD and RELS-EXT - FezACML probably not necessary as it can be inhereted
            // and the FezMD can have status - 'freshly uploaded' or something.

            $filename_ext = strtolower(substr($short_name, (strrpos($short_name, ".") + 1)));
            if ($filename_ext == "xml") {
                $xmlObj = file_get_contents($full_name);
                if (is_numeric(strpos($xmlObj, "foxml:digitalObject"))) {
                    $this->handleFOXMLImport($xmlObj);
                    Record::setIndexMatchingFields($xdis_id, $pid);
                    $handled_as_xml = true;
                } elseif (is_numeric(strpos($xmlObj, "<eprintsdata>"))) {
                    $this->handleEntireEprintsImport($pid, $collection_pid, $xmlObj);
                    $handled_as_xml = true;
                } elseif (is_numeric(strpos($xmlObj, "METS:mets"))) {
                    $xmlBegin = $this->ConvertMETSToFOXML($pid, $xmlObj, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id);
                    $xmlObj = $this->handleMETSImport($pid, $xmlObj, $xmlBegin, $xdis_id);
                    $handled_as_xml = true;
                }
            } 
            if (!$handled_as_xml) {
                if ($this->bgp) {
                    $this->bgp->setProgress(intval($counter*100/count($filenames))); 
                    $this->bgp->setStatus($short_name); 
                }
                // Create the Record in Fedora 
                if (empty($dsarray)) {
                    // use default metadata
                    $xmlObj = Foxml::GenerateSingleFOXMLTemplate($pid, $parent_pid, $full_name, $short_name,
                            $xdis_id, $ret_id, $sta_id);
                    //Insert the generated foxml object
                    Fedora_API::callIngestObject($xmlObj);
                    Record::setIndexMatchingFields($xdis_id, $pid);
                } else {
                    // use metadata from a user template
                    Record::insertFromTemplate($pid, $xdis_id, $short_name, $dsarray);
                }
                // add the binary batch import file.
                $this->handleStandardFileImport($pid, $full_name, $short_name, $xdis_id);
                if ($this->bgp) {
                    $this->bgp->setStatus('Imported '.count($filenames).' files'); 
                }
            }

        }
        if ($this->bgp) {
            $this->bgp->setProgress(100); 
        }
    }




    /**
     * Method used to extra the OAI dublin core metadata from an xml string.
     * 
     * @access  public
     * @return  string $xmlObj The xml object 
     * @return  string $xmlCut The OAI DC extracted, or boolean false if not found
     */
    function getOAI_DC($xmlObj) {
        $tagOpen = '<oai_dc:dc';
        $tagClose = "</oai_dc:dc>";	
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

    /**
     * Method used to remove a specific tag from an xml string.
     * 
     * @access  public
     * @return  string $xmlObj The xml object 
     * @return  string $tag The tag to be removed
     * @return  string $xmlObj The xml string without the tag
     */
    function stripTag($xmlObj, $tag) {
        $tagClose = "\<\/".substr($tag, 1);
        $xmlObj = preg_replace("/(\\".$tag.")(.*?)(".$tagClose.")/", "", $xmlObj);
        return $xmlObj;	
    }

    /**
     * Method get the external datastreams from an ePrints object.
     * 
     * @access  public
     * @return  string $xmlObj The xml object 
     * @return  array $externalDatastreams 
     * @return  array $externalDatastreams by reference with all the ePrints files (pdfs) 
     */
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
                $this->getExternalDatastreams($xmlObj, $externalDatastreams);
            }
        } 
    }

    /**
     * Method used to convert a METS xml record into a FOXML record.
     * 
     * @access  public
     * @param   string $pid The current persistent identifier
     * @param   string $xmlImport the string of the METS xml file
     * @param   string $collection_pid The pid of the collection this will belong to.
     * @param   string $short_name The filename of the file being imported, without the directory path (basic filename)
     * @param   string $xdis_id The XSD Display ID the object will have.
     * @param   string $ret_id The object type ID the object will have.
     * @param   string $sta_id The initial status ID the object will have.
     * @return  string $xmlObj The xml object 
     */
    function ConvertMETSToFOXML($pid, $xmlImport, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id) {
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
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="Fez1.0" LABEL="Fez extension metadata">
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
