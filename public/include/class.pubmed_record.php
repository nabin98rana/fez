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
// | Authors: Chris Maj <c.maj@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.language.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");
//include_once(APP_INC_PATH . "class.thomson_doctype_mappings.php");

/**
 * Class for working with the ISI WoS REC item object
 *
 * @version 0.1
 * @author Chris Maj <c.maj@library.uq.edu.au>
 *
 */

class PubmedRecItem
{
	private $_loaded;
	
	private $strippedTitle;
	
	private $issue;
	
	private $volume;
	
	private $pmid;
	
	private $doi;
	
	public function __construct($recordData)
	{
		$this->load($recordData);
	}
	
	public function load($recordData)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($recordData);
		
		$pubmedArticle = $dom->getElementById('PubmedArticle');
		$xpath = new DOMXPath($dom);
		
		$title = $xpath->query('MedlineCitation/Article/Journal/Title', $pubmedArticle);
		$this->strippedTitle = RCL::normaliseTitle($title->item(0)->nodeValue);
		
		$issue = $xpath->query('MedlineCitation/Article/Journal/JournalIssue/Issue', $pubmedArticle);
		$this->issue = $issue->item(0)->nodeValue;
		
		$volume = $xpath->query('MedlineCitation/Article/Journal/JournalIssue/Volume', $pubmedArticle);
		$this->volume = $volume->item(0)->nodeValue;
		
		$pmid = $xpath->query('MedlineCitation/PMID', $pubmedArticle);
		$this->pmid = $pmid->item(0)->nodeValue;
		
		$eLocations = $xpath->query('MedlineCitation/Article/ELocationID', $pubmedArticle);
		
		foreach($eLocations as $eLocation)
		{
			$eidType = $eLocation->getAttribute('EIdType');
			$isValidDoi = $eLocation->getAttribute('ValidYN');
			
			if(($eidType == "doi") && ($isValidDoi == "Y"))
			{
				$this->doi = $eLocation->nodeValue;
			}
		}
		
		$this->_loaded = TRUE;
	}
	
	protected function _getSekData()
	{
		//NOTE - Fez_Record_Search_Key instance may need to be passed in like for the Wos record processor
		
		//Build an array of sek data. Ie map all the search key column names as array keys and map them to the 
		//fields in this object and return the array. Seems a bit superlfuous but we'll see.
		
	}
	
	public function save()
	{
		
	}
	
	public function update()
	{
		
	}
	
	
}
