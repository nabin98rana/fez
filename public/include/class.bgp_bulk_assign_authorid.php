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
    protected $_db = null;
    protected $_log = null;

    protected $_authorId = "";
    protected $_authorName = "";
    protected $_numAuthorsUpdated = 0;
    protected $_pidsUpdated = array();
    protected $_pid = null;
    protected $_pids = array();

	function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_bulk_assign_authorid.php';
		$this->name = 'Bulk Assign AuthorID to records';

        if (!$this->_db){
            $this->_db = DB_API::get();
        }
        if (!$this->_log){
            $this->_log = FezLog::get();
        }
	}

	function run()
	{
		$this->setState(1);
		extract(unserialize($this->inputs));

        $this->_pids = $pids;

		if (!empty($this->_pids) && is_array($this->_pids) && !empty($author_name) && !empty($author_id)) {

			$this->setStatus("Trying to update " . count($this->_pids) . " pids ".
                             " with author id " . $author_id . " for authors with '" . $author_name . "'");

            $record_counter = 0;
            $record_count = count($this->_pids);

            // Get the configurations for ETA calculation
            $eta_cfg = $this->getETAConfig();

			/*
			 * For each pid update author id
			 */
			foreach ($this->_pids as $pid) {

                $this->_pid = $pid;

                $record_counter++;

                // Get the ETA calculations
                $eta = $this->getETA($record_counter, $record_count, $eta_cfg);

                $this->setProgress($eta['progress']);
                $this->setStatus(chr(10) . "-- Updating Author ID for '" . $author_name . "' on PID '" . $this->_pid . "'");
                $this->setStatus(chr(10) . "(" . $record_counter . "/" . $record_count . ") ".
                                 chr(10) ."<br />".
                                          "(Avg " . $eta['time_per_object'] . "s per Object. " .
                                            "Expected Finish " . $eta['expected_finish'] . ")"
                                        );


                // For Fedora-bypass version, do not call Fedora_API::callGetDatastreamContents,
                // because it seems like there is a mixed up understanding between "datastreams" on Fedora  and non-Fedora.
                // Fedora datastreams = XML content of a PID, which includes: DOI, FezMD, DC, PremisEvent, MODS, Links, Attached files, etc.
                // Fedora-bypass datastreams = attached files.
                if (APP_FEDORA_BYPASS == 'ON'){

                    $this->_updateWithDatabase($author_id, $author_name);

                // Ok, this is the Fedora version.
                }else {
                    $modsXML = Fedora_API::callGetDatastreamContents($pid, 'MODS', true);

                    $doc = new DOMDocument();
                    $doc->preserveWhiteSpace = false;
                    $ret = $doc->loadXML($modsXML);

                    $xpath = new DOMXPath($doc);
                    $xpath->registerNamespace('mods','http://www.loc.gov/mods/v3');

                    $query = "//mods:name[contains(mods:namePart,'$author_name')]";
                    $nodeList = $xpath->query($query);

                    if($nodeList->length > 0 ) {
                        $this->_numAuthorsUpdated += $nodeList->length;
                        $this->_pidsUpdated[] = $pid;
                        $this->setStatus("Updated " . $nodeList->length ." Author ID(s) for " . $pid . " ");
                        foreach ($nodeList as $item){
                            $item->setAttribute('ID', $author_id);
                            $this->setStatus(" -- Updated Author '" . $item->firstChild->nodeValue . "'");
                        }

                        // Update the XML
                        $newXML = $doc->saveXML();
                        Fedora_API::callModifyDatastreamByValue($pid, "MODS", "A", "Metadata Object Description Schema", $newXML, "text/xml", "inherit");

                        // Update the History
                        $historyDetail = "Updated " . $nodeList->length ." Author ID(s) via bulk AuthorID workflow";
                        History::addHistory($pid, null, date('Y-m-d H:i:s'), "", true, $historyDetail, "");

                        // Set Index
                        Record::setIndexMatchingFields($pid);
                    } else {
                        $this->setStatus("Did NOT update " . $pid . " didn't have any authors with '" . $author_name . "'");
                    }
                }

                $this->markPidAsFinished($pid);
			}

            $this->setProgress(100);
			$this->setStatus("Finished. Updated " . $this->_numAuthorsUpdated . " authors for " . count($this->_pidsUpdated) . " pids");

			if( APP_SOLR_INDEXER == "ON" ) {
				foreach ($this->_pidsUpdated as $pid) {
					$this->setStatus("Adding ".$pid." to Solr reindex queue");
					FulltextQueue::singleton()->add($pid);
				}
				FulltextQueue::singleton()->commit();
				FulltextQueue::singleton()->triggerUpdate();
			}
		}
		$this->setState(2);
	}


    /**
     * Replaces author id record search key of a PID, with the author id specified on $newAuthorId parameter.
     * Author ID only gets updated on the author name matched with the one specified on $authorName parameter.
     *
     * @param int $newAuthorId
     * @param string $authorName
     * @return boolean
     */
    protected function _updateWithDatabase($newAuthorId, $authorName)
    {
        // Get the list of authors of a PID, include rek_author_order
        $authorObj = new Author();
        $authors = $authorObj->getAuthorsByPID($this->_pid);

//        echo chr(10) . "<pre>AUTHORS = " . print_r($authors, 1) . "</pre>";
//        exit;
        // We are not using getSearchKeyIndexValue() because we need all columns from the sek author table
        // $authors = Record::getSearchKeyIndexValue($this->_pid, 'Author');

        if (sizeof($authors) <= 0 ){
            return false;
        }

        // $searchKeyData[0] = 1-to-1 search keys, $searchKeyData[1] = 1-to-many search keys
        $searchKeyData = array(0 => array(), 1 => array());

        $details = Record::getDetailsLite($this->_pid);
        $xsdmfIdForAuthorId = XSD_HTML_Match::getXSDMFIDBySearchKeyTitleXDIS_ID('Author ID', $details[0]['rek_display_type']);

        // Find a match PID's author name(s) with param authorName.
        $authorIDs = array();
        $authorExistsOnPID = false;

        foreach ($authors as $author) {
            if ($author["rek_author"] == $authorName) {
                $authorExistsOnPID = true;
                $this->_numAuthorsUpdated++;
                $authorIDs[] = $newAuthorId;
            } else {
                $authorIDs[] = $author["rek_author_id"];
            }
        }

        // We don't need to update this PID since there is not matching Author
        if ($authorExistsOnPID !== true){
            $this->setStatus("Did NOT update " . $this->_pid . " didn't have any authors with '" . $authorName . "'");
            return false;
        }


        // @todo: do we still need to pass in update_date?
        $searchKeyData[0]['updated_date']['xsdmf_id'] = $details[0]['rek_updated_date_xsdmf_id'];
        $searchKeyData[0]['updated_date']['xsdmf_value'] = Date_API::getFedoraFormattedDateUTC();

        // Set record search key values
        $searchKeyData[1]['author_id']['xsdmf_id'] = $xsdmfIdForAuthorId;
        $searchKeyData[1]['author_id']['xsdmf_value'] = $authorIDs;

        // Update record search key
        $recordSearchKey = new Fez_Record_Searchkey();
        if (!$recordSearchKey->updateRecord($this->_pid, $searchKeyData)){
            return false;
        }

        $this->setStatus(" Updated Author ID for '" . $authorName . "' on PID '" . $this->_pid . "'");

        // Update PID's history
        $historyDetail = " Updated " . $authorName ." Author ID via ". $this->name ." workflow.";
        History::addHistory($this->_pid, null, date('Y-m-d H:i:s'), "", true, $historyDetail, "");

        // Add PID on updated list
        $this->_pidsUpdated[] = $this->_pid;

        return true;
    }
}
