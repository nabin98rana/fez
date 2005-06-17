<?php
/*
This file will create the metsxml for new objects.
   New object mets
   New DC - DS mets (needs repeatable elements coded in: subject, identifier)
   New NSDL_DC DS mets (same as above)

Written by Elly Cramer 2004 - elly@cs.cornell.edu
Modified by Christiaan Kortekaas 2005 for eSpace - c.kortekaas@library.uq.edu.au
**/
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");

class Foxml_Template {


function getDC ($formFieldArray, $typeDC, $pid) {
    //reusable vars
    $dcDate = '<dc:date>'.gmdate("Y-m-d\TH:i:s", time()).'</dc:date>';
    $dcLanguage = '<dc:language>en</dc:language>';
    // OAI_DC and NSDL_DC xml: title, desc, identifier, subject, type, format, contributor, creator, audience
    $oai_dc = '<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
	<dc:identifier>http://cms.nsdl.org/get/'.$pid.'</dc:identifier>'
	. $dcDate . $dcLanguage;
    $nsdl_dc = '<nsdl_dc:nsdl_dc xmlns:nsdl_dc="http://ns.nsdl.org/nsdl_dc_v1.02/" schemaVersion="1.02.000" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <dc:identifier>http://cms.nsdl.org/get/'.$pid.'</dc:identifier>' . $dcDate . $dcLanguage;
    foreach ($formFieldArray as $fieldName => $fieldValue) {
        if ($fieldName == 'description' || $fieldName == 'contributor' || $fieldName == 'creator' || $fieldName == 'audience') {
            $oai_dc .= '<dc:'.$fieldName.'>'.$fieldValue.'</dc:'.$fieldName.'>';
            $nsdl_dc .= '<dc:'.$fieldName.'>'.$fieldValue.'</dc:'.$fieldName.'>';
        } elseif (eregi('label', $fieldName)) {
            $oai_dc .= '<dc:title>'.$fieldValue.'</dc:title>';
            $nsdl_dc .= '<dc:title>'.$fieldValue.'</dc:title>';
        } elseif (eregi('group', $fieldName)) {
	    foreach ($fieldValue as $subjectValue) {
               $oai_dc .= '<dc:subject>group:'.$subjectValue.'</dc:subject>';
               $nsdl_dc .= '<dc:subject  xsi:type="nsdl_dc:nsdlgroup">'.$subjectValue.'</dc:subject>';
            }
        } elseif (eregi('keyword', $fieldName)) {
	    foreach ($fieldValue as $subjectValue) {
               $oai_dc .= '<dc:subject>'.$subjectValue.'</dc:subject>';
               $nsdl_dc .= '<dc:subject>'.$subjectValue.'</dc:subject>';
	    }
        } elseif (eregi('format', $fieldName)) {
            $oai_dc .= '<dc:format>'.$fieldValue.'</dc:format>';
            $nsdl_dc .= '<dc:format xsi:type="dct:IMT">'.$fieldValue.'</dc:format>';
        } elseif (eregi('type', $fieldName)) {
            $oai_dc .= '<dc:type>'.$fieldValue.'</dc:type>';
            $nsdl_dc .= '<dc:type xsi:type="dct:DCMIType">'.$fieldValue.'</dc:type>';
        } elseif (eregi('audience', $fieldName)) {
            //not present in oai_dc
            $nsdl_dc .= '<dct:audience>'.$fieldValue.'</dct:audience>';
        }    
    }
    
    $oai_dc .= '</oai_dc:dc>';
    $nsdl_dc .= '</nsdl_dc:nsdl_dc>';
    
    if ($typeDC == 'oai_dc') {
        return $oai_dc;
    } elseif ($typeDC == 'nsdl_dc') {
        return $nsdl_dc;
    }
}

function getMetsxml ($formFieldArray, $pid) {
	global $_REQUEST;
    $metsxml = '<?xml version="1.0" encoding="UTF-8"?>
    <METS:mets LABEL="'.$_REQUEST['label'].'" OBJID="'.$pid.'" PROFILE="'.$_REQUEST['cmodel'].'" TYPE="FedoraObject"
      xmlns:METS="http://www.loc.gov/METS/" xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:fedoraAudit="http://fedora.comm.nsdlib.org/audit" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
      xmlns:xlink="http://www.w3.org/TR/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/METS/ http://www.fedora.info/definitions/1/0/mets-fedora-ext.xsd">
      <METS:metsHdr RECORDSTATUS="A"/>
      <METS:dmdSecFedora ID="DC" STATUS="A">
        <METS:descMD CREATED="'.gmdate("Y-m-d\TH:i:s", time()).'" ID="DC1.0">
          <METS:mdWrap LABEL="Dublin Core Metadata" MDTYPE="OTHER" MIMETYPE="text/xml" OTHERMDTYPE="UNSPECIFIED">
            <METS:xmlData>'
              .Foxml_Template::getDC($formFieldArray, 'oai_dc', $pid)
            .'</METS:xmlData>
          </METS:mdWrap>
        </METS:descMD>
      </METS:dmdSecFedora>
      <METS:dmdSecFedora ID="NSDL_DC" STATUS="A">
        <METS:descMD CREATED="'.gmdate("Y-m-d\TH:i:s", time()).'" ID="NSDL_DC1.0">
          <METS:mdWrap LABEL="NSDL_DC Metadata" MDTYPE="OTHER" MIMETYPE="text/xml" OTHERMDTYPE="UNSPECIFIED">
            <METS:xmlData>'
              .Foxml_Template::getDC($formFieldArray, 'nsdl_dc', $pid)
            .'</METS:xmlData>
          </METS:mdWrap>
        </METS:descMD>
      </METS:dmdSecFedora>
    </METS:mets>';
    
    return $metsxml;
}

function getFoxml ($formFieldArray, $pid) {
	global $_REQUEST;
    $foxml = '<?xml version="1.0" encoding="UTF-8"?>
    <foxml:digitalObject PID="'.$pid.'" URI="info:fedora/'.$pid.'" xmlns:METS="http://www.loc.gov/METS/" xmlns:audit="info:fedora/fedora-system:def/audit#" xmlns:fedoraAudit="http://www.fedora.info/definitions/audit" xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xlink="http://www.w3.org/TR/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd">
         <foxml:objectProperties>
              <foxml:property NAME="info:fedora/fedora-system:def/fType" VALUE="FedoraObject"/>
              <foxml:property NAME="info:fedora/fedora-system:def/label" VALUE="'.$_REQUEST['label'].'"/>
              <foxml:property NAME="info:fedora/fedora-system:def/cModel" VALUE="'.$_REQUEST['cmodel'].'"/>
         </foxml:objectProperties>
         <foxml:datastream CONTROL_GROUP="X" FORMAT_URI="null" ID="DC" MIMETYPE="text/xml" STATE="A" URI="info:fedora/nsdl:1000/DC" VERSIONABLE="YES">
              <foxml:datastreamVersion ID="DC1.0" LABEL="Dublin Core Metadata" SIZE="256">
                   <foxml:xmlContent>'
                    .Foxml_Template::getDC($formFieldArray, 'oai_dc', $pid)
                   .'</foxml:xmlContent>
                   <foxml:contentDigest DIGEST="future: hash of content goes here" TYPE="MD5"/>
              </foxml:datastreamVersion>
         </foxml:datastream>
         <foxml:datastream CONTROL_GROUP="X" FORMAT_URI="null" ID="NSDL_DC" MIMETYPE="text/xml" STATE="A" URI="info:fedora/nsdl:1000/NSDL_DC" VERSIONABLE="YES">
              <foxml:datastreamVersion ID="NSDL_DC1.0" LABEL="NSDL_DC Metadata" SIZE="256">
                   <foxml:xmlContent>'
                    .Foxml_Template::getDC($formFieldArray, 'nsdl_dc', $pid)
                   .'</foxml:xmlContent>
                   <foxml:contentDigest DIGEST="future: hash of content goes here" TYPE="MD5"/>
              </foxml:datastreamVersion>
         </foxml:datastream>
    </foxml:digitalObject>    
    ';
    
    return $foxml;
}

} // End of Foxml_Template Class

?>
