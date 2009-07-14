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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.bulk_assign_record_group.php');

class BackgroundProcess_Bulk_Assign_Authorid extends BackgroundProcess
{
	function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_bulk_assign_authorid.php';
		$this->name = 'Bulk Assign AuthorID to records';
	}

	function run()
	{
		$this->setState(1);
		extract(unserialize($this->inputs));

		$updatedPids = array();
		$numAuthorsUpdated = 0;

		if (!empty($pids) && is_array($pids) && !empty($author_name) && !empty($author_id)) {

			$this->setStatus("Trying to update " . count($pids) . " pids with author id " . $author_id . " for authors with '" . $author_name . "'");
			 
			/*
			 * For each pid update author id
			 */
			foreach ($pids as $pid) {
				$modsXML = Fedora_API::callGetDatastreamContents($pid, 'MODS', true);
				 
				$doc = new DOMDocument();
				$doc->preserveWhiteSpace = false;
				$ret = $doc->loadXML($modsXML);
				 
				$xpath = new DOMXPath($doc);
				$xpath->registerNamespace('mods','http://www.loc.gov/mods/v3');
				 
				$query = "//mods:name[contains(mods:namePart,'$author_name')]";
				$nodeList = $xpath->query($query);
				 
				if($nodeList->length > 0 ) {
					$numAuthorsUpdated += $nodeList->length;
					$updatedPids[] = $pid;
					$this->setStatus("Updated " . $nodeList->length ." Author ID(s) for " . $pid . " ");
					foreach ($nodeList as $item){
						$item->setAttribute('ID', $author_id);
						$this->setStatus(" -- Updated Author '" . $item->firstChild->nodeValue . "'");
					}
					$newXML = $doc->saveXML();
					Fedora_API::callModifyDatastreamByValue($pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit");
					$historyDetail = "Updated " . $nodeList->length ." Author ID(s) via bulk AuthorID workflow";
					History::addHistory($pid, null, date('Y-m-d H:i:s'), "", true, $historyDetail, "");
					Record::setIndexMatchingFields($pid);
				} else {
					$this->setStatus("Did NOT update " . $pid . " didn't have any authors with '" . $author_name . "'");
				}
				 
			}
			$this->setStatus("Finished. Updated " . $numAuthorsUpdated . " authors for " . count($updatedPids) . " pids");

			if( APP_SOLR_INDEXER == "ON" ) {
				foreach ($updatedPids as $pid) {
					$this->setStatus("Adding ".$pid." to Solr reindex queue");
					FulltextQueue::singleton()->add($pid);
				}
			}
		}
		$this->setState(2);
	}
}
