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

/**
 * Citation
 * Class to format citations for displaying to the user.  The citations are stored as templates where
 * values from the records are substituted into placeholders.  The citation templates are mapped according
 * to the XSD Display for the record so there is a template for each Display mapping that uses the xsdmf_ids
 * as the placeholders.  There is also support for different types of citations - APA, MLA, Chicago etc... but
 * only APA is implemented for now.
 *
 * This system of citations replaces a system using the xsdmf_citation_* columns in the fez_xsd_html_matchfiels table.
 *
 * The templates use place holders of the form {<xsdmf_id>|<prefix>|<suffix>|<option>} where the <xsdmf_id> is the
 * xsdmf_id to be mapped from the record to this part of the template.  If the values for this xsdmf_id is set,
 * then the prefix and suffix are also put before and after the value in the output.  Also there are some options that
 * can control how the value is formatted which may depend on the type of citation and even vary between content models
 * in the same citation type (e.g. in APA dates are formatted differently between newspapers which have the year
 * month and day or books which just have the year).
 */
class Citation
{
	/**
	 * getDetails - Retrieves a citation template for the given xdis_id and type from the citations table in the
	 * database.
	 * @param integer $xdis_id - The display id that the citation is for.
	 * @param string $type - Optional citation type (default is 'APA').
	 * @return array - Citation table columns for the row that matches the xdis_id and type
	 *                 or an empty array for no match.
	 */
	function getDetails($xdis_id, $type='APA')
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT * FROM ".$dbtp."citation WHERE cit_xdis_id=".$db->quote($xdis_id, 'INTEGER')." AND cit_type=".$db->quote($type);
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}
	 
	/**
	 * getDetailsAllTypes - retrieves the citatiosn table rows for all types of citations on a given display
	 * @param integer $xdis_id - The display id that the citations are for.
	 * @return array - Citation table columns for the rows that match the xdis_id or an empty array for no match.
	 */
	function getDetailsAllTypes($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT * FROM ".$dbtp."citation WHERE cit_xdis_id=".$db->quote($xdis_id, 'INTEGER');
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	/**
	 * save - Write a citation template to the citations database table.  Either inserts a new one or updates
	 * an existing one.  This can be determined as there is only one template allowed for a given xdis_id and type.
	 * @param integer $xdis_id - The display id that the citation is for.
	 * @param string $type - Optional citation type (default is 'APA').
	 * @return boolean - True if the query succeeded, false if there was an error (the Error_Handler is also called
	 *                   when there is an error)
	 */
	function save($xdis_id, $template, $type='APA')
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$det = Citation::getDetails($xdis_id, $type);

		if (empty($det)) {
			$stmt = "INSERT INTO ".$dbtp."citation (cit_xdis_id, cit_template, cit_type) " .
                    "VALUES (".$db->quote($xdis_id, 'INTEGER').",".$db->quote($template).",".$db->quote($type).")";
		} else {
			$stmt = "UPDATE ".$dbtp."citation SET " .
                    "cit_xdis_id=".$db->quote($xdis_id, 'INTEGER')."," .
                    "cit_template=".$db->quote($template)."," .
                    "cit_type=".$db->quote($type)." " .
                    "WHERE cit_id=".$db->quote($det['cit_id'], 'INTEGER');
		}
		Citation::clearCitationCacheByType($xdis_id);
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}

	/**
	 * deleteAllTypes - removed citation templates from the citations database table for a given XSD Display.
	 * @param integer $xdis_id - The display id that the citations are for.
	 * @return boolean - True if the query succeeded, false if there was an error (the Error_Handler is also called
	 *                   when there is an error)
	 */
	function deleteAllTypes($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "DELETE FROM ".$dbtp."citation WHERE cit_xdis_id=".$db->quote($xdis_id, 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}


	function clearAllCitationCache()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."record_search_key set rek_citation = ''";
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}

	function clearCitationCacheByType($xdis_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($xdis_id)) {
			return false;
		}
		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."record_search_key set rek_citation = '' WHERE rek_display_type=".$db->quote($xdis_id, 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		if (( APP_SOLR_INDEXER == "ON" ) || (APP_FILECACHE == "ON")) {
			$stmt = "SELECT rek_pid FROM ".$dbtp."record_search_key WHERE rek_display_type=".$db->quote($xdis_id, 'INTEGER');
			try {
				$res = $db->fetchCol($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
			foreach ($res as $pid) {
				if (APP_FILECACHE == "ON") {
					$log->debug("Citation::clearCitationCacheByType Poisoning fileCache for ".$pid."");
					$cache = new fileCache($pid, 'pid='.$pid);
					$cache->poisonCache();
				}
				if ( APP_SOLR_INDEXER == "ON" ) {
					$log->debug("Citation::clearCitationCacheByType ADDING ".$pid." TO QUEUE");
					FulltextQueue::singleton()->add($pid);
				}
			}
			if ( APP_SOLR_INDEXER == "ON" ) {
				FulltextQueue::singleton()->commit();
				FulltextQueue::singleton()->triggerUpdate();
			}
		} 
		return true;
	}

	/**
	 * renderCitation - produce a HTML rendering of a citation given the XSD Display Id, xsdmf and record data.
	 * @param integer $xdis_id - The display id that the citation is for.
	 * @param array $details - Associative array of record values where the keys are xsdmf_ids
	 * @param array $xsd_display_fields - Match fields for the XSD Display.  Provides extra information about how
	 *                                    the record fields should be formatted.
	 * @param string $type - Optional citation type (default is 'APA').
	 */
	function renderCitation($xdis_id, $details, $xsd_display_fields, $type='APA')
	{
		$dbtp =  APP_TABLE_PREFIX;
		$det = Citation::getDetails($xdis_id, $type);
		$result = $det['cit_template'];
		if (empty($result)) {
			return '';
		}
		return Citation::renderCitationTemplate($result, $details, $xsd_display_fields, $type);
	}


	function renderIndexCitations($list, $type='APA', $cache = true, $knownFull = false)
	{
		
		$log = FezLog::get();
		foreach ($list as $row => $value) {
			if ($list[$row]['rek_citation'] == "" || $cache == false) {
				$xdis_id = $value['rek_display_type'];
				$det = Citation::getDetails($xdis_id, $type);
				$result = $det['cit_template'];
				if (empty($result)) {
					$citation = Record::getTitleFromIndex($list[$row]['rek_pid']);
					$log->debug("No Style, so setting Citation to just title of ".$citation." for PID ".$pid);
					$citation = Citation::formatTitle($citation, $list[$row]);
					Citation::updateCitationCache($list[$row]['rek_pid'], $citation);				
					continue;
				}
				if ($knownFull == false) {
					//get a full index load of data for this row
					$options = array();
					$options["searchKey".Search_Key::getID("Pid")] = $list[$row]['rek_pid']; // enforce records only
					$list_full = Record::getListing($options, array("Lister"), 0, 1, "Title", false, false);
					$list[$row] = $list_full["list"][0];
				}
				$citation = Citation::renderIndexCitationTemplate($result, $list[$row], $type);
				if ($citation != "") {
					$log->debug("Setting Citation to full citation of ".$citation." for PID ".$pid);
					Citation::updateCitationCache($list[$row]['rek_pid'], $citation);
				} else { // if no citation template has been applied to this display type then just put in the title so it at least doesnt keep trying to make it
					$citation = Record::getTitleFromIndex($list[$row]['rek_pid']);
					$log->debug("Setting Citation to just title of ".$citation." for PID ".$pid);
					Citation::updateCitationCache($list[$row]['rek_pid'], $citation);
				}
				$list[$row]['rek_citation'] = $citation;
			}
		}
		return $list;
	}

	function updateCitationCache($pid, $citation="")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		if ($citation == "") {
			$options = array();
			$options["searchKey".Search_Key::getID("Pid")] = $pid; // enforce records only
			$list = Record::getListingForCitation($options, array("Lister"));
			$list = $list["list"];
			$list = Citation::renderIndexCitations($list, 'APA', false, true);
			if (count($list) != 1) {
				return;
			}
			$citation = $list[0]['rek_citation'];
		}
		if ($citation == "") {
			return;
		}

		$stmt = "UPDATE
				" . APP_TABLE_PREFIX . "record_search_key r1
				SET rek_citation = ".$db->quote($citation)."
				WHERE rek_pid = ".$db->quote($pid);
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1; // abort
		}
		return $citation;
	}

	/**
	 * Create a citation string based on a template
	 *
	 * @param string $template the citation template
	 * @param array  $details  an array of details about a pid
	 * @param string $type
	 *
	 * @return string  the citation string of a pid
	 *
	 * @access public
	 */
	function renderIndexCitationTemplate($template, $details, $type='APA')
	{
		/*
		 * Extract the xsdmf_id's into an array
		 */
		preg_match_all('/\{(.*?)\}/',$template,$matches,PREG_PATTERN_ORDER);

		/*
		 * Loop through xsdmf_id's
		 */
		foreach ($matches[1] as $key => $match) {
			list($xsdmf_id,$prefix,$suffix,$option) = explode('|',$match);

			if (is_numeric($xsdmf_id)) {
				$xsdmf_details = Search_Key::getAllDetailsByXSDMF_ID($xsdmf_id);

				$value = "";
				$sek_title = "rek_".$xsdmf_details['sek_title_db'];
				if (is_array($details)) {

					if (array_key_exists($sek_title, $details)) {
						$value = $details[$sek_title];

						if (!empty($value) && !is_null($value) && $value != "") {
							$value = Citation::formatValue($details[$sek_title], '', $details, $xsdmf_details, $option, $type);
						}
					} else {
						$value = "";
					}
						
				}

				if (!empty($value) && !is_null($value) && $value != "") {
					$value = $prefix.$value.$suffix;
				}
				//Error_Handler::logError($match);
				$template = str_replace('{'.$match.'}', $value, $template);
			}
		}
		return $template;
	}

	/**
	 * renderCitation - produce a HTML rendering of a citation given the template, xsdmf and record data.
	 * This can be used to render a citation that hasn't been saved in the database yet - e.g. previewing
	 * @param string $template - The template to be rendered.
	 * @param array $details - Associative array of record values where the keys are xsdmf_ids
	 * @param array $xsd_display_fields - Match fields for the XSD Display.  Provides extra information about how
	 *                                    the record fields should be formatted.
	 * @param string $type - Optional citation type (default is 'APA').
	 */
	function renderCitationTemplate($template, $details, $xsd_display_fields, $type='APA')
	{
		preg_match_all('/\{(.*?)\}/',$template,$matches,PREG_PATTERN_ORDER);
		$xsdmf_list = Misc::keyArray($xsd_display_fields, 'xsdmf_id');
		foreach ($matches[1] as $key => $match) {
			list($xsdmf_id,$prefix,$suffix,$option) = explode('|',$match);
			if (is_numeric($xsdmf_id)) {
				$value = Citation::formatValue($details[$xsdmf_id], '', array(), $xsdmf_list[$xsdmf_id], $option, $type);
				if (!empty($value) && !is_null($value)) {
					$value = $prefix.$value.$suffix;
				}
				//Error_Handler::logError($match);
				$template = str_replace('{'.$match.'}', $value, $template);
			}
		}
		return $template;
	}
	 
	/**
	 * formatValue - format a single value in the template.  e.g. if the value is a timestamp, it can be shown as
	 * a formatted date according to the citation type.
	 * @param mixed $value - the value from the record.
	 * @param mixed $yy - the current loop id (to match with parallel data like author -> author ids).
	 * @param array $details - row data for this object
	 * @param array $xsdmf - matchfields table columns for this value
	 * @param string $option - optional template item parameter
	 * @param string $type - Optional citation type (default is 'APA').
	 */
	function formatValue($value, $yy='', $details, $xsdmf, $option = '', $type='APA')
	{
		if (is_array($value)) {
			// If the item is an array, then we'll format a list of values and
			// recurse for each item of the list to format each of them too.
			$list = '';
			$cnt = count($value);
			for ($ii = 0; $ii < $cnt; $ii++) {
				if ($ii > 0) {
					if ($ii >= $cnt - 1) {
						$list .= ' and ';
					} else {
						$list .= ', ';
					}
				}
				// recurse for each item of list.
				$list .= Citation::formatValue($value[$ii], $ii, $details, $xsdmf, $option, $type);
			}
			$value = $list;
		} elseif ($xsdmf['sek_data_type'] == 'date' || $xsdmf['xsdmf_html_input'] == 'date_selector') {
			if (!empty($value) && !is_null($value) && $value != "") {
				switch($option) {
					case 'ymd':
						$value = strftime("%Y, %B %d", strtotime($value));
						break;
					case 'ym':
						$value = strftime("%Y, %B", strtotime($value));
						break;
					case 'my':
						$value = strftime("%B %Y", strtotime($value));
						break;
					default:
						$value = substr(trim($value), 0, 4);
						break;
				}
			}
			// hacky formatting of authors names.  Pretty easy to break - like
			// if the field doesn't use the selector or the sek_title or xsdmf_title is in a different language. WILL PROBABLY NEVER BE USED!
		} elseif ($xsdmf['xsdmf_html_input'] == 'author_selector') {
			$value = Citation::formatAuthor(Author::getFullname($value), $type);
			// special case hack for editors name fix
		} elseif ($xsdmf['sek_title'] == "Author" || strpos($xsdmf['xsdmf_title'], 'Editor') !== false) {
			$value = Citation::formatAuthor($value, $type);
		}

		if (count($details) > 0) {
			if (is_numeric($yy)) {
				if ($xsdmf['sek_title'] == "Author") {
					if (is_array($details['rek_author_id']) && $details['rek_author_id'][$yy] != 0) {
						$value = '<a class="author_id_link" title="Browse by Author ID for '.htmlentities($details['rek_author_id_lookup'][$yy], ENT_COMPAT, 'UTF-8').'" href="' . APP_RELATIVE_URL . 'list/author_id/'.$details['rek_author_id'][$yy].'/">'.$value.'</a>';
					} else {
						$value = '<a title="Browse by Author Name for '.$details['rek_author'][$yy].'" href="' . APP_RELATIVE_URL . 'list/author/'.urlencode(htmlentities($details['rek_author'][$yy])).'/">'.$value.'</a>';
					}
				}
			} else {
				if ($xsdmf['sek_title'] == "Title") {
					$value = Citation::formatTitle($value, $details);
				}
				if ($xsdmf['sek_title'] == "Date") {
					$value = '<a title="Browse by Year '.htmlentities($value, ENT_COMPAT, 'UTF-8').'" href="' . APP_RELATIVE_URL . 'list/year/'.htmlentities($value, ENT_COMPAT, 'UTF-8').'/">'.$value.'</a>';
				}
			}
		}

		return $value;
	}

	/**
	 * formatAuthor - Check an author's name for commas and try to get it into the right combination of
	 * lst name and initials for the given citation type.
	 * @param string $value - the value from the record.
	 * @param string $type - Optional citation type (default is 'APA').
	 */
	function formatAuthor($value, $type='APA')
	{
		if (empty($value)) {
			return '';
		}
		return $value;
	}

	function formatTitle($value, $details)
	{
		if (empty($value)) {
			return '';
		}
		if ($details['rek_object_type'] == 3) {
			$value = '<a title="Click to view '.$details['rek_display_type_lookup'].': '.htmlentities($value, ENT_COMPAT, 'UTF-8').'" href="' . APP_RELATIVE_URL . 'view/'.$details['rek_pid'].'">'.$value.'</a>';
		} elseif ($details['rek_object_type'] == 2) {
			$value = '<a title="Click to list records in '.$details['rek_display_type_lookup'].' '.htmlentities($value, ENT_COMPAT, 'UTF-8').'" href="' . APP_RELATIVE_URL . 'collection/'.$details['rek_pid'].'">'.$value.'</a>';
		} elseif ($details['rek_object_type'] == 1) {
			$value = '<a title="Click to list collections in '.$details['rek_display_type_lookup'].' '.htmlentities($value, ENT_COMPAT, 'UTF-8').'" href="' . APP_RELATIVE_URL . 'community/'.$details['rek_pid'].'">'.$value.'</a>';
		}
		return $value;
	}


	/**
	 * convert - retrieve old style of doing citations and write a citation template equivalent.
	 * This assumes that the existing citations are the APA style.  This has been written specifically for
	 * UQ eSpace which had existing citation templates using xsdmf_citation* columns which this templating
	 * system replaces.
	 * @param integer $xdis_id - The display id that the citations are for.
	 */
	function convert($xdis_id)
	{
		$xsd_display_fields = XSD_HTML_Match::getListByDisplay($xdis_id, array('FezACML'));
		$citation = array();
		// Now generate the Citation View
		// First get the citation fields in the correct order
		foreach ($xsd_display_fields as $dis_key => $dis_field) {
			if (($dis_field['xsdmf_enabled'] == 1) && ($dis_field['xsdmf_citation'] == 1) && (is_numeric($dis_field['xsdmf_citation_order']))) {
				$citation[$dis_field['xsdmf_citation_order']] = $dis_field;
			}
		}
		ksort($citation);
		$citation_html = "";
		foreach($citation as $cit_key => $cit_field) {
			if ($cit_field['xsdmf_citation_bold'] == 1) {
				$citation_html .= "<b>";
			}
			if ($cit_field['xsdmf_citation_italics'] == 1) {
				$citation_html .= "<i>";
			}
			if ($cit_field['xsdmf_citation_brackets'] == 1) {
				$citation_html .= " (";
			}
			$citation_html .= '{'.$cit_field['xsdmf_id'];
			if (trim($cit_field['xsdmf_citation_prefix']) != "") {
				$citation_html .= '|'.$cit_field['xsdmf_citation_prefix'];
			}
			if (trim($cit_field['xsdmf_citation_suffix']) != "") {
				if (trim($cit_field['xsdmf_citation_prefix']) == "") {
					$citation_html .= '|';
				}
				$citation_html .= '|'.$cit_field['xsdmf_citation_suffix'];
			}
			$citation_html .= '} ';
			if ($cit_field['xsdmf_citation_bold'] == 1) {
				$citation_html .= "</b>";
			}
			if ($cit_field['xsdmf_citation_italics'] == 1) {
				$citation_html .= "</i>";
			}
			if ($cit_field['xsdmf_citation_brackets'] == 1) {
				$citation_html .= ")";
			}
		}
		$citation_html = str_replace(' ,', ', ', $citation_html);
		$citation_html = str_replace(' .', '. ', $citation_html);
		$citation_html = preg_replace('/(,|\.),\S/', ', ', $citation_html);
		return Citation::save($xdis_id, trim($citation_html), 'APA');
	}

	/**
	 * export - Create an XML export of the citation template for a XSD Display.
	 * @param object $xnode - DOM object to attach the XML representation of the citation template to.  The function
	 *                        modifies this object to add XML child nodes.
	 * @param integer $xdis_id - The display id that the citations are for.
	 * @return null
	 */
	function export(&$xnode, $xdis_id)
	{
		$list = Citation::getDetailsAllTypes($xdis_id);
		if (!empty($list)) {
			$xcits = $xnode->ownerDocument->createElement('citations');
			foreach ($list as $det) {
				$xcit = $xnode->ownerDocument->createElement('citation');
				$keys = array_diff(array_keys($det), array('cit_template'));
				foreach ($keys as $key) {
					$xcit->setAttribute($key, $det[$key]);
				}
				$cdata = $xnode->ownerDocument->createCDATASection($det['cit_template']);
				$xcit->appendChild($cdata);
				$xcits->appendChild($xcit);
			}
			$xnode->appendChild($xcits);
		}
	}

	/**
	 * import - Traverse a XML DOM representation of a citation template and save it to the citations database table.
	 * @param object $xdis - DOM object to extract the citation templates from.
	 * @param integer $xdis_id - The XSD Display to save the citation for.  Note that the value in the DOM cit_xdis_id
	 *                           attribute might be wrong as the display might be at a different db id than from what
	 *                           was exported, so be sure to use the xdis_id passed in the function params.
	 * @return null
	 */
	function import($xdis, $xdis_id, &$maps)
	{
		$xpath = new DOMXPath($xdis->ownerDocument);
		$xcits = $xpath->query('citations/citation', $xdis);
		foreach ($xcits as $xcit) {
			$template = $xcit->nodeValue;
			$cit_type = $xcit->getAttribute('cit_type');
			Citation::save($xdis_id, $template, $cit_type);
			$maps['citations'][$xdis_id][$cit_type] = 1;
		}
	}

	function remapImport(&$maps)
	{
		foreach ($maps['citations'] as $xdis_id => $type_items) {
			foreach ($type_items as $cit_type => $dummy) {
				// getTemplate
				$cit_details = Citation::getDetails($xdis_id, $cit_type);
				$template = $cit_details['cit_template'];
				if (preg_match_all('/\{(\d+)\D/', $template, $matches)) {
					foreach ($matches[1] as $xsdmf_id) {
						if ($xsdmf_id != $maps['xsdmf_map'][$xsdmf_id]) {
							$template = preg_replace('/\{'.$xsdmf_id.'(\D)/',
    						    				'{'.$maps['xsdmf_map'][$xsdmf_id].'$1', $template);
						}
					}
				}
				Citation::save($xdis_id, $template, $cit_type);
			}
		}
	}
}
