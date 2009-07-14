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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle ePrints queries for data that can't be accessed in the eprints export XML for a comprehensive migration
 * The special thing about this class is that it uses a different database connection for connecting to the ePrints database
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 *
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.record.php");
include_once("DB.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.auth.php");


class ePrints

{

	function ePrints() 
	{
		$params = array(
		            'host' => EPRINTS_DB_HOST,            
		            'username' => EPRINTS_DB_USERNAME,
		            'password' => EPRINTS_DB_PASSWD,
		        	'dbname' => EPRINTS_DB_DATABASE_NAME,
					'profiler' => array(
						'enabled' 	=> APP_DB_USE_PROFILER,
						'class' 	=> 'Zend_Db_Profiler_Firebug'
					)
		);
		        
		try {
			$db = Zend_Db::factory(EPRINTS_DB_TYPE, $params);
			$db->getConnection();
			Zend_Registry::set('eprintsdb', $db);   
		}
		catch (Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));		        
			exit;
		}
	}

	function getUserDetails($usr_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select * from ".EPRINTS_DB_DATABASE_NAME.".users where userid = ".$db->quote($usr_id, 'INTEGER');
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}

	function getList($table) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select * from ".EPRINTS_DB_DATABASE_NAME.".$table order by eprintid desc";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}

	function getUserList($table) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select distinct u.* from ".EPRINTS_DB_DATABASE_NAME.".$table u, archive a where a.userid = u.userid and a.userid is not null order by u.userid asc";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}

	function getListCount($table) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select count(*) as list_count from ".EPRINTS_DB_DATABASE_NAME.".$table";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}
		
	function getSucceeds($eprint_id, $table) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select succeeds from ".EPRINTS_DB_DATABASE_NAME.".$table where eprintid = ".$db->quote($eprint_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function getPIDfromePrintID($eprint_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select epr_fez_pid from  " . APP_TABLE_PREFIX . "eprints_import_pids where epr_eprints_id = ".$db->quote($eprint_id, 'INTEGER');
		$stmt = "select succeeds from ".EPRINTS_DB_DATABASE_NAME.".$table where eprintid = ".$db->quote($eprint_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		return $res;
	}

	function getListEprintID($eprintid, $table, $extension) 
	{
		$log = FezLog::get();
		$db = DB_API::get('eprintsdb');
		
		$stmt = "select * from ".EPRINTS_DB_DATABASE_NAME.".$table$extension where eprintid = ".$db->quote($eprintid, 'INTEGER')." order by pos asc";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return array();
		}
		return $res;
	}

	function importFromSQL($pid, $collection_pid, $xmlObj, $batch_import_object, $use_MODS=1)
	{
		$created_date = Date_API::getFedoraFormattedDateUTC();
		$updated_date = $created_date;
		$eprint_record_counter = 0;
		if (BATCH_IMPORT_TYPE == "Dublin Core 1.0") {
			$use_MODS = 0;
		} else {
			$use_MODS = 1;
		}
		if ($use_MODS == 1) {
			$xdis_version = "MODS 1.0";
		} else {
			$xdis_version = "Dublin Core 1.0";
		}

		$object_types = array("archive" => "2");
		$record_count = 0;
		foreach ($object_types as $table => $sta_id) {
			$record_count += $this->getListCount($table);
		}
		if (EPRINTS_IMPORT_USERS == "ON") {
			//Add the ePrint users
			$epUsers = $this->getUserList("users");
			foreach ($epUsers as $epUser) {
				$fez_usr_id = User::getIDByExtID($epUser['userid']);
				if (!is_numeric($fez_usr_id)) {
					User::insertFromEprints($epUser['username'], $epUser['name_given']." ".$epUser['name_family'], $epUser['email'], $epUser['userid']);
				}
			}
		}
			
		foreach ($object_types as $table => $sta_id) {
			$list = $this->getList($table);
			foreach ($list as $record) {
				$eprintid = $record["eprintid"];
				$document_type = $record["type"];
				$author_list = $this->getListEprintID($eprintid, $table, "_authors");
				$authorArray[$eprintid] = array();
				$authorArrayExtra[$eprintid] = array();
				$author_counter = 0;
				foreach ($author_list as $author) {
					$given = $author["authors_given"];
					$family = $author["authors_family"];
					$fez_author_id = "";
					if ($given != "" && $family != "") {
						$fez_author_id = Author::getIDByName($given, $family);
					}
					if (!is_array($authorArrayExtra[$eprintid])) {
						$authorArrayExtra[$eprintid] = array();
					}
					if (!is_array($authorArrayExtra[$eprintid][$author_counter])) {
						$authorArrayExtra[$eprintid][$author_counter] = array();

					}
					$authorArrayExtra[$eprintid][$author_counter]["fullname"] =  $family.", ".$given;
					if ($fez_author_id != "") {
						$authorArrayExtra[$eprintid][$author_counter]["id"] =  $fez_author_id;
					}
					array_push($authorArray[$eprintid], $family.", ".$given);
					$author_counter++;
				}
				$editor_list = $this->getListEprintID($eprintid, $table, "_editors");
				$editorArray[$eprintid] = array();
				$editorArrayExtra[$eprintid] = array();
				$author_counter = 0;
				foreach ($editor_list as $editor) {
					$given = $editor["editors_given"];
					$family = $editor["editors_family"];
					$fez_editor_id = "";
					if ($given != "" && $family != "") {
						$fez_editor_id = Author::getIDByName($given, $family);
					}
					if (!is_array($editorArrayExtra[$eprintid])) {
						$editorArrayExtra[$eprintid] = array();
					}
					if (!is_array($editorArrayExtra[$eprintid][$author_counter])) {
						$editorArrayExtra[$eprintid][$author_counter] = array();
					}
					$editorArrayExtra[$eprintid][$author_counter]["fullname"] = $family.", ".$given;
					if ($fez_editor_id != "") {
						$editorArrayExtra[$eprintid][$author_counter]["id"] =  $fez_editor_id;
					}
						
					array_push($editorArray[$eprintid], $family.", ".$given);
					$author_counter++;
				}
				//				Error_Handler::logError($authorArrayExtra);
				$pagesArray = BatchImport::getEprintsPages($record['pages']);
				$subject_list = $this->getListEprintID($eprintid, $table, "_subjects");
				$record['subjects'] = $subject_list;
				$keywordArray = BatchImport::getEprintsKeywords($record['keywords']);
				$keywordXML = "";
				foreach ($keywordArray as $kw) {
					$keywordXML .= '<mods:subject authority="keyword"><mods:topic>'.$kw.'</mods:topic></mods:subject>';
						
				}

				switch ($document_type) {
					case 'confpaper':
						$xdis_title = "Conference Paper";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Conference Paper</mods:genre>
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="conference">'.htmlspecialchars(@$record['conference']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
								      <mods:place>
								        <mods:placeTerm type="text">'.htmlspecialchars(@$record['confloc']).'</mods:placeTerm>
								      </mods:place>
								      <mods:dateOther>'.htmlspecialchars(@$record['confdates']).'</mods:dateOther>
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="issue">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:detail type="volume">
								        <mods:number>'.htmlspecialchars(@$record['volume']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 															
						} else {
							$xmlDocumentType = '<foxml:datastream ID="ConferencePaperMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferencePaperMD1.0" LABEL="Fez extension metadata for Conference Papers">
	                            <foxml:xmlContent>
	                            <ConferencePaperMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <conference>'.htmlspecialchars(@$record['conference']).'</conference>
	                            <conf_start_date/>
	                            <conf_end_date/>
	                            <confloc>'.htmlspecialchars(@$record['confloc']).'</confloc>
	                            <conf_details>'.htmlspecialchars(@$record['confdates']).'</conf_details>
	                            </ConferencePaperMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 																							
						}
						break;
					case 'journale':
						$xdis_title = "Online Journal Article";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Online Journal Article</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="journal">'.htmlspecialchars(@$record['publication']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
									    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="issue">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:detail type="volume">
								        <mods:number>'.htmlspecialchars(@$record['volume']).'</mods:number>
								      </mods:detail>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="OnlineJournalArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="OnlineJournalArticleMD1.0" LABEL="Fez extension metadata for Online Journal Articles">
	                            <foxml:xmlContent>
	                            <OnlineJournalArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <journal>'.htmlspecialchars(@$record['publication']).'</journal>
	                            <volume>'.htmlspecialchars(@$record['volume']).'</volume>
	                            <number>'.htmlspecialchars(@$record['number']).'</number>
	                            </OnlineJournalArticleMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						}
						break;
					case 'journalp':
						$xdis_title = "Journal Article";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Journal Article</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="journal">'.htmlspecialchars(@$record['publication']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
									    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="issue">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:detail type="volume">
								        <mods:number>'.htmlspecialchars(@$record['volume']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="JournalArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="JournalArticleMD1.0" LABEL="Fez extension metadata for Journal Articles">
	                            <foxml:xmlContent>
	                            <JournalArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <journal>'.htmlspecialchars(@$record['publication']).'</journal>
	                            <volume>'.htmlspecialchars(@$record['volume']).'</volume>
	                            <number>'.htmlspecialchars(@$record['number']).'</number>
	                            <pages>'.htmlspecialchars(@$record['pages']).'</pages>
	                            </JournalArticleMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 
						}
						break;
					case 'preprint':
						$xdis_title = "Preprint";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>				
								  <mods:genre>Preprint</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							// Preprints are basically generic documents but with a better flag
						}
						break;

					case 'other':
						$xdis_title = "Generic Document";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>				
								  <mods:genre>Generic Document</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							//
						}
						break;
					case 'thesis':
						$xdis_title = "Thesis";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre type="'.htmlspecialchars(@$record['thesistype']).'">Thesis</mods:genre>
							      <mods:originInfo>
								    <mods:publisher>'.htmlspecialchars(@$record['publisher']).'</mods:publisher>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								  </mods:originInfo>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="school">'.htmlspecialchars(@$record['department']).'</mods:namePart>
								    </mods:name>
								    <mods:name>
								      <mods:namePart type="institution">'.htmlspecialchars(@$record['institution']).'</mods:namePart>
								    </mods:name>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="ThesisMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ThesisMD1.0" LABEL="Fez extension metadata for Theses">
	                            <foxml:xmlContent>
	                            <ThesisMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <schooldeptcentre>'.htmlspecialchars(@$record['department']).'</schooldeptcentre>
	                            <institution>'.htmlspecialchars(@$record['institution']).'</institution>
	                            <thesis_type>'.htmlspecialchars(@$record['thesistype']).'</thesis_type>
	                            </ThesisMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 
						}
						break;
					case 'newsarticle':
						$xdis_title = "Newspaper Article";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Newspaper Article</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:titleInfo>
								      <mods:title>'.htmlspecialchars(@$record['publication']).'</mods:title>
								    </mods:titleInfo>
								    <mods:originInfo>
										<mods:publisher>'.htmlspecialchars(@$record['publisher']).'</mods:publisher>								    						  
									    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="newspaper">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="NewspaperArticleMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="NewspaperArticleMD1.0" LABEL="Fez extension metadata for Newspaper Articles">
	                            <foxml:xmlContent>
	                            <NewspaperArticleMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <newspaper>'.htmlspecialchars(@$record['publication']).'</newspaper>
	                            <edition>'.htmlspecialchars(@$record['volume']).'</edition>
	                            <number>'.htmlspecialchars(@$record['number']).'</number>
	                            <pages>'.htmlspecialchars(@$record['pages']).'</pages>
	                            </NewspaperArticleMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 
						}
						break;
					case 'book':
						$xdis_title = "Book";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Book</mods:genre>
							      <mods:originInfo>
								    <mods:publisher>'.htmlspecialchars(@$record['publisher']).'</mods:publisher>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								  </mods:originInfo>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="series">'.htmlspecialchars(@$record['series']).'</mods:namePart>
								    </mods:name>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="BookMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="BookMD1.0" LABEL="Fez extension metadata for Books">
	                            <foxml:xmlContent>
	                            <BookMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <edition>'.htmlspecialchars(@$record['volume']).'</edition>
	                            <series>'.htmlspecialchars(@$record['series']).'</series>
	                            <place_of_publication/>
	                            </BookMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						}
						break;
					case 'bookchapter':
						$xdis_title = "Book Chapter";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Book Chapter</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:titleInfo>
								      <mods:title>'.htmlspecialchars(@$record['publication']).'</mods:title>
								    </mods:titleInfo>								  
								    <mods:name>
								      <mods:namePart type="series">'.htmlspecialchars(@$record['series']).'</mods:namePart>
								    </mods:name>
							      <mods:originInfo>
								    <mods:publisher>'.htmlspecialchars(@$record['publisher']).'</mods:publisher>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>								    
								  </mods:originInfo>
								  <mods:part>
								      <mods:detail type="chapter">
								        <mods:number>'.htmlspecialchars(@$record['chapter']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>							    
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 						
						} else {
							$xmlDocumentType = '<foxml:datastream ID="BookChapterMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="BookChapterMD1.0" LABEL="Fez extension metadata for Book Chapters">
	                            <foxml:xmlContent>
	                            <BookChapterMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <edition>'.htmlspecialchars(@$record['volume']).'</edition>
	                            <series>'.htmlspecialchars(@$record['series']).'</series>
	                            <place_of_publication/>
	                            </BookChapterMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						}
						break;
					case 'techreport':
						$xdis_title = "Department Technical Report";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>				
								  <mods:genre>Department Technical Report</mods:genre>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="series">'.htmlspecialchars(@$record['series']).'</mods:namePart>
								    </mods:name>								  
								    <mods:name>
								      <mods:namePart type="school">'.htmlspecialchars(@$record['department']).'</mods:namePart>
								    </mods:name>
								    <mods:name>
								      <mods:namePart type="institution">'.htmlspecialchars(@$record['institution']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
										<mods:publisher>'.htmlspecialchars(@$record['publisher']).'</mods:publisher>								    						  
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="report">
								        <mods:number>'.htmlspecialchars(@$record['reportno']).'</mods:number>
								      </mods:detail>
									</mods:part>								      
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						} else {
							$xmlDocumentType = '<foxml:datastream ID="DeptTechReportMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="DeptTechReportMD1.0" LABEL="Fez extension metadata for Departmental Technical Reports">
	                            <foxml:xmlContent>
	                            <DeptTechReportMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <schooldeptcentre>'.htmlspecialchars(@$record['department']).'</schooldeptcentre>
	                            <institution>'.htmlspecialchars(@$record['institution']).'</institution>								  
	                            <report_number>'.htmlspecialchars(@$record['reportno']).'</report_number>
	                            <series>'.htmlspecialchars(@$record['series']).'</series>
	                            </DeptTechReportMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						}
						break;
					case 'proceedings':
						$xdis_title = "Conference Proceedings";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Conference Proceedings</mods:genre>
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="conference">'.htmlspecialchars(@$record['conference']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
								      <mods:place>
								        <mods:placeTerm type="text">'.htmlspecialchars(@$record['confloc']).'</mods:placeTerm>
								      </mods:place>
								      <mods:dateOther>'.htmlspecialchars(@$record['confdates']).'</mods:dateOther>
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="issue">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:detail type="volume">
								        <mods:number>'.htmlspecialchars(@$record['volume']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 															
						} else {
							$xmlDocumentType = '<foxml:datastream ID="ConferenceProceedingsMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferenceProceedingsMD1.0" LABEL="Fez extension metadata for Conference Proceedings">
	                            <foxml:xmlContent>
	                            <ConferenceProceedingsMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <conference>'.htmlspecialchars(@$record['conference']).'</conference>
	                            <conf_start_date/>
	                            <conf_end_date/>
	                            <confloc>'.htmlspecialchars(@$record['confloc']).'</confloc>
	                            <conf_details>'.htmlspecialchars(@$record['confdates']).'</conf_details>
	                            <paper_presentation_date/>
	                            <page_numbers>'.htmlspecialchars(@$record['pages']).'</page_numbers>
	                            </ConferenceProceedingsMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>';
						}
						break;
					case 'confposter':
						$xdis_title = "Conference Poster";
						if ($use_MODS == 1) {
							$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
	                            <foxml:xmlContent>
								<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
								  <mods:titleInfo>
								    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
								  </mods:titleInfo>';	
							$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
							$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
							$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
							$xmlDocumentType .= $keywordXML.'
								  <mods:genre>Conference Poster</mods:genre>
								  <mods:originInfo>
								    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
								  </mods:originInfo>
								  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
								  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
								  <mods:relatedItem type="host">
								    <mods:name>
								      <mods:namePart type="conference">'.htmlspecialchars(@$record['conference']).'</mods:namePart>
								    </mods:name>
								    <mods:originInfo>
								      <mods:place>
								        <mods:placeTerm type="text">'.htmlspecialchars(@$record['confloc']).'</mods:placeTerm>
								      </mods:place>
								      <mods:dateOther>'.htmlspecialchars(@$record['confdates']).'</mods:dateOther>
								    </mods:originInfo>
								    <mods:part>
								      <mods:detail type="issue">
								        <mods:number>'.htmlspecialchars(@$record['number']).'</mods:number>
								      </mods:detail>
								      <mods:detail type="volume">
								        <mods:number>'.htmlspecialchars(@$record['volume']).'</mods:number>
								      </mods:detail>
								      <mods:extent unit="page">
								        <mods:start>'.$pagesArray[0].'</mods:start>
								        <mods:end>'.$pagesArray[1].'</mods:end>
								      </mods:extent>
								    </mods:part>
								  </mods:relatedItem>
								</mods:mods>								  
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 															
						} else {
							$xmlDocumentType = '<foxml:datastream ID="ConferencePostersMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
	                            <foxml:datastreamVersion MIMETYPE="text/xml" ID="ConferencePostersMD1.0" LABEL="Fez extension metadata for Conference Posters">
	                            <foxml:xmlContent>
	                            <ConferencePostersMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
	                            <conference>'.htmlspecialchars(@$record['conference']).'</conference>
	                            <conf_start_date/>
	                            <conf_end_date/>
	                            <confloc>'.htmlspecialchars(@$record['confloc']).'</confloc>
	                            <conf_details>'.htmlspecialchars(@$record['confdates']).'</conf_details>
	                            <poster_presentation_date/>
	                            </ConferencePostersMD>
	                            </foxml:xmlContent>
	                            </foxml:datastreamVersion>
	                            </foxml:datastream>'; 
						}
						break;
					default:
						$xdis_title = "Generic Document";
						//echo "Unrecognised record type $document_type\n";
						break;
				}


				if ($use_MODS == 1 && $xdis_title == "Generic Document") {
					$xmlDocumentType = '<foxml:datastream ID="MODS" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                        <foxml:datastreamVersion MIMETYPE="text/xml" ID="MODS.0" LABEL="Metadata Object Description Schema">
                        <foxml:xmlContent>
						<mods:mods xmlns:mods="http://www.loc.gov/mods/v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
						  <mods:titleInfo>
						    <mods:title>'.htmlspecialchars(@$record['title']).'</mods:title>
						  </mods:titleInfo>';	
					$xmlDocumentType .= BatchImport::createMODSName($authorArrayExtra, $eprintid, "author");
					$xmlDocumentType .= BatchImport::createMODSName($editorArrayExtra, $eprintid, "editor");
					$xmlDocumentType .= BatchImport::createMODSSubject($record['subjects'], $document_type, $eprintid);
					$xmlDocumentType .= $keywordXML.'
						  <mods:genre>Generic Document</mods:genre>
						  <mods:originInfo>
						    <mods:dateIssued>'.htmlspecialchars(BatchImport::getEprintsDate(@$record['year'], @$record['month'])).'</mods:dateIssued>
						  </mods:originInfo>
						  <mods:abstract>'.htmlspecialchars(@$record['abstract']).'</mods:abstract>								  
						  <mods:note>'.htmlspecialchars(@$record['note']).'</mods:note>
						</mods:mods>								  
                        </foxml:xmlContent>
                        </foxml:datastreamVersion>
                        </foxml:datastream>'; 															
				}






				$xdis_id = XSD_Display::getXDIS_IDByTitleVersion($xdis_title, $xdis_version);

				$ret_id = 3; // standard record type id
				$xsd_id = XSD_Display::getParentXSDID($xdis_id);
				$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
				$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
				$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
					

				$oai_dc_url = EPRINTS_OAI.$eprintid; // This gets the EPRINTS OAI DC feed for the Eprints DC record. This is neccessary because the Eprints export_xml does not give the URL for the attached PDFs etc
				$oai_dc_xml = Misc::processURL($oai_dc_url);
				$oai_dc_xml = $oai_dc_xml[0];
				//                $oai_dc_xml = Fedora_API::URLopen($oai_dc_url);
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
				// Don't want to do it like this anymore, add them later so you can controll eg for secure eprints files
				/*                foreach($oai_ds as $ds) {
				 $short_ds = $ds;
				 if (is_numeric(strpos($ds, "/"))) {
				 $short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
				 }
				 // ID must start with _ or letter
				 $short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 20);
				 $mimetype = Misc::get_content_type($ds);

				 $xmlEnd.= '
				 <foxml:datastream ID="'.$short_ds.'" CONTROL_GROUP="M" STATE="A">
				 <foxml:datastreamVersion ID="'.$short_ds.'.0" MIMETYPE="'.$mimetype.'" LABEL="'.$short_ds.'">
				 <foxml:contentLocation REF="'.htmlspecialchars($ds).'" TYPE="URL"/>
				 </foxml:datastreamVersion>
				 </foxml:datastream>';
				 }
				 */
				$xmlObj = '<?xml version="1.0" ?>
                    <foxml:digitalObject PID="'.$pid.'"
                    fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                    <foxml:objectProperties>
                    <foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
                    <foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
                    <foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="Batch Import ePrint Record '.$eprintid.'"/>
                    </foxml:objectProperties>';
					
				$xmlObj .= '
                    <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                    <foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
                    <foxml:xmlContent>
                    <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                    <dc:title>'.htmlspecialchars(@$record['title']).'</dc:title>
                    ';
				if (is_array(@$authorArray[$eprintid])) {
					foreach ($authorArray[$eprintid] as $author) {
						$xmlObj .= '<dc:creator>'.htmlspecialchars($author).'</dc:creator>
                            ';					    
					}
				}
				if (is_array(@$record['subjects'])) {
					foreach (@$record['subjects'] as $subject) {
						$xmlObj .= '
                            <dc:subject>'.htmlspecialchars($subject['subjects']).'</dc:subject>
                            ';	    
					}
				}

				$xmlObj .= '<dc:description>'.htmlspecialchars(@$record['abstract']).'</dc:description>
                    <dc:publisher>'.htmlspecialchars(@$record['publisher']).'</dc:publisher>
                    <dc:contributor/>
                    <dc:date dateType="1">'.htmlspecialchars(@$record['year']).'-01-01</dc:date>
                    <dc:type>'.$xdis_title.'</dc:type>
                    <dc:source/>
                    <dc:language/>
                    <dc:relation/>
                    <dc:coverage/>
                    <dc:rights>'.htmlspecialchars(@$record['note']).'</dc:rights>
                    </oai_dc:dc>
                    </foxml:xmlContent>			
                    </foxml:datastreamVersion>
                    </foxml:datastream>
                    <foxml:datastream ID="RELS-EXT" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                    <foxml:datastreamVersion MIMETYPE="text/xml" ID="RELS-EXT.0" LABEL="Relationships to other objects">
                    <foxml:xmlContent>';		
					
				$relsext[$pid]['eprint_id'] = $eprintid;
				$relsext[$pid]['succeeds'] = ePrints::getSucceeds($eprintid, $table);
				$relsext[$pid]['xml'] = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                    xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                    <rdf:description rdf:about="info:fedora/'.$pid.'">
                    <rel:isMemberOf rdf:resource="info:fedora/'.$collection_pid.'"/>
                    ';
				$xmlObj .= $relsext[$pid]['xml'];
				$xmlObj .= '</rdf:description>
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
                    <created_date>'.htmlspecialchars(@$record['datestamp']).'</created_date>                      
                    <updated_date>'.$updated_date.'</updated_date>
                    <publication>'.htmlspecialchars(@$record['publication']).'</publication>  
                    <copyright>on</copyright>
                    ';
				if (@$record['refereed'] == 'TRUE') {
					$xmlObj .= "<refereed>on</refereed>";
				} else {
					$xmlObj .= "<refereed/>";
				}
				if (is_numeric(@$record['userid'])) {
					$xmlObj .= "<depositor>".User::getIDByExtID($record['userid'])."</depositor>";
				} else {
					$xmlObj .= "<depositor/>";
				}
					
				/*                if (is_array(@$keywordArray[$eprintid])) {
				 foreach ($keywordArray[$eprintid] as $keyword) {
				 $xmlObj .= '
				 <keyword>'.htmlspecialchars($keyword).'</keyword>';
				 }
				 } */
				$xmlObj .= '
                    <reference_text>'.htmlspecialchars(@$record['referencetext']).'</reference_text>                  
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
				BatchImport::saveEprintPid($eprintid, $pid); // save the eprint id against its new Fedora/Fez pid so it can be used with a mod-rewrite redirect for the ePrints record and bringing across stats
				$result = Fedora_API::callIngestObject($xmlObj);
				if (is_array($result)) {
					$errMsg =  "The article \"{$record['title']}\" had the following error:\n"
					.print_r($result,true)."\n";
					//                    $errMsg = "\n$xmlObj\n";
					Error_Handler::logError("$errMsg \n", __FILE__,__LINE__);

				}

				$altlocs_list = $this->getListEprintID($eprintid, $table, "_altloc");
				$link_number = 1;
				if (APP_VERSION_UPLOADS_AND_LINKS == "ON") {
					$versionable = "true";
				} else {
					$versionable = "false";
				}

				foreach ($altlocs_list as $altloc) {
					Fedora_API::callAddDatastream($pid, "link_".$link_number, $altloc['altloc'],
                                'Alternative Location', "A", "text/xml", "R", $versionable);
					$link_number+=1;
				}
					

				foreach($oai_ds as $ds) {

					$short_ds = $ds;
					if (is_numeric(strpos($ds, "/"))) {
						$short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
					}
					// ID must start with _ or letter
					$short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 20);
					if (is_numeric(strpos($ds, "/secure/"))) {
						file_put_contents(APP_TEMP_DIR.$short_ds, Misc::getFileURL($ds, EPRINTS_USERNAME, EPRINTS_PASSWD));
					} else {
						file_put_contents(APP_TEMP_DIR.$short_ds, Misc::getFileURL($ds));
					}
						
					$mimetype = Misc::mime_content_type(APP_TEMP_DIR.$short_ds);

					Fedora_API::getUploadLocationByLocalRef($pid, $short_ds, $short_ds, $short_ds, $mimetype, "M", null, $versionable);
						
					//                  $presmd_check = Workflow::checkForPresMD($ds);  // try APP_TEMP_DIR.$short_ds
					$presmd_check = Workflow::checkForPresMD(APP_TEMP_DIR.$short_ds);  // try APP_TEMP_DIR.$short_ds
					if ($presmd_check != false) {
						Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check,
						$presmd_check, "text/xml", "M");
					}
					if (is_file(APP_TEMP_DIR.basename($presmd_check))) {
						$deleteCommand = APP_DELETE_CMD." ".APP_TEMP_DIR.basename($presmd_check);
						exec($deleteCommand);
					}

					Workflow::processIngestTrigger($pid, $short_ds, $mimetype);
					// ID must start with _ or letter
					//                    $short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 20);
					$new_file = APP_TEMP_DIR.$short_ds;
					if (is_file($new_file)) {
						$return_array = array();
						$deleteCommand = APP_DELETE_CMD." ".$new_file;
						exec($deleteCommand, $return_array, $return_status);
						if ($return_status <> 0) {
							Error_Handler::logError("Batch Import Delete Error: $deleteCommand: ".implode(",", $return_array).", return status = $return_status \n", __FILE__,__LINE__);
						}
					}
						

					/*                    if (is_numeric(strpos($ds, "/"))) {
					 $ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself
					 }
					 $ds = str_replace(" ", "_", $ds); */
					//Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the file attachment to the fez index	// this is now done in Record::setIndexMatchingFields more dynamically
					// Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
				}

				// process ingest trigger after all the datastreams are in
				/*                foreach($oai_ds as $ds) {
				 $mimetype = Misc::get_content_type($ds);

				 $short_ds = $ds;
				 if (is_numeric(strpos($ds, "/"))) {
				 $short_ds = substr($ds, strrpos($ds, "/")+1); // take out any nasty slashes from the ds name itself (linux paths)
				 }
				 if (is_numeric(strpos($ds, "\\"))) {
				 $short_ds = substr($ds, strrpos($ds, "\\")+2); // take out any nasty slashes from the ds name itself (windows paths)
				 }
				 Workflow::processIngestTrigger($pid, $short_ds, $mimetype);
				 // ID must start with _ or letter
				 $short_ds = Misc::shortFilename(Foxml::makeNCName($short_ds), 20);
					$new_file = APP_TEMP_DIR.$short_ds;
					if (is_file($new_file)) {
					$return_array = array();
					$deleteCommand = APP_DELETE_CMD." ".$new_file;
					exec($deleteCommand, $return_array, $return_status);
					if ($return_status <> 0) {
					Error_Handler::logError("Batch Import Delete Error: $deleteCommand: ".implode(",", $return_array).", return status = $return_status \n", __FILE__,__LINE__);
					}
					}
					}*/

				$array_ptr = array();
				$xsdmf_array = array();
				Record::setIndexMatchingFields($pid);

				$eprint_record_counter++;
				if ($batch_import_object->bgp) {
					$bgp_details = $batch_import_object->bgp->getDetails();
					//					$utc_date = Date_API::getDateGMT();
					$utc_date = Date_API::getSimpleDateUTC();
					$time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
					$date_new = new Date(strtotime($bgp_details['bgp_started']));
					$time_per_object = round(($time_per_object / $eprint_record_counter), 2);
					$expected_finish = Date_API::getFormattedDate($date_new->getTime());
					$date_new->addSeconds($time_per_object*$record_count);
					$expected_finish = Date_API::getFormattedDate($date_new->getTime());
					$batch_import_object->bgp->setProgress(intval(100*$eprint_record_counter/$record_count));
					$batch_import_object->bgp->setStatus("Just Ingested: ".$record['title']. " (".$eprint_record_counter."/".$record_count.") (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
				}

				$pid = Fedora_API::getNextPID(); // get a new pid for the next loop
			} //end of list record loop

		} // end of for object type loop
		foreach ($relsext as $pid => $re) {
			if ($re['succeeds'] != "") {
				$eprintid = $re["succeeds"];
				$succeedsPID = ePrints::getPIDfromePrintID($eprintid);
				$newXML = $re['xml'] . '<rel:isDerivationOf rdf:resource="info:fedora/'.$succeedsPID.'"/>
			  			 	  </rdf:description>
                              </rdf:RDF>';						  
				Fedora_API::callModifyDatastreamByValue($pid, "RELS-EXT", "A", "", $newXML, "text/xml", "inherit");
				Record::setIndexMatchingFields($pid);
			}
		}
		$batch_import_object->bgp->setStatus("Imported $eprint_record_counter Records");
	} // end of function


} // end of class
