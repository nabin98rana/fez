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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class designed to handle all business logic related to the Records in the
 * system, such as adding or updating them or listing them in the grid mode.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.conference.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.object_type.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.history.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.auth_rules.php");
include_once(APP_INC_PATH . "class.google_scholar.php");
include_once(APP_INC_PATH . "class.auth_index.php");
include_once(APP_INC_PATH . "class.xml_helper.php");
include_once(APP_INC_PATH . "class.record_lock.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.exiftool.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.filecache.php");
include_once(APP_INC_PATH . "class.handle_requestor.php");
include_once(APP_INC_PATH . "class.record_object.php");
include_once(APP_INC_PATH . "class.record_general.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.links_amr_queue.php");
include_once(APP_INC_PATH . "class.internal_notes.php");
include_once(APP_INC_PATH . "ForceUTF8/Encoding.php");
include_once(APP_INC_PATH . "class.ulrichs.php");


define('SK_JOIN', 0);
define('SK_LEFT_JOIN', 1);
define('SK_WHERE', 2);
define('SK_SORT_ORDER', 3);
define('SK_KEY_ID', 4);
define('SK_MAX_COUNT', 5);
define('SK_FULLTEXT_REL', 6);
define('SK_SEARCH_TXT', 7);
define('SK_GROUP_BY', 8);
define('SK_ORDER_BY', 9);

/**
 * Record
 * Static class for accessing record related queries
 * See RecordObject for an object oriented representation of a record.
 */
class Record
{
  const status_undefined = 0;
  const status_unpublished = 1;
  const status_published = 2;


   // Used to ajax lookup
  function suggest($terms, $current_row = 0, $max = 10)
  {
      $options = array();
      $filter = array();
      if (APP_CUSTOM_VIEW_PID != "") {
        // enforce custom view collections only
        $options["searchKey".Search_Key::getID("isMemberOf")] = APP_CUSTOM_VIEW_PID;
      }
      $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
      $options["searchKey".Search_Key::getID("Object Type")] = 3; // records only

      $filter["manualFilter"] = ' ( (title_t:("'.$terms.'") OR pid_t:("'.$terms.'")) AND !ismemberof_mt:"'.APP_TEMPORARY_DUPLICATES_COLLECTION.'" ) ';

      $list = Record::getListing($options, array("Lister"), $current_row, $max, "Title", true, false, $filter);

      $list = $list['list'];

      $returnList = array();

      foreach ($list as $key => $element) {
        $returnList[$key] = array();
        $returnList[$key]['id'] = $element['rek_pid'];
        $returnList[$key]['pid'] = $element['rek_pid'];
        $returnList[$key]['name'] = $element['rek_title'];
      }
      return $returnList;


  }


  /**
   * Method used to get the parents of a given record available in the
   * system.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @return  array The list
   */
  function getParents($pid, $searchKey='isMemberOf')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sek_title = Search_Key::makeSQLTableName($searchKey);
    $stmt = "SELECT ".APP_SQL_CACHE."
          m1.rek_".$sek_title."
         FROM
          " . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 INNER JOIN
          " . APP_TABLE_PREFIX . "record_search_key m2 ON m1.rek_".$sek_title." = m2.rek_pid
         WHERE m1.rek_".$sek_title."_pid = ".$db->quote($pid);
    try {
      $res = $db->fetchCol($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  function getTitleFromIndex($pid)
  {
    $title = Record::getSearchKeyIndexValue($pid, "title", false);
    return $title;
  }

  function getIsiLocFromIndex($pid)
  {
    return Record::getSearchKeyIndexValue($pid, "ISI LOC", false);
  }


  /**
   * Method used to get the parents of a given record available in the
   * system.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @return  array The list
   */
  function getParentsDetails($pid, $clearcache=false, $searchKey='isMemberOf')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sek_title = Search_Key::makeSQLTableName($searchKey);
    $stmt = "SELECT ".APP_SQL_CACHE."
          r1.*
         FROM
          " . APP_TABLE_PREFIX . "record_search_key r1 inner join
          " . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".
          $sek_title." AND m1.rek_".$sek_title."_pid = ".$db->quote($pid);

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  /**
   * Method used to get the parents of a given record available in the
   * system.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @return  array The list
   */
  function getChildrensDetails($pid, $clearcache=false, $searchKey='isMemberOf')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sek_title = Search_Key::makeSQLTableName($searchKey);
    $stmt = "SELECT ".APP_SQL_CACHE."
          r1.*
         FROM
          " . APP_TABLE_PREFIX . "record_search_key r1 inner join
          " . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".
          $sek_title."_pid AND m1.rek_".$sek_title." = ".$db->quote($pid);

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  function getCollectionChildrensDetails($pid, $clearcache=false, $searchKey='isMemberOf')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sek_title = Search_Key::makeSQLTableName($searchKey);

    $stmt = "SELECT ".APP_SQL_CACHE."
          r1.*
         FROM
          " . APP_TABLE_PREFIX . "record_search_key r1 inner join
          " . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".
         $sek_title."_pid AND m1.rek_".$sek_title." = ".$db->quote($pid)."
         WHERE r1.rek_object_type = 2";

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }


  function generateDerivationTree($pid, $derivations, &$dTree, &$shownPids=array(), $hideSelf = false)
  {
    if (!array($derivations)) {
      return;
    }
    foreach ($derivations as $devkey => $dev) { // now build HTML of the citation
      if (!in_array($dev['rek_pid'], $shownPids)) {
        if ($dev['rek_pid'] != $pid) {
          $xdis_title = XSD_Display::getTitle($dev['rek_display_type']);
          $dTree .= '<li>';
          $dTree .= '<a href="' . APP_RELATIVE_URL . 'view/' . $dev['rek_pid'] . '">' .
                    $dev['rek_title'] . '</a> <i>' . $xdis_title . '</i> (deposited ' .
                    Date_API::getFormattedSimpleDate($dev['rek_created_date']) . ')';
          $dTree .= '</li>';
        } elseif ($hideSelf !== true) {
          $dTree .= '<li>' . $dev['rek_title'] . ' <b>(Current Record)</b></li>';
        }
        array_push($shownPids, $dev['rek_pid']);
        if (is_array($dev['children'])) {
          Record::generateDerivationTree($pid, $dev['children'], $dTree, $shownPids, $hideSelf);
        }
      }
    }
  }


  function wrapDerivationTree(&$dTree)
  {
    $dTree = "<ul>" . $dTree . "</ul>";
  }


  /**
   * Method used to get all of the parents of a given record available in the
   * system.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @param   string $searchKey The search key - defaults to isMemberOf, but can be isDerivationOf
   *                            or any other similar setup RELS-EXT element
   * @return  array The list
   */
  function getParentsAll($pid, $searchKey="isMemberOf", $flatTree=true)
  {
    static $returns;

    if (isset($returns[$pid][$searchKey])) {
      return $returns[$pid][$searchKey];
    }

    $res = Record::getParentsDetails($pid, false, $searchKey);
    $recursive_details = array();
    $details = $res;
    foreach ($details as $key => $row) {
      $temp = Record::getParentsDetails($row['rek_pid'], false, $searchKey, $flatTree);
      foreach ($temp as $trow) {
        array_push($recursive_details, $trow);
      }
    }
    foreach ($recursive_details as $rrow) {
      if ($flatTree == true) {
        array_push($details, $rrow);
      } else {
        if (!is_array($recursive_details['children'])) {
          $details['parents'] = array();
        }
        array_push($details['parents'], $rrow);
      }
    }
    $details = array_reverse($details);
    if ($GLOBALS['app_cache']) {
      // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
      if (!is_array($returns) || count($returns) > 10) {
        $returns = array();
      }
      $returns[$pid][$searchKey] = $details;
    }
    return $details;
  }

  /**
   * Method used to get all of the children of a given record available in the
   * system.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @param   string $searchKey The search key - defaults to isMemberOf, but can be isDerivationOf
   *                            or any other similar setup RELS-EXT element
   * @return  array The list
   */
  function getChildrenAll($pid, $searchKey="isMemberOf", $flatTree=true)
  {

    static $returns;

    if (isset($returns[$pid][$searchKey])) {
      return $returns[$pid][$searchKey];
    }
    $dbtp =  APP_TABLE_PREFIX;

    $details = Record::getChildrensDetails($pid, false, $searchKey);
    $recursive_details = array();
    foreach ($details as $key => $row) {
      $temp = Record::getChildrensDetails($row['rek_pid'], false, $searchKey);
      foreach ($temp as $trow) {
        if ($flatTree == true) {
          array_push($details, $trow);
        } else {
          if (!is_array($details[$key]['children'])) {
            $details[$key]['children'] = array();
          }
          array_push($details[$key]['children'], $trow);
        }

      }
    }
    $details = array_reverse($details);
    if ($GLOBALS['app_cache']) {
      // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
      if (!is_array($returns) || count($returns) > 10) {
        $returns = array();
      }
      $returns[$pid][$searchKey] = $details;
    }
    return $details;
  }


  function getCollectionChildrenAll($pid, $searchKey="isMemberOf", $flatTree=true)
  {

    static $returns;

    if (isset($returns[$pid][$searchKey])) {
      return $returns[$pid][$searchKey];
    }
    $dbtp =  APP_TABLE_PREFIX;

    $details = Record::getCollectionChildrensDetails($pid);
    $recursive_details = array();
    foreach ($details as $key => $row) {
      $temp = Record::getCollectionChildrensDetails($row['rek_pid'], false, $searchKey);
      foreach ($temp as $trow) {
        if ($flatTree == true) {
          array_push($details, $trow);
        } else {
          if (!is_array($details[$key]['children'])) {
            $details[$key]['children'] = array();
          }
          array_push($details[$key]['children'], $trow);
        }

      }
    }
    $details = array_reverse($details);
    if ($GLOBALS['app_cache']) {
      // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
      if (!is_array($returns) || count($returns) > 10) {
        $returns = array();
      }
      $returns[$pid][$searchKey] = $details;
    }
    return $details;
  }

   /**
	* Method used to return the core metadata for a record that affects the Q-index calculation.
	*
	* @access  public
	* @param   string $pid The persistent identifier of the record
	* @return  array A series of strings, representing the data for each of the key fields
	*/
	function getQindexMeta($pid)
	{

	$rj = Record::getRankedJournalInfo($pid);
	$rc = Record::getRankedConferenceInfo($pid);
	$hc = Record::getHERDCcode($pid);

	return array(	'rj' => $rj,
					'rc' => $rc,
					'hc' => $hc	);
	}

  function getResearchDetailsbyPIDS($result, $getAuthorMatching = false, $versionDate=null)
  {

    $pids = array();
    for ($i = 0; $i < count($result); $i++) {
      $pids[] = $result[$i]["rek_pid"];
    }
    if (count($pids) == 0) {
      return;
    }

		$herdc = Record::getHERDCcodeByPIDs($pids);

		$rj = Record::getRankedJournalInfoByPIDs($pids);

		$rc = Record::getRankedConferenceInfoByPIDs($pids);

		$ht = array();
		$rjt = array();
		$rct = array();
		$res = $herdc;
		if (is_array($res)) {
		  for ($i = 0; $i < count($res); $i++) {
	      $ht[$res[$i]["pid"]]['herdc_code_description'] = $res[$i]["herdc_code_description"];
	    }
		}
		$res = $rj;
		if (is_array($res)) {
		  for ($i = 0; $i < count($res); $i++) {
              $rjt[$res[$i]["pid"]]['rj_'.$res[$i]["year"].'_rank'] = $res[$i]["rank"];
              $rjt[$res[$i]["pid"]]['rj_'.$res[$i]["year"].'_title'] = $res[$i]["title"];
	      }
		}
		$res = $rc;
		if (is_array($res)) {
          for ($i = 0; $i < count($res); $i++) {
              $rct[$res[$i]["pid"]]['rc_'.$res[$i]["year"].'_rank'] = $res[$i]["rank"];
              $rct[$res[$i]["pid"]]['rc_'.$res[$i]["year"].'_title'] = $res[$i]["title"];
          }
		}
    // now populate the $result variable again
    // for ($i = 0; $i < count($result); $i++) {
    //   $result[$i]["rek_ismemberof_count"] = $t[$result[$i]["rek_pid"]];
    // }
  	if ($getAuthorMatching == true) {
      $username = Auth::getActingUsername();
      $aut_id = Author::getIDByUsername($username);
    } else {
      $aut_id = false;
    }
    for ($i = 0; $i < count($result); $i++) {
			$pid = $result[$i]['rek_pid'];
			if (array_key_exists($pid, $ht) && is_array($ht[$pid])) {
        $result[$i] = array_merge($result[$i], $ht[$pid]);
			}
			if (array_key_exists($pid, $rjt) && is_array($rjt[$pid])) {
        $result[$i] = array_merge($result[$i], $rjt[$pid]);
			}
			if (array_key_exists($pid, $rct) && is_array($rct[$pid])) {
        $result[$i] = array_merge($result[$i], $rct[$pid]);
			}
      if ($aut_id) {
        $record = new RecordObject($result[$i]["rek_pid"], $versionDate);
        // Bump relevance using matchAuthor
        $match_res = $record->matchAuthor($aut_id, FALSE, FALSE, 1, FALSE);

        if (is_array($match_res) && ($match_res[1] >= 0.70)) {
          $result[$i]["Relevance"] *= (1 + $match_res[1]);
          $result[$i]["Relevance_Boosted"] = 1;
        }
      }
		}
		return $result;
	}

	function getRankedJournalInfo($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				jnl_rank AS rank,
				jnl_journal_name AS title,
				jnl_era_year as year
			FROM
				" . APP_TABLE_PREFIX . "matched_journals,
				" . APP_TABLE_PREFIX . "journal
			WHERE
				mtj_jnl_id = jnl_id
				AND mtj_pid = " . $db->quote($pid) . "
	        ORDER BY jnl_era_year DESC
		";

		try {
			$res = $db->fetchAll($stmt, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		if (count($res) == 0) {
			return "";
		}
        $return = array();
		foreach ($res as $rec) {
            $return['rj_'.$rec['year'].'_title'] = $rec['title'];
            $return['rj_'.$rec['year'].'_rank'] = $rec['rank'];
        }
		return $return;
	}

  function getRankedJournal($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
      SELECT
        mtj_jnl_id AS matching_id, ";

      if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
         $stmt .= " ' (' || jnl_era_year || ')' || jnl_journal_name  AS title";
      } else {
         $stmt .= " CONCAT(' (',  jnl_era_year, ') ',jnl_journal_name) AS title";
      }
      $stmt .= "
      FROM
        " . APP_TABLE_PREFIX . "matched_journals LEFT JOIN
        " . APP_TABLE_PREFIX . "journal ON jnl_id = mtj_jnl_id
      WHERE
        mtj_pid = " . $db->quote($pid) . ";
    ";

    try {
      $res = $db->fetchPairs($stmt);
    } catch(Exception $ex) {
      $log->err($ex);
      return "";
    }

    return $res;
  }

	function getRankedJournalInfoByPIDs($pids)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				mtj_pid as pid,
				jnl_rank AS rank,
				jnl_journal_name AS title,
				jnl_era_year as year
			FROM
				" . APP_TABLE_PREFIX . "matched_journals,
				" . APP_TABLE_PREFIX . "journal
			WHERE
				mtj_jnl_id = jnl_id
				AND mtj_pid in (".Misc::arrayToSQLBindStr($pids).")
		";

		try {
			$res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		return $res;
	}

	function getRankedConferenceInfo($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				cnf_rank AS rank,
				cnf_conference_name AS title,
				cnf_era_year as year
			FROM
				" . APP_TABLE_PREFIX . "matched_conferences,
				" . APP_TABLE_PREFIX . "conference
			WHERE
				mtc_cnf_id = cnf_id
				AND mtc_pid = " . $db->quote($pid) . "

	        ORDER BY cnf_era_year DESC;
		";

		try {
			$res = $db->fetchAll($stmt, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		if (count($res) == 0) {
			return "";
		}
        $return = array();
		foreach ($res as $rec) {
            $return['rc_'.$rec['year'].'_title'] = $rec['title'];
            $return['rc_'.$rec['year'].'_rank'] = $rec['rank'];
        }
		return $return;
	}

  function getRankedConference($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
      SELECT
        mtc_cnf_id AS matching_id, ";

      if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
        $stmt .= " cnf_conference_name || ' (' || cnf_era_year || ')' AS title";
      } else {
        $stmt .= " CONCAT(cnf_conference_name, ' (',  cnf_era_year, ')') AS title";
      }
      $stmt .= "
      FROM
          " . APP_TABLE_PREFIX . "matched_conferences LEFT JOIN
          " . APP_TABLE_PREFIX . "conference ON mtc_cnf_id = cnf_id
      WHERE
        mtc_pid = " . $db->quote($pid) . ";
    ";

    try {
      $res = $db->fetchPairs($stmt);
    } catch(Exception $ex) {
      $log->err($ex);
      return "";
    }

    return $res;
  }

	function getRankedConferenceInfoByPIDs($pids)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				mtc_pid as pid,
				cnf_rank AS rank,
				cnf_conference_name AS title,
				cnf_era_year as year
			FROM
				" . APP_TABLE_PREFIX . "matched_conferences,
				" . APP_TABLE_PREFIX . "conference
			WHERE
				mtc_cnf_id = cnf_id
				AND mtc_pid in (".Misc::arrayToSQLBindStr($pids).")
		";

		try {
			$res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		return $res;
	}

	function getHERDCcode($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
      SELECT
        *
      FROM
      (
        SELECT
          rek_herdc_code_pid,
          cvo_title AS herdc_code,
          cvo_desc AS herdc_code_description
        FROM
          " . APP_TABLE_PREFIX . "record_search_key_herdc_code,
          " . APP_TABLE_PREFIX . "controlled_vocab,
          " . APP_TABLE_PREFIX . "controlled_vocab_relationship
        WHERE
          rek_herdc_code = cvo_id
          AND cvr_child_cvo_id = cvo_id
          AND cvr_parent_cvo_id = '450000'
          AND rek_herdc_code_pid = " . $db->quote($pid) . "
      ) q1 LEFT JOIN
      (
        SELECT
          rek_herdc_status_pid,
          cvo_title AS herdc_status
        FROM
          " . APP_TABLE_PREFIX . "record_search_key_herdc_status,
          " . APP_TABLE_PREFIX . "controlled_vocab,
          " . APP_TABLE_PREFIX . "controlled_vocab_relationship
        WHERE
          rek_herdc_status = cvo_id
          AND cvr_child_cvo_id = cvo_id
          AND cvr_parent_cvo_id = '453219'
          AND rek_herdc_status_pid = " . $db->quote($pid) . "

      ) q2 ON q1.rek_herdc_code_pid = q2.rek_herdc_status_pid;";

		try {
			$res = $db->fetchRow($stmt, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		if (count($res) == 0) {
			return "";
		}

		return $res;
	}


	function getHERDCcodeByPIDs($pids)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				rek_herdc_code_pid as pid,
				cvo_title AS herdc_code,
				cvo_desc AS herdc_code_description
			FROM
				" . APP_TABLE_PREFIX . "record_search_key_herdc_code
				INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab ON rek_herdc_code = cvo_id
				INNER JOIN " . APP_TABLE_PREFIX . "controlled_vocab_relationship ON cvr_child_cvo_id = cvo_id AND cvr_parent_cvo_id = '450000'
			WHERE
				rek_herdc_code_pid in (".Misc::arrayToSQLBindStr($pids).")
		";

		try {
			$res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
		} catch(Exception $ex) {
			$log->err($ex);
			return "";
		}

		return $res;
	}

  /**
   * Determines if a given PID is in one of the nominates W.O.S. collections.
   */
  function isInWOScollection($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!defined('APP_WOS_COLLECTIONS') || trim(APP_WOS_COLLECTIONS) == "") {
      return 0;
    }

    $stmt = "SELECT COUNT(*) AS memberships
            FROM " . APP_TABLE_PREFIX . "record_search_key_ismemberof
            WHERE rek_ismemberof_pid = " . $db->quote($pid) . "
            AND rek_ismemberof IN (" . APP_WOS_COLLECTIONS . ");";

   try {
      $res = $db->fetchRow($stmt, $pids, Zend_Db::FETCH_ASSOC);
    } catch(Exception $ex) {
      $log->err($ex);
      return 0;
    }

    return $res['memberships'];
  }

  /**
   * Method used to update the details of a specific Record. Now calls the class.
   *
   * @access  public
   * @param   string $pid The persistent identifier of the record
   * @return  integer 1 if the update worked, -1 otherwise
   */
  function update($pid, $exclude_list=array(), $specify_list=array())
  {
    $log = FezLog::get();

    $record = new RecordObject($pid);
    $ret = $record->fedoraInsertUpdate($exclude_list, $specify_list);

    /*
     * This pid has been updated, we want to delete any
     * cached files as well as cached files for custom views
     */
    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }

    if ($ret) {
      return 1;
    }

    return -1;
  }

  /**
   * Method used to edit the security (FezACML) details of a specific Datastream.
   *
   * @access  public
   * @param   string $pid The persistent identifier of the record
   * @param   string $dsID The datastream ID of the datastream
   * @return  integer 1 if the update worked, -1 otherwise
   */
  function editDatastreamSecurity($pid, $dsID)
  {
    //        $record = new RecordObject($pid);
    $xdis_id = XSD_Display::getID('FezACML for Datastreams');
    $display = new XSD_DisplayObject($xdis_id);
    list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
    $indexArray = array();
    $header = "<".$xsd_element_prefix.$xsd_top_element_name." ";
    $header .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
    $header .= ">\n";
    $xmlObj = Foxml::array_to_xml_instance(
        $array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "",
        $indexArray, '', '', '', '', '', ''
    );
    $xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">\n";
    $xmlObj = $header . $xmlObj;
    $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
    if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
      Fedora_API::callModifyDatastreamByValue(
          $pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
          $xmlObj, "text/xml", "inherit"
      );
    } else {
      Fedora_API::getUploadLocation(
          $pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID,
          "text/xml", "X", null, "true"
      );
    }
    /*
     * This pid has been updated, we want to delete any
     * cached files as well as cached files for custom views
     */

    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }
  }

  /**
   * Method used to update the Admin details (FezMD) of a specific Record.
   *
   * @access  public
   * @param   string $pid The persistent identifier of the record
   * @param   integer $xdis_id The XSD Display ID of the record
   * @return  integer 1 if the update worked, -1 otherwise
   */
  function updateAdminDatastream($pid, $xdis_id)
  {
    $record = new RecordObject($pid);
    if ($record->updateAdminDatastream($xdis_id)) {
      return 1;
    } else {
      return -1;
    }
  }


  /**
   * Method used to add a new Record using the normal report form.
   *
   * @access  public
   * @return  integer The new Record ID
   */
  function insert()
  {
    $record = new RecordObject();
    $ret = $record->fedoraInsertUpdate();
    return $record;
  }



  function findPIDsToCleanBySearchKeyTable($sekTable = "")
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if ($sekTable == "") {
      return false;
    }

    $stmt = "select distinct r1.rek_".$sekTable."_pid from " . APP_TABLE_PREFIX . "record_search_key_".$sekTable." r1
      where
      r1.rek_".$sekTable."_xsdmf_id not in
      (
      select mf2.xsdmf_id
      from " . APP_TABLE_PREFIX . "record_search_key
      inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as mf1 on rek_display_type = mf1.xsdmf_xdis_id
      inner join " . APP_TABLE_PREFIX .
        "xsd_relationship on xsdrel_xsdmf_id = mf1.xsdmf_id and mf1.xsdmf_html_input = 'xsd_ref'
      inner join " . APP_TABLE_PREFIX . "record_search_key_".$sekTable." r2 on rek_pid = r2.rek_".$sekTable."_pid
      inner join " . APP_TABLE_PREFIX. "xsd_display_matchfields mf2 on xsdrel_xdis_id = mf2.xsdmf_xdis_id and r2.rek_".
      $sekTable."_xsdmf_id = mf2.xsdmf_id
      where rek_pid = r1.rek_".$sekTable."_pid
    )";

    try {
      $res = $db->fetchCol($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return $res;
  }


  /**
   * Method used to clean search key index (and solr if enabled) of any index rows where an object's display
   * type was changed while a bug was in the reindex code that didnt handle display
   * types changing properly. An alternative is to just run a fez full reindex, but this method (called
   * from misc/fix_converted_index.php) is quicker.
   *
   * @access  public
   * @param   string $pid      The pid to match on
   * @param   string $sekTable The search key table name
   * @return  boolean true on success, false on failure suffix
   */
  function cleanIndexSearchKey($pid, $sekTable)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "record_search_key_".$sekTable."
         WHERE rek_".$sekTable."_pid = " . $db->quote($pid);

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
   * Method used to remove an entry in the Fez Index.
   *
   * @access  public
   * @param   string $pid The persistent identifier of the record
   * @param   bool $remove_solr should this record be also removed from solr (defaults to true)
   * @return  void
   */
  function removeIndexRecord($pid, $remove_solr=true, $shadow = false, $date='')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($pid)) {
        return -1;
    }

    if ($shadow == true) {
        Zend_Registry::set('version', $date);
        $recordSearchkeyShadow = new Fez_Record_SearchkeyShadow($pid);
    }

    // get list of the Related 1-M search keys, delete those first, then delete the 1-1 core table entries
    $sekDet = Search_Key::getList();
    foreach ($sekDet as $sval) {
      // if is a 1-M needs its own delete sql, otherwise if a 0 (1-1) the core delete will do it
      if ($sval['sek_relationship'] == 1) {
        $sekTable = Search_Key::makeSQLTableName($sval['sek_title']);
        if ($shadow == true) {
            $hasDelta = $recordSearchkeyShadow->hasDelta($sval['sek_title']);
            if ($hasDelta) {
              $recordSearchkeyShadow->copySearchKeyToShadow($sekTable);
            }
        }
        $stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "record_search_key_".$sekTable."
             WHERE rek_".$sekTable."_pid = " . $db->quote($pid);
        try {
          $db->query($stmt);
        }
        catch(Exception $ex) {
          $log->err($ex);
        }
      }
    }

    if ($shadow == true) {
        $recordSearchkeyShadow->copyRecordSearchKeyToShadow();
    }
    $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "record_search_key
         WHERE rek_pid = " . $db->quote($pid);
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }

    //
    // KJ: remove from fulltext index
    //
    if ( APP_SOLR_INDEXER == "ON" && $remove_solr == true) {
      $log->debug("Record::removeIndexRecord() REMOVING ".$pid." FROM QUEUE");
      FulltextQueue::singleton()->remove($pid);
      FulltextQueue::singleton()->commit();
      FulltextQueue::singleton()->triggerUpdate();
    }

    $cache = new fileCache($pid, 'pid='.$pid);
    $cache->poisonCache();

  }

  /**
   * Method used to remove an entry in the Fez Index by its XSD Matching Field ID
   *
   * @access  public
   * @param   string $pid The persistent identifier of the record
   * @param   string $xsdmf_id The XSD Matching Field ID to check for when deleting
   * @return  string The $pid if successful, otherwise -1
   */
  function removeIndexRecordByXSDMF_ID($pid, $xsdmf_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sekDet = Search_Key::getDetailsByXSDMF_ID($xsdmf_id);

    // if couldnt find  a search key,
    // we won't be able to remove this from the index
    if (!isset($sekDet['sek_id'])) {
      return -1;
    }

    if ($sekDet['sek_relationship'] == 1) {
      $sekTableName = "_".$sekDet['sek_title_db'];
    } else {
      $sekTableName = "";
    }

    /*
     * Should only be neccessary to delete in this function for non-core things,
     * as they can just be updated on the insert.
     * If a full delete the other main function would be being used so this should be safe.
     */
    if ($sekDet['sek_relationship'] == 1) {
      $stmt = "DELETE FROM
                " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
                WHERE rek".$sekTableName."_pid = " . $db->quote($pid) . " and rek_".$sekDet['sek_title_db'].
                "_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
      try {
        $db->query($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return -1;
      }
    }

    return $pid;
  }


  /**
     * Returns the timestamp to be used on Shadow table(s) operations.
     * When Fedora Bypass is turned on, utilise the timestamp registered on Zend Register from earlier process.
     *
     * @param string | boolean $proposedTimestamp The timestamp desired by the calling function/method
     * @return string
     */
    public static function setSearchKeyTimestamp($proposedTimestamp = false)
    {
        if (!empty($proposedTimestamp)) {
            $timestamp = $proposedTimestamp;
        } else {
            // Use earlier registered 'Version' variable to achieve uniform timestamp across all search keys updates.
            if (APP_FEDORA_BYPASS == "ON") {
                if (!Zend_Registry::isRegistered('version')) {
                  Zend_Registry::set('version', Date_API::getCurrentDateGMT());

                }
                $timestamp = Zend_Registry::get('version');
            } else {
                $timestamp = Date_API::getCurrentDateGMT();
            }
        }
        return $timestamp;
    }


  /**
   * Updates a PID's search keys values.
   * When specified, update the search key shadow table.
   *
   * @param string $pid Targetted PID
   * @param array $sekData Array of search key names & values pair.
   * The format value for $sekData = array(
   *                                       [0] => Array of 1-to-1 search keys
   *                                       [1] => Array of 1-to-Many search keys
   *                                 )
   * @param boolean $shadow Indication on whether to update shadow table.
   * @param boolean $updateTS Record update timestamp
   * @return boolean The result of the update. True if successful, false otherwise.
   */
  function updateSearchKeys($pid, $sekData, $shadow = false, $updateTS = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $ret = true;
    // Get the timestamp to be used for shadow tables.
    $now = Record::setSearchKeyTimestamp($updateTS);

    /*
     *  Update 1-to-1 search keys
     */
    $stmt[] = 'rek_pid';
    $valuesIns[] = $db->quote($pid);
    $existingData = array(0 => array('rek_pid' => $pid));
    Record::getSearchKeysByPIDS($existingData, true, $shadow);
    $foundDifference = false;
//    $diff = Misc::array_diff_assoc_recursive($sekData, $existingData[0]);
    if (is_array($sekData[0])) {
        foreach ($sekData[0] as $sek_column => $sek_value) {
            //Check that the column value has changed before using it
            if ((!is_array($existingData) || !isset($existingData[0]['rek_'.$sek_column]) || $existingData[0]['rek_'.$sek_column] != $sek_value['xsdmf_value']) &&
              !($sek_value['xsdmf_value'] == '' && $existingData[0]['rek_'.$sek_column] == null) ) {
              $stmt[] = "rek_{$sek_column}, rek_{$sek_column}_xsdmf_id";

              if ($sek_value['xsdmf_value'] === 'NULL' || $sek_value['xsdmf_value'] === '') {
                $xsdmf_value = 'NULL';
                $sek_value['xsdmf_id'] = 'NULL';

              } elseif ($sek_value['xsdmf_value'] == 'on') {
                $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($sek_value['xsdmf_id']);
                $searchKeyDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);
                if ($searchKeyDetails['sek_data_type'] == 'int') {
                  $xsdmf_value = 1;
                } else {
                  $xsdmf_value = 0;
                }
              } else {
                $sek_value['xsdmf_value'] = (is_array($sek_value['xsdmf_value']) && array_key_exists('Year', $sek_value['xsdmf_value']))
                  ? $sek_value['xsdmf_value']['Year'] : $sek_value['xsdmf_value'];
                $xsdmf_value = $db->quote(trim($sek_value['xsdmf_value']));
              }

              $valuesIns[] = "$xsdmf_value, {$sek_value['xsdmf_id']}";
              $valuesUpd[] = "rek_{$sek_column} = $xsdmf_value, rek_{$sek_column}_xsdmf_id = {$sek_value['xsdmf_id']}";

            }

        }

        $table = APP_TABLE_PREFIX . "record_search_key";
        if ($shadow) {
          $table .= "__shadow";
        }

        $stmtIns = "INSERT INTO " . $table . " (" . implode(",", $stmt);
        if ($shadow) {
          $stmtIns .= ", rek_stamp";
        }
        $stmtIns .= ") ";
        $stmtIns .= " VALUES (" . implode(",", $valuesIns);
        if ($shadow) {
          $stmtIns .= ", " . $db->quote($now);
        }
        $stmtIns .= ")";
            $db->beginTransaction();
        if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
          $stmt = $stmtIns ." ON DUPLICATE KEY UPDATE " . implode(",", $valuesUpd);
        } else {
          if (is_array($existingData)) {
            $stmt = "UPDATE ".$table." SET ".implode(",", $valuesUpd)." WHERE rek_pid = '".$pid."'";
          } else {
            $stmt = $stmtIns;
          }


        }

        $stmt = Encoding::toUTF8($stmt);
        try {
          $db->exec($stmt);
                $db->commit();
        }
        catch(Exception $ex) {
                $db->rollBack();
          $log->err($ex);
          $ret = false;
        }
    }
    /*
     *  Update 1-to-Many search keys
     */
    if (is_array($sekData[1])) {
      foreach ($sekData[1] as $sek_table => $sek_value) {
        // First check the data is different before changing
        $diff = array();
        if (is_array($existingData) && is_array($existingData[0]['rek_'.$sek_table]) && is_array($sek_value['xsdmf_value'])) {
          $diff = array_merge(array_diff_assoc($sek_value['xsdmf_value'], $existingData[0]['rek_'.$sek_table]), array_diff_assoc($existingData[0]['rek_'.$sek_table], $sek_value['xsdmf_value']));
        } elseif (is_array($existingData) && $existingData[0]['rek_'.$sek_table] != $sek_value['xsdmf_value']) {
          $diff = array(0=>'true'); // set it to something so it catches in the next if statement
        }

        if (!is_array($existingData) || !isset($existingData[0]['rek_'.$sek_table]) || count($existingData[0]['rek_'.$sek_table]) != count($sek_value['xsdmf_value']) || count($diff) > 0) {

          $stmt = "";
          $sekValTest = (is_array($sek_value['xsdmf_value']))
              ? strtoupper(implode('', $sek_value['xsdmf_value']))
              : $sek_value['xsdmf_value'];
          if (
              !empty($sek_value['xsdmf_value']) && !is_null($sek_value['xsdmf_value'])
              && ($sekValTest != "NULL")
          ) {

            // Added this notEmpty check to look for empty arrays.  Stops fez from writing empty keyword
            // values to fez_record_search_key_keywords table.  -  heaphey
            $notEmpty = 1;  // start assuming that value is not empty
            if (is_array($sek_value['xsdmf_value'])) {
              $stringvalue = implode("", $sek_value['xsdmf_value']);
              if (strlen($stringvalue) == 0) {
                $notEmpty = 0;  // this value is an array and it is empty
                //Error_Handler::logError($sek_value['xsdmf_value']);
              }
            }

            $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($sek_value['xsdmf_id']);
            $searchKeyDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);

            // do final check for cardinality before trying to insert/update an array of values in one to many tables
            if (is_array($sek_value['xsdmf_value'])) {
              if ($searchKeyDetails['sek_cardinality'] == 0) {
                $log->err(
                    "The cardinality of this value is 1-1 but it is in the 1-M data and contains multiple ".
                    "values. We cannot insert/update pid {$pid} for the {$sek_table} table with data: " .
                    var_export($sek_value, true)
                );
                $ret = false;
                continue;
              }
            }

            if ($notEmpty) { // only write values to tables if the value is not empty

              $cardinalityCol = "";
              if (is_array($sek_value['xsdmf_value'])) {
                $cardinalityCol = ",rek_".$sek_table."_order";
              }

              $table = APP_TABLE_PREFIX . "record_search_key_" . $sek_table;

              if ($shadow) {
                $table .= "__shadow";
              }

              // Run REPLACE INTO when cardinality is 0 and we are using MySQL as db connection type
              // Otherwise use INSERT INTO
//              if ($searchKeyDetails['sek_cardinality'] == 0 && is_numeric(strpos(APP_SQL_DBTYPE, "mysql")) ) {
              if (!$shadow) {
                $stmt = "DELETE FROM " . $table . " WHERE rek_".$sek_table."_pid = " . $db->quote($pid);
                $db->exec($stmt);
              }
              $stmt = "INSERT INTO " . $table;
//              }
              $stmt .= " (rek_" . $sek_table . "_pid, rek_" . $sek_table . "_xsdmf_id, rek_" . $sek_table . $cardinalityCol;

              if ($shadow) {
                $stmt .= ", rek_" . $sek_table . "_stamp";
              }
              $stmt .= ") VALUES ";

              if (is_array($sek_value['xsdmf_value'])) {

                $cardinalityVal = 1;
                foreach ($sek_value['xsdmf_value'] as $value ) {
                  $val = "(" . $db->quote($pid) . "," . $db->quote($sek_value['xsdmf_id'], 'INTEGER') . "," . $db->quote($value) . ", $cardinalityVal";
                  if ($shadow) {
                    $val .= ", " . $db->quote($now);
                  }
                  $val .= ")";
                  $stmtVars[] = $val;
                  $cardinalityVal++;
                }
                $stmt .= implode(",", $stmtVars);
                unset($stmtVars);

              } else {
                if ($sek_value['xsdmf_value'] == 'on') {
                  if ($searchKeyDetails['sek_data_type'] == 'int') {
                    $sek_value['xsdmf_value'] = 1;
                  } else {
                    $sek_value['xsdmf_value'] = 0;
                  }
                }
                $stmt .= "(" . $db->quote($pid) . "," . $db->quote($sek_value['xsdmf_id'], 'INTEGER') . "," . $db->quote($sek_value['xsdmf_value']);
                if ($shadow) {
                  $stmt .= ", " . $db->quote($now);
                }
                $stmt .= ")";
              }
              $stmt = Encoding::toUTF8($stmt);
              try {
                $db->exec($stmt);
              }
              catch(Exception $ex) {
                $log->err($ex);
                $ret = false;
              }
            }
          }
        }
      }
    }

    /*
     *	Update Derived search keys
     */

    $derivedSearchKeys = Search_Key::getDerivedList();
    foreach ($derivedSearchKeys as $sekId) {
      $sekDetails = Search_Key::getDetails($sekId);
      $sekTable = $sekDetails['sek_title_db'];
      $deriveFunction = $sekDetails['sek_derived_function'];
      $cardinalityCol = ",rek_".$sekTable."_order";

      if (trim($deriveFunction) != '') {

        $table = APP_TABLE_PREFIX . "record_search_key_" . $sekTable;
        if ($shadow) {
          $table .= "__shadow";
        }

        // Run REPLACE INTO when cardinality is 0 and we are using MySQL as db connection type
        // Otherwise use INSERT INTO
        if ($sekDetails['sek_cardinality'] == 0 && is_numeric(strpos(APP_SQL_DBTYPE, "mysql")) ) {
          $stmt = "REPLACE INTO ". $table;
        } else {
          $stmt = "INSERT INTO " . $table;
        }


        $stmt .= " (rek_{$sekTable}_pid, rek_{$sekTable}) VALUES ";

        eval("\$derivedValue = $deriveFunction(\$pid);");

        // only run the sql if there are derived values to be put into the database
        if (is_array($derivedValue) || trim($derivedValue) != '') {

          // deal with an array of derived values
          if (is_array($derivedValue) && count($derivedValue) && $sekDetails['sek_cardinality'] == 1) {
            $sek_table = $sekDetails['sek_title_db'];
            $cardinalityCol = ",rek_".$sek_table."_order";

            $cardinalityVal = 1;
            $stmtVars = array();
            foreach ($derivedValue as $value) {
              $stmtVars[] = "(".$db->quote($pid).",".$db->quote($value).")";
              $cardinalityVal++;
            }
            $stmt .= implode(",", $stmtVars);
            unset($stmtVars);

          } elseif (trim($derivedValue) != '') {
            // deal with a single derived value
            $stmt .= "(".$db->quote($pid).",".$db->quote($derivedValue).")";
          }

          try {
            $db->exec($stmt);
          }
          catch(Exception $ex) {
            $log->err($ex);
            $ret = false;
          }
        }
      }
    }

    if (defined('APP_HERDC_SUPPORT') && APP_HERDC_SUPPORT == 'ON' &&
        (!defined('PROVISIONAL_CODE_UPDATE_FROM_SCRIPT') || PROVISIONAL_CODE_UPDATE_FROM_SCRIPT === false)) {
      Record::applyProvisionalCode($pid);
    }
    Citation::updateCitationCache($pid, "", $shadow);
    Statistics::updateSummaryStatsOnPid($pid);
    Google_Scholar::updateCitationCache($pid);
    Record::updateThomsonCitationCountFromHistory($pid);
    Record::updateScopusCitationCountFromHistory($pid);
    Record::updateAltmetricScoreFromHistory($pid);

    //
    // KJ: update fulltext index
    //
    if (APP_SOLR_INDEXER == "ON") {
      $log->debug("Record::updateSearchKeys() ADDING ".$pid." TO QUEUE");
      FulltextQueue::singleton()->add($pid);
      FulltextQueue::singleton()->commit();
    }

    if (APP_FILECACHE == "ON" ) {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }

    // Add the updated record for Links AMR processing
    if (APP_AUTO_LINKSAMR_UPLOAD == "ON") {
      $isi_loc = Record::getSearchKeyIndexValue($pid, "ISI Loc", false);
      // only send to the queue if we don't already have an ISI Loc for this pid
      if ($isi_loc == '' || empty($isi_loc)) {
        LinksAmrQueue::get()->add($pid);
      }
    }

    return $ret;
  }



  function applyProvisionalCode($pid)
  {
    // Load up the record, extract the bits we need to determine the provisional HERDC code.


    if(APP_FEDORA_BYPASS == 'ON') {
      $record = new RecordObject($pid);
      $docType = $record->getDocumentType();
      $subType = Record::getSearchKeyIndexValue($pid, "Subtype");
      // no longer use genreType for records, only subtype
//    $genreType = Record::getSearchKeyIndexValue($pid, "Genre Type");
      $existingHERDCcode = Record::getSearchKeyIndexValue($pid, "HERDC code");
    } else {
      $record = new RecordObject($pid);
      $docType = $record->getDocumentType();
      $subType = $record->getFieldValueBySearchKey("Subtype");
      $subType = $subType[0];
//      $genreType = $record->getFieldValueBySearchKey("Genre Type");
//      $genreType = $genreType[0];
      $existingHERDCcode = $record->getFieldValueBySearchKey("HERDC code");
      $existingHERDCcode = $existingHERDCcode[0];
    }

    $provHERDCcode = "";

    // Bail out if we already have a HERDC code
    if ((is_array($existingHERDCcode) && count($existingHERDCcode) != 0) || (!is_array($existingHERDCcode) && ($existingHERDCcode != '' || !is_null($existingHERDCcode) )) ) {
      return;
    }

    //////////////////////////////////////////////////////////////////////////////
    // This is a fairly linear translation of MG's business rules. I have not   //
    // attempted to optimise them in any way. Leaving them in this form makes   //
    // the mapping between the source rules and the code as simple as possible. //
    // Note: There are some extra rules here that were not in MG's original     //
    // document; these have come from email discussions.                        //
    //////////////////////////////////////////////////////////////////////////////
    if ($docType == "Conference Paper") {
      if ($subType == "Fully published paper") {
        $provHERDCcode = "E1";
      } elseif ($subType == "Oral presentation") {
        $provHERDCcode = "EX";
      } elseif ($subType == "Other") {
        $provHERDCcode = "EX";
      } elseif ($subType == "Poster") {
        $provHERDCcode = "EX";
      } elseif ($subType == "Published abstract") {
        $provHERDCcode = "EX";
      }

    } elseif ($docType == "Book") {
      if ($subType == "Research book (original research)") {
        $provHERDCcode = "A1";
      } elseif ($subType == "Edited book") {
        $provHERDCcode = "A3";
      } elseif ($subType == "Creative work") {
        $provHERDCcode = "AX";
      } elseif ($subType == "Other") {
        $provHERDCcode = "AX";
      } elseif ($subType == "Reference work, encyclopaedia, manual or handbook") {
        $provHERDCcode = "AX";
      } elseif ($subType == "Textbook") {
        $provHERDCcode = "AX";
      }

    } elseif ($docType == "Book Chapter") {
      if ($subType == "Research book chapter (original research)") {
        $provHERDCcode = "B1";
      } elseif ($subType == "Critical review of research, literature review, critical commentary") {
        $provHERDCcode = "B1";
      } elseif ($subType == "Introduction, foreword, editorial or appendix") {
        $provHERDCcode = "BX";
      } elseif ($subType == "Other") {
        $provHERDCcode = "BX";
      } elseif ($subType == "Creative work") {
        $provHERDCcode = "BX";
      } elseif ($subType == "Chapter in reference work, encyclopaedia, manual or handbook") {
        $provHERDCcode = "BX";
      } elseif ($subType == "Chapter in textbook") {
        $provHERDCcode = "BX";
      }

    } elseif ($docType == "Journal Article") {
      if ($subType == "Article (original research)") {
        $provHERDCcode = "C1";
      } elseif ($subType == "Correction/erratum") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Creative work") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Discussion - responses, round table/panel discussions, Q&A, reply") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Editorial") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Letter to editor, brief commentary or brief communication") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Other") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Review of book, film, TV, video, software, performance, music etc") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Critical review of research, literature review, critical commentary") {
        $provHERDCcode = "C1";
      }

    } elseif ($docType == "Online Journal Article") {
      if ($subType == "Article (original research)") {
        $provHERDCcode = "C1";
      } elseif ($subType == "Correction/erratum") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Creative work") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Discussion - responses, round table/panel discussions, Q&A, reply") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Editorial") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Letter to editor, brief commentary or brief communication") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Other") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Review of book, film, TV, video, software, performance, music etc") {
        $provHERDCcode = "CX";
      } elseif ($subType == "Critical review of research, literature review, critical commentary") {
        $provHERDCcode = "C1";
      }

    }

    if ($provHERDCcode != '') {

      $history = "was set based on Provisional HERDC assignment rules";

      // HERDC code
      $provCode = Controlled_Vocab::getID($provHERDCcode);
      $record->addSearchKeyValueList(array("HERDC code"), array($provCode), true, $history);

      // HERDC status
      $provisional = Controlled_Vocab::getID('Provisional Code');
      $record->addSearchKeyValueList(array("HERDC Status"), array($provisional), true, $history);

      // Institutional status
      $unknown = Controlled_Vocab::getID('Unknown');
      $record->addSearchKeyValueList(array("Institutional Status"), array($unknown), true, $history);
    }

    return;
  }


  /**
   * Sets the index during batch import. Could also be used in future versions for objects in
   * Fedora that are not in the index yet.
   * EG a "Re-index Fedora" type of admin function.
   *
   * @access  public
   * @param   string $xdis_id  The XSD Display ID of the object
   * @param   string $pid The persistent identifier of the object
   * @return  void
   */
  function setIndexMatchingFields($pid)
  {
    $log = FezLog::get();
    $record = new RecordObject($pid);
    $record->setIndexMatchingFields();

    AuthIndex::setIndexAuth($pid); //set the security index
  }

  function setIndexMatchingFieldsRecurse($pid, $bgp=null, $fteindex = true)
  {
    if (!empty($bgp)) {
      $bgp->setStatus("Processing ".$pid);
      $bgp->incrementProgress();
    }
    $record = new RecordObject($pid);
    $record->setIndexMatchingFields();
    if (!$record->isCommunity() && !$record->isCollection() && $fteindex) {
      FulltextIndex::indexPid($pid);
    }
    // recurse children
    // NOTE: this only finds objects that are already indexed correctly at least when it comes to
    //          memberOf
    $children = $record->getChildrenPids();
    foreach ($children as $child_pid) {
      Record::setIndexMatchingFieldsRecurse($child_pid, $bgp, $fteindex);
    }
  }

  /**
   * Method used to get the FezACML datastream XML content of the record
   *
   * @access  public
   * @package fedora
   * @param   string $pid The persistent identifier of the object
   * @param   string $dsID (optional) The datastream ID
   * @param   string $createdDT (optional) Fedora timestamp of version to retrieve
   * @return  domdocument $xmldoc A Dom Document of the XML or false if not found
   *
   *
   * @uses
   *   - FezACML->getUsersByRolePidAssoc();
   *       called by workflow/edit_user_view.php
   *       used by "Manage Thesis Assessor Access" workflow.
   *
   *   - #migrate_fedora_managedcontent_to_fezCAS.php
   *     Since this is migrating from Fedora system, we can ignore it.
   *
   *   - #Auth->getAuthorisationGroups();
   *      These 3 calls are from Fedora codebase, so this can be ignored.
   *
   *   - #Auth->getAuth();
   *     3 calls from this method.
   *     Auth->getAuth is called by AuthIndex->setIndexAuthBGP(),
   *       called by BackgroundProcess_Index_Auth->run(),
   *       called by AuthIndex->setIndexAuth(),
   *       called by workflow/regenerate_auth_index.php
   *       used by "Regenerate Auth Index" workflow
   *         Workflow description:
   *         "Regenerates the Auth Index for a selected Object or for the whole repository if no record is selected.
   *         This does not make changes to the fedora repository."
   *     Since this is method is traced to workflow that only used on Fedora system, we can ignore this.
   *
   *   - #Auth->getParentACMLs();
   *     The calls that need attention are from Auth->getAuth(); (can ignored, see above point).
   *
   */
  function getACML($pid, $dsID="", $createdDT=null)
  {
    static $acml_cache;
    $ds_pattern = false;
    $ds_search = 'FezACML';
    if ($dsID != "") {
      if (isset($acml_cache['ds'][$dsID][$pid])) {
        return $acml_cache['ds'][$dsID][$pid];
      } else {
        $dsIDCore = preg_replace("/(web_|preview_|thumbnail_|stream_)/", "", $dsID);
        $dsIDCore = substr($dsIDCore, 0, strrpos($dsIDCore, "."));
        $ds_pattern = '/^FezACML_'.$dsIDCore.'\.(.*)xml$/';
        $ds_search = 'FezACML_'.$dsID.'.xml';
      }
    } else {
      if (isset($acml_cache['pid'][$pid])) {
        return $acml_cache['pid'][$pid];
      }
    }
    $dsExists = Fedora_API::datastreamExists($pid, $ds_search, true, $ds_pattern);
    if ($dsExists !== false) {
      if ($ds_pattern != false) {
        $DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsExists);
      } else {
        $DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $ds_search);
      }

      $xmlACML = @$DSResultArray['stream'];
      $xmldoc= new DomDocument();
      $xmldoc->preserveWhiteSpace = false;
      $xmldoc->loadXML($xmlACML);
      if ($GLOBALS['app_cache']) {
        // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
        if (!is_array($acml_cache) || count($acml_cache) > 10) {
          $acml_cache = array();
        }
        if ($dsID != "") {
          $acml_cache['ds'][$dsID][$pid] = $xmldoc;
        } else {
          $acml_cache['pid'][$pid] = $xmldoc;
        }
      }
      return $xmldoc;
    } else {
      if ($GLOBALS['app_cache']) {
        // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
        if (!is_array($acml_cache) || count($acml_cache) > 10) {
          $acml_cache = array();
        }
        if ($dsID != "") {
          $acml_cache['ds'][$dsID][$pid] = false;
        } else {
          $acml_cache['pid'][$pid] = false;
        }
        return false;
      }
    }
  }

  /**
   * Method used to get the details for a specific Record gotten directly from the Fedora repository.
   *
   * @access  public
   * @param   string $pid The persistent identifier of the object
   * @param   string $xdis_id  The XSD Display ID of the object
   * @param   string $createdDT (optional) Fedora timestamp of version to retrieve
   * @return  array $xsdmf_array The details for the XML object against its XSD Matching Field IDs
   * @uses Search found no results on the usage of this method.
   *   Most record details calls are referring to RecordObject->getDetails();
   *
   */
  function getDetails($pid, $xdis_id, $createdDT=null)
  {
    $log = FezLog::get();

    // Get the Datastreams.
    $datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
    foreach ($datastreamTitles as $dsValue) {
      $DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title'], $createdDT);
      if (isset($DSResultArray['stream'])) {
        $xmlDatastream = $DSResultArray['stream'];
        $xsd_id = XSD_Display::getParentXSDID($dsValue['xsdmf_xdis_id']);
        $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
        $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
        $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

        $xmlnode = new DomDocument();
        $xmlnode->loadXML($xmlDatastream);
        echo $xmlDatastream;
        $array_ptr = array();
        Misc::dom_xml_to_simple_array(
            $xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id
        );
      }
    }
    return $xsdmf_array;
  }

  /**
   * Get details about a pid(s)
   *
   * @param string/array $pid  the pid(s) to get details about
   *
   * @return array  the pid and their details ie. title, description etc
   * @access public
   */
  function getDetailsLite($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if ($pid == '') {
      return array();
    }
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $order = '';
    $bind_param = array();

    if ( is_array($pid) && count($pid) > 0) {
      $where = "rek_pid IN (".Misc::arrayToSQLBindStr($pid).")";
      $bind_param = $pid;
      $order = "rek_created_date";
    } elseif (!is_array($pid)) {
      $where = "rek_pid = ".$db->quote($pid);
    } else {
      return array();
    }

    $stmt =  "SELECT * " .
                "FROM {$dbtp}record_search_key " .
                "WHERE $where";

    if ( $order ) {
      $stmt .= " ORDER BY $order DESC";
    }

    try {
      $res = $db->fetchAll($stmt, array_values($bind_param), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    $usr_id = Auth::getUserID();
    Record::getAuthWorkflowsByPIDS($res, $usr_id);
    return $res;
  }

   /**
   * Get if  pid exists
   *
   * @param string $pid  the pid to get to see if it exists
   *
   * @return true/false
   * @access public
   */
  function getIfRecordExists($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if ($pid == '') {
      return false;
    }

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $where = "rek_pid = ".$db->quote($pid);
    $stmt =  "SELECT rek_pid " .
                "FROM {$dbtp}record_search_key " .
                "WHERE $where";

    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return !empty($res);
  }


  /**
   * Method used to get the default record XDIS_ID
   *
   * Developer Note: Need to make this able to be set in Administrative interface and stored in the Fez database,
   * although this is not really much used anymore.
   *
   * @access  public
   * @return  integer $xdis_id The XSD Display ID of a generic Fez record
   */
  function getRecordXDIS_ID()
  {
    // will make this more dynamic later. (probably feed from a mysql table which can be
    // configured in the gui admin interface).
    // this isn't realy used much anymore
    $xdis_id = 5;
    return $xdis_id;
  }


  function getRecentRecords()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt =  'SELECT * ' .
                'FROM ' . APP_TABLE_PREFIX . 'recently_added_items ';

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_COLUMN); //DB_FETCHMODE_FLIPPED
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  function getRecentDLRecords()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt =  'SELECT * ' .
                'FROM ' . APP_TABLE_PREFIX . 'recently_downloaded_items '.
                'ORDER BY rdi_downloads DESC ';

    try {
      $res = $db->fetchAssoc($stmt); //DB_FETCHMODE_FLIPPED
      $log->info($res);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }


  /**
   * .
   *
   * @access  public
   * @param string $options The search parameters
   * @return array $res2 The index details of records associated with the search params
   */
  function getListing(
      $options, $approved_roles=array(9,10), $current_page=0,$page_rows="ALL", $sort_by="Title",
      $getSimple=false, $citationCache=false, $filter=array(), $operator='AND', $use_faceting = false,
      $use_highlighting = false, $doExactMatch = false, $facet_limit = APP_SOLR_FACET_LIMIT,
      $facet_mincount = APP_SOLR_FACET_MINCOUNT, $getAuthorMatching = false, $versionDate=null, $forceLocal = false
  )
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (APP_SOLR_SWITCH == "ON" && $forceLocal == false) {
      return Record::getSearchListing(
          $options, $approved_roles, $current_page, $page_rows, $sort_by, $getSimple,
          $citationCache, $filter, $operator, $use_faceting, $use_highlighting, $doExactMatch,
          $facet_limit, $facet_mincount, $getAuthorMatching
      );
    } else {
      $options = array_merge($options, $filter);
    }

    if ($page_rows == "ALL") {
      $page_rows = 9999999;
    }
    if ($page_rows == "") {
      $page_rows = APP_DEFAULT_PAGER_SIZE;
    }

    $start = $current_page * $page_rows;
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $current_row = $current_page * $page_rows;

    if ( Search_Key::getMaxID() == 0 ) {
      $log->err(array('No Search Keys', __FILE__, __LINE__));
      return array();
    }

    // make sure the sort by is setup well
    if (!is_numeric(strpos($sort_by, "searchKey"))) {
      $sort_by_id = Search_Key::getID($sort_by);
      if ($sort_by_id != "") {
        $sort_by = "searchKey".$sort_by_id;
      } else {
        $sort_by_id = Search_Key::getID("Title");
        $sort_by = "searchKey".$sort_by_id;
      }
    }

    //echo $sort_by . '<br />';
    $searchKey_join = Record::buildSearchKeyJoins($options, $sort_by, $operator, $filter);

    $authArray = Collection::getAuthIndexStmt($approved_roles, "r1.rek_pid");
    $authStmt = $authArray['authStmt'];

    if(!is_null($versionDate))
    {
        $stmt = " FROM {$dbtp}record_search_key__shadow AS r1 ";
    }
    else
    {
        $stmt = " FROM {$dbtp}record_search_key AS r1 ";
    }

    $stmt .= $searchKey_join[SK_JOIN].$searchKey_join[SK_LEFT_JOIN].$authStmt." ";

    if(!is_null($versionDate))
    {
        $searchKey_join[SK_WHERE] = str_replace(')',
        		" AND rek_stamp = '$versionDate' )",
                $searchKey_join[SK_WHERE]);
    }

    $stmt .= $searchKey_join[SK_WHERE];

    // If the DB is mysql then you can use SQL_NUM_ROWS, even with a limit and get better performance, otherwise you
    // need to do a seperate query to get the total count
    if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
      $total_rows = 1;
      $stmt =  "SELECT ".APP_SQL_CACHE." SQL_CALC_FOUND_ROWS DISTINCT r1.* ".$searchKey_join[SK_FULLTEXT_REL].
          " ".$stmt.$searchKey_join[SK_GROUP_BY];
      $stmt .= " ORDER BY ".$searchKey_join[SK_SORT_ORDER]." r".$searchKey_join[SK_KEY_ID].".rek_pid DESC ";
    } else {
      $countStmt =  "SELECT ".APP_SQL_CACHE." COUNT(r1.rek_pid) ".$stmt;
      try {
        $total_rows = $db->fetchOne($countStmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
      }
      $stmt =  "SELECT ".APP_SQL_CACHE." r1.* ".$searchKey_join[SK_FULLTEXT_REL]." ".$stmt.$searchKey_join[SK_GROUP_BY];
      $stmt .= " ORDER BY ".$searchKey_join[SK_SORT_ORDER]." r".$searchKey_join[SK_KEY_ID].".rek_pid DESC ";
    }

    $usr_id = Auth::getUserID();
    if ($total_rows > 0) {
      try {
        $stmt = $db->limit($stmt, $page_rows, $start);
      }
      catch(Exception $ex) {
        $log->err($ex." stmt = $stmt, page_rows = $page_rows, start = $start");
      }

      try {
        $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
      }
      catch(Exception $ex) {
        $log->err($ex);
        $res = array();
      }

      //now add on the other search keys, security roles, workflows, if necessary
      if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
        try {
          $total_rows = $db->fetchOne('SELECT FOUND_ROWS()');
        }
        catch(Exception $ex) {
          $log->err($ex);
        }
      }
      $getSimple = false;
      $citationCache = false;
      if (count($res) > 0) {
        if ($getSimple == false || empty($getSimple)) {
          if ($citationCache == false) {
            Record::getSearchKeysByPIDS($res,true);
						if (APP_MY_RESEARCH_MODULE == 'ON') {
						  $res = Record::getResearchDetailsbyPIDS($res, $getAuthorMatching, $versionDate);
						}
            InternalNotes::readNotes($res);
          }
          Record::identifyThumbnails($res, $citationCache);
          Record::getAuthWorkflowsByPIDS($res, $usr_id);
        }
      }
    } else {
      $res = array();
    }

    if ($citationCache == true) {
      $res = Citation::renderIndexCitations($res, 'APA', true, false);
    }

    $thumb_counter = 0;
    if (!empty($res)) {
      // needed for viewer
      $res = Auth::getIndexAuthCascade($res);
      foreach ($res as $key => $rec) {
        if ($res[$key]['thumbnail'][0] != "") {
          $thumb_counter++;
        }
        $res[$key]['isLister'] = true;
        $res[$key]['rek_citation_stripped'] = strip_tags($res[$key]['rek_citation']);
      }
    }
    $list = $res;
    if (count($res) != 0) {
      $thumb_ratio = $thumb_counter / count($res);
    } else {
      $thumb_ratio = 0;
    }
    if ($page_rows == 0) {
      $total_pages = 1;
    } else {
      $total_pages = intval($total_rows / $page_rows);
      if ($total_rows % $page_rows) {
        $total_pages++;
      }
    }

    $search_info = rtrim($searchKey_join[SK_SEARCH_TXT], ', ');
    if ($searchKey_join[SK_WHERE] == "") {
      $noOrder = 1;
    } else {
      $noOrder = 0;
    }
    $next_page = ($current_page >= $total_pages) ? -1 : $current_page + 1;
    $prev_page = ($current_page <= 0) ? -1 : $current_page - 1;
    $last_page = $total_pages - 1;
    $current_last_row = $current_row + count($list);
    if (($current_page - 5) > 0) {
      $start_range = $current_page - 5;
    } else {
      $start_range = 0;
    }
    if (($current_page + 5) >= $last_page) {
      $end_range = $last_page + 1;
    } else {
      $end_range = $current_page + 5;
    }
    $printable_page = $current_page + 1;
    $info = compact(
        'total_rows', 'page_rows', 'current_row', 'current_last_row', 'current_page', 'total_pages',
        'next_page', 'prev_page', 'last_page', 'noOrder', 'search_info', 'start_range', 'end_range',
        'printable_page', 'thumb_ratio'
    );
    return compact('info', 'list');
  }


  function getListingForCitation($options, $approved_roles, $sort_by="Title", $filter=array(), $operator='AND')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $searchKey_join = Record::buildSearchKeyJoins($options, $sort_by, $operator, $filter);

    $authArray = Collection::getAuthIndexStmt($approved_roles, "r1.rek_pid");
    $authStmt = $authArray['authStmt'];
    $joinStmt = $authArray['joinStmt'];

    $stmt =  "SELECT DISTINCT r1.* " .
                  "FROM {$dbtp}record_search_key AS r1 ".
    $searchKey_join[SK_JOIN].$searchKey_join[SK_LEFT_JOIN].$authStmt." ".
    $searchKey_join[SK_WHERE];

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      $res = array();
    }

    if (count($res) > 0) {

      $usr_id = Auth::getUserID();
      Record::getSearchKeysByPIDS($res, true);
      Record::getChildCountByPIDS($res, $usr_id);

    }

    return array(
         'info'  =>  '',
         'list'  =>  $res,
    );
  }

  /**
   * Extracts and prepares search specific parameters from the $_GET/$_POST request.
   * This performs kind of a parameter validation and packs variables in
   * arrays.
   *
   * KJ/ETH 2/08
   *
   * @param unknown_type $request
   */

  private function extractSearchParameters()
  {
    // WORD SEARCH

    // w(\d+)	keyword number $1, example w1=test
    // wop=[and|or]	global default operator, used if no individual operators were set
    // wf(\d+)	name of field number $1

    $wop = Misc::GETorPOST("wop");
    if (!$wop) {
      $wop = "AND";
    }
    // start with search word #1
    $num = 1;
    $params['words'] = array();

    while (Misc::GETorPOST("w$num")) {
      $w = Misc::GETorPOST("w$num");
      $wf = Misc::GETorPOST("wf$num");
      if (!$wf || $wf=='ALL') {
        // default: search everything/all fields
        $wf = "ALL";
      } else {
        // map search key id to name
        $wf = strtolower($wf);
        /*
         $id = preg_replace("/searchKey(\d*)/",'$1', $wf);
         if ($id) {
          $wf = Search_key::getTitle($id);
          }
          */
      }
      $wopn = Misc::GETorPOST("wop$num");
      if (!$wopn) {
        // use default operator
        $wopn = $wop;
      }
      $keyword = array('wf' => $wf, 'w' => $w, 'op' => $wopn);
      $params['words'][$num] = $keyword;

      $num++;
    }

    // EXPERT SEARCH
    $q = Misc::GETorPOST('search_keys');
    if ($q) {
      $params['direct'] = array('q' => $q[0]);
    }


    // FILTERS
    // dates
    // ...
    //var_dump($params);
    return $params;
  }

  /**
   * Searches repository for matching documents/collections/communities.
   *
   * @access  public
   * @param string $options The search parameters
   * @return array $res2 The index details of records associated with the search params
   */
  function getSearchListing(
      $options, $approved_roles=array(9,10), $current_page=0, $page_rows="ALL",
      $sort_by="", $getSimple=false, $citationCache=false, $filter=array(), $operator="AND",
      $use_faceting = false, $use_highlighting = false, $doExactMatch = false, $facet_limit = APP_SOLR_FACET_LIMIT,
      $facet_mincount = APP_SOLR_FACET_MINCOUNT, $getAuthorMatching = false
  )
  {

    $log = FezLog::get();

    // paging preparation
    if ($page_rows == "ALL") {
      $page_rows = 9999999;
    }

      // make sure the sort by is setup well
    if (!is_numeric(strpos($sort_by, "searchKey"))) {
        $sort_by_id = Search_Key::getID($sort_by);
        $cardinality = Search_Key::getCardinality($sort_by);
        if (($sort_by_id == "") || ($cardinality == '1')) {
            $sort_by_id = Search_Key::getID("Title");
        }
        $sort_by = "searchKey".$sort_by_id;
    }

    $start = $current_page * $page_rows;
    $current_row = $current_page * $page_rows;

    $searchKey_join = self::buildSearchKeyFilterSolr($options, $sort_by, $operator, $doExactMatch);
    $filter_join = self::buildSearchKeyFilterSolr($filter, "", $operator, $doExactMatch);
    $index = new FulltextIndex_Solr(true);

    $res = $index->searchAdvancedQuery(
        $searchKey_join, $filter_join, $approved_roles, $start, $page_rows, $use_faceting,
        $use_highlighting, $facet_limit, $facet_mincount
    );
    $total_rows = $res['total_rows'];
    $facets = $res['facets'];
    $snips = $res['snips'];
    $res = $res['docs'];

    $usr_id = Auth::getUserID();
    // disable citation caching for the moment - CK commented out forced citation true on 17/6/08, was a Rhys thin
    //		$citationCache = true;
    if (count($res) > 0) {
      if ($getSimple == false || empty($getSimple)) {
        // temporarily jus set everything to citation cache true to test out getting lookups, reseasrc and notes from solr now
        $citationCache = true;
        if ($citationCache == false) {
          if (is_numeric($usr_id)) {
            //This can be significently made faster by Record::getExtendedPidInfo($res, array('Display Type', 'HERDC Status', 'HERDC code', 'Institutional Status', 'Follow up Flags', 'Follow up Flags IMU'));
              Record::getExtendedPidInfo($res);
          } else {
              Record::getExtendedPidInfo($res);
          }
          if (APP_MY_RESEARCH_MODULE == 'ON') {
            $res = Record::getResearchDetailsbyPIDS($res, $getAuthorMatching);
          }
          InternalNotes::readNotes($res);
        }
        Record::identifyThumbnails($res, $citationCache);
        Record::getAuthWorkflowsByPIDS($res, $usr_id);
        Record::getChildCountByPIDS($res, $usr_id);
      }

    }
    $thumb_counter = 0;
    // KJ/ETH: if the object came up to here, it can be listed (Solr filter!)
    if (!empty($res)) {

      // needed for viewer
      $res = Auth::getIndexAuthCascade($res);

      foreach ($res as $key => $rec) {
//        if ($res[$key]['rek_display_type_lookup'] != "") {
//          $res[$key]['rek_coin'] = Misc::OpenURL($rec);
//        }
        if (array_key_exists('thumbnail', $res[$key]) && $res[$key]['thumbnail'][0] != "") {
          $thumb_counter++;
        }
        $res[$key]['isLister'] = true;
        $res[$key]['rek_citation_stripped'] = strip_tags($res[$key]['rek_citation']);
      }
    }
    if (count($res) != 0) {
      $thumb_ratio = $thumb_counter / count($res);
    } else {
      $thumb_ratio = 0;
    }
    // query display...
    $search_info = rtrim($searchKey_join[SK_SEARCH_TXT], ', ');

    $list = $res;
    $total_pages = intval($total_rows / $page_rows);
    if ($total_rows % $page_rows) {
      $total_pages++;
    }

    $noOrder = 0;  // KJ: don't know what this is...

    $last_page = $total_pages - 1;
    $next_page = ($current_page >= $last_page) ? -1 : $current_page + 1;
    $prev_page = ($current_page <= 0) ? -1 : $current_page - 1;
    $current_last_row = $current_row + count($list);
    if (($current_page - 10) > 0) {
      $start_range = $current_page - 10;
    } else {
      $start_range = 0;
    }
    if (($current_page + 10) >= $last_page) {
      $end_range = $last_page + 1;
    } else {
      $end_range = $current_page + 10;
    }
    $printable_page = $current_page + 1;

    // return result
    $info = compact(
        'total_rows', 'page_rows', 'current_row', 'current_last_row',
        'current_page', 'total_pages', 'next_page', 'prev_page', 'last_page',
        'noOrder', 'search_info', 'start_range', 'end_range', 'printable_page', 'thumb_ratio'
    );

    $log->debug($info);
    return compact('info', 'list', 'facets', 'snips');
  }

  function identifyThumbnails(&$result, $citationCache = false)
  {

    if ($citationCache == true) { //need to go and get the left join for file_attachments if simple
      $pids = array();
      for ($i = 0; $i < count($result); $i++) {
        $pids[] = $result[$i]["rek_pid"];
      }
      if (count($pids) == 0) {
        return;
      }
      $sek_details = Search_Key::getBasicDetailsByTitle("File Attachment Name");
      $sek_sql_title = $sek_details['sek_title_db'];
      $res = array();
      $res = Record::getSearchKeyByPIDS($sek_sql_title, $pids);
      $t = array();
      $p = array();
      for ($i = 0; $i < count($res); $i++) {
        if (!array_key_exists($res[$i]["rek_".$sek_sql_title."_pid"], $t)) {
          $t[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
        }
        $t[$res[$i]["rek_".$sek_sql_title."_pid"]][] =  $res[$i]["rek_".$sek_sql_title];
      }
      // now populate the $result variable again
      for ($i = 0; $i < count($result); $i++) {
        if (!array_key_exists("rek_".$sek_sql_title, $result[$i])) {
          $result[$i]["rek_".$sek_sql_title] = array();
        }
        if (array_key_exists($result[$i]["rek_pid"], $t)) {
            $result[$i]["rek_".$sek_sql_title] = $t[$result[$i]["rek_pid"]];
        }
      }
    }

    for ($i = 0; $i < count($result); $i++) {
      if (array_key_exists('rek_file_attachment_name', $result[$i])) {
          for ($x = 0; $x < count($result[$i]['rek_file_attachment_name']); $x++) {
            if (is_numeric(strpos($result[$i]['rek_file_attachment_name'][$x], "thumbnail_"))) {
              if (!is_array(@$result[$i]['thumbnail'])) {
                $result[$i]['thumbnail'] = array();
              }
              array_push($result[$i]['thumbnail'], $result[$i]['rek_file_attachment_name'][$x]);
              if (APP_EXIFTOOL_SWITCH == 'ON') {
                $exif_details = Exiftool::getDetails($result[$i]['rek_pid'], $result[$i]['rek_file_attachment_name'][$x]);
                if (count($exif_details) != 0) {
                  if (!is_array(@$result[$i]['thumbnail_width'])) {
                    $result[$i]['thumbnail_width'] = array();
                  }
                  if (!is_array(@$result[$i]['thumbnail_height'])) {
                    $result[$i]['thumbnail_height'] = array();
                  }
                  array_push($result[$i]['thumbnail_width'], $exif_details['exif_image_width']);
                  array_push($result[$i]['thumbnail_height'], $exif_details['exif_image_height']);
                }
              }
            }
            if (is_numeric(strpos($result[$i]['rek_file_attachment_name'][$x], "stream_"))) {
              if (!is_array(@$result[$i]['stream'])) {
                $result[$i]['stream'] = array();
              }
              array_push($result[$i]['stream'], $result[$i]['rek_file_attachment_name'][$x]);
            }
            if (is_numeric(strpos($result[$i]['rek_file_attachment_name'][$x], "web_"))) {
              if (!is_array(@$result[$i]['web_image'])) {
                  $result[$i]['web_image'] = array();
              }
              array_push($result[$i]['web_image'], $result[$i]['rek_file_attachment_name'][$x]);
            }
          }
      }
    }
  }

  function getLookup($param, $lookupFunction, &$cacheEval) {
        // Wrap param in single quote, if the value is a string
        if (!empty($param) && gettype($param)=='string'){
          $param = "'". $param ."'";
        }

        $func = $lookupFunction.'('. $param .');';
        if (array_key_exists($func, $cacheEval)) {
            return $cacheEval[$func];
        } else {
            eval('$lookupValue = '. $func);
            $cacheEval[$func] = $lookupValue;
            return $lookupValue;
        }
    }

  //Adds needed lookups for search listings for spyglasses
  //$limitedLookupTo restrict lookup to only certain searchKeys
  function getExtendedPidInfo(&$result, $limitLookupsTo = false) {
      $cacheEval = array();
      //$lookups = Search_Key::getSearchKeysWithLookups();
      $lookups = Search_Key::getList(false);
      foreach($lookups as $lookup) {
          if (empty($lookup['sek_lookup_function']) || (is_array($limitLookupsTo) && !in_array($lookup['sek_title'], $limitLookupsTo))) {
              continue;
          }
          $lookupVariable = 'rek_'.Search_Key::makeSQLTableName($lookup['sek_title']);
          for ($i = 0; $i < count($result); $i++) {
              if (array_key_exists($lookupVariable, $result[$i]) && !empty($result[$i][$lookupVariable])) {
                  if ($lookup['sek_cardinality'] == 0) {
                      $result[$i][$lookupVariable.'_lookup'] = Record::getLookup($result[$i][$lookupVariable], $lookup['sek_lookup_function'], $cacheEval);
                  } else {
                      $result[$i][$lookupVariable.'_lookup'] = array();
                      foreach ($result[$i][$lookupVariable] as $value) {
                            array_push($result[$i][$lookupVariable.'_lookup'], Record::getLookup($value, $lookup['sek_lookup_function'], $cacheEval));
                      }
                  }
              } else {
                  $result[$i][$lookupVariable.'_lookup'] = "";
              }
          }
      }
  }

  function getSearchKeysByPIDS(&$result, $forceGetExtra = false, $shadow = false)
  {

    $pids = array();
    for ($i = 0; $i < count($result); $i++) {
      $pids[] = $result[$i]["rek_pid"];
    }
    if (count($pids) == 0) {
      return;
    }
    $sek_details = Search_Key::getList(false);
    $cache_eval = array();
    $param = '';

    foreach ($sek_details as $sekKey => $sekData) {
      $sek_sql_title = Search_Key::makeSQLTableName($sekData['sek_title']);
      if ($sekData['sek_relationship'] == 0) { //already have the data, just need to do any required lookups for 1-1
          for ($i = 0; $i < count($result); $i++) {
            // Solr already returns all this data, just need the lookups, unless called from somewhere other than solr
            if ($forceGetExtra == true) {
              $res = array();
              if ($shadow) {
                $res[$i]['rek_'.$sek_sql_title] = Record::getSearchKeyIndexValueShadow($result[$i]['rek_pid'], $sekData['sek_title'], false);
              } else {
                $res[$i]['rek_'.$sek_sql_title] = Record::getSearchKeyIndexValue($result[$i]['rek_pid'], $sekData['sek_title'], false);
              }

            } else {
              $res = $result;
            }

            // If the base array doesnt have the 1-1 search keys already, and force is on, then add them
            if ($forceGetExtra == true && $res && !array_key_exists('rek_'.$sek_sql_title, $result[$i]) && array_key_exists('rek_'.$sek_sql_title, $res[$i])) {
              $result[$i]['rek_'.$sek_sql_title] = $res[$i]['rek_'.$sek_sql_title];
            }
            if ($sekData['sek_lookup_function'] != "") {
                if (array_key_exists('rek_'.$sek_sql_title, $result[$i]) && !empty($result[$i]['rek_'.$sek_sql_title])) {
                  $param = $result[$i]['rek_'.$sek_sql_title];
                  // Wrap param in single quote, if the value is a string
                  if (!empty($param) && gettype($param)=='string'){
                    $param = "'". $param ."'";
                  }

                  $func = $sekData['sek_lookup_function'].'('. $param .');';
                  if (!is_callable(preg_replace('/\(.*\);/', '', $func))) {
                    $result[$i]["rek_".$sek_sql_title."_lookup"] = "";
                  } elseif (array_key_exists($func, $cache_eval)) {
                    $result[$i]["rek_".$sek_sql_title."_lookup"] = $cache_eval[$func];
                  } else {
                    eval('$result[$i]["rek_'.$sek_sql_title.'_lookup"] = '.$func);
                    $cache_eval[$func] = $result[$i]["rek_".$sek_sql_title."_lookup"];
                  }
                } else {
                  $result[$i]['rek_'.$sek_sql_title.'_lookup'] = "";
                }
              }
            }

      } else {
        if ($forceGetExtra == true) {
          $res = Record::getSearchKeyByPIDS($sek_sql_title, $pids, $shadow);
        } else {
          $res = $result;
        }

        $t = array();
        $p = array();

        for ($i = 0; $i < count($res); $i++) {
          if (is_array($res[$i]) && array_key_exists("rek_".$sek_sql_title."_pid", $res[$i])) {
              if (array_key_exists("rek_".$sek_sql_title."_pid", $res[$i]) && !array_key_exists($res[$i]["rek_".$sek_sql_title."_pid"], $t) && ($sekData['sek_cardinality'] == 1)) {
                $t[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
              }
              if (array_key_exists("rek_".$sek_sql_title."_pid", $res[$i]) && !array_key_exists($res[$i]["rek_".$sek_sql_title."_pid"], $p) && ($sekData['sek_cardinality'] == 1)) {
                $p[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
              }

              if ($sekData['sek_lookup_function'] != "") {
                $func = $sekData['sek_lookup_function']."('".$res[$i]['rek_'.$sek_sql_title]."');";

                if (array_key_exists($func, $cache_eval)) {
                  $res[$i]["rek_".$sek_sql_title."_lookup"] = $cache_eval[$func];
                } else {
                  eval('$res[$i]["rek_'.$sek_sql_title.'_lookup"] = '.$func);
                  $cache_eval[$func] = $res[$i]["rek_".$sek_sql_title."_lookup"];
                }

                if ($sekData['sek_cardinality'] == 1) {
                    if (array_key_exists('rek_'.$sek_sql_title.'_pid', $res[$i]) && array_key_exists('rek_'.$sek_sql_title.'_lookup', $res[$i])) {
                      if (!is_array($p[$res[$i]["rek_".$sek_sql_title."_pid"]]["rek_".$sek_sql_title."_lookup"]) || !array_key_exists("rek_".$sek_sql_title."_lookup", $p[$res[$i]["rek_".$sek_sql_title."_pid"]])) {
                        $p[$res[$i]["rek_".$sek_sql_title."_pid"]]["rek_".$sek_sql_title."_lookup"] = array();
                      }
                      array_push($p[$res[$i]["rek_".$sek_sql_title."_pid"]]["rek_".$sek_sql_title."_lookup"], $res[$i]["rek_".$sek_sql_title."_lookup"]);
                    }
                } else {
                    if (array_key_exists('rek_'.$sek_sql_title.'_pid', $res[$i]) && array_key_exists('rek_'.$sek_sql_title.'_lookup', $res[$i])) {
                      $p[$res[$i]["rek_".$sek_sql_title."_pid"]]["rek_".$sek_sql_title."_lookup"] =  $res[$i]["rek_".$sek_sql_title."_lookup"];
                    }
                }
              }
              if ($sekData['sek_cardinality'] == 1) {
                $t[$res[$i]["rek_".$sek_sql_title."_pid"]][] =  $res[$i]["rek_".$sek_sql_title];
              } else {
                $t[$res[$i]["rek_".$sek_sql_title."_pid"]] =  $res[$i]["rek_".$sek_sql_title];
              }
          }
        }
        // now populate the $result variable again
        for ($i = 0; $i < count($result); $i++) {
              if (!isset($result[$i]["rek_".$sek_sql_title]) && ($sekData['sek_cardinality'] == 1)) {
                $result[$i]["rek_".$sek_sql_title] = array();
              }
              if (isset($t[$result[$i]["rek_pid"]])) {
                $result[$i]["rek_".$sek_sql_title] = $t[$result[$i]["rek_pid"]];
              }
              if ($sekData['sek_lookup_function'] != "" && isset($p[$result[$i]["rek_pid"]]["rek_".$sek_sql_title."_lookup"])) {
                $result[$i]["rek_".$sek_sql_title."_lookup"] = $p[$result[$i]["rek_pid"]]["rek_".$sek_sql_title."_lookup"];
              }
          }
      }
    }
  }

  function getSearchKeyByPIDS($sek_sql_title, $pids = array(), $shadow = false, $previousToDate = '')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (count($pids) == 0) {
      return array();
    }
    $dbtp =  APP_TABLE_PREFIX;
    $shadowSQL = '';
    if ($shadow) {
      $shadowSQL = '__shadow';
      $stmt = "SELECT
                    rek_" . $sek_sql_title ."_pid,
                    rek_" . $sek_sql_title ."
                 FROM
                    " . $dbtp . "record_search_key_" . $sek_sql_title . $shadowSQL ." s1
                 WHERE
                    s1.rek_".$sek_sql_title."_pid IN (".Misc::arrayToSQLBindStr($pids).")
                    AND s1.rek_".$sek_sql_title."_stamp = (SELECT MAX(s2.rek_".$sek_sql_title."_stamp)
                      FROM " . $dbtp . "record_search_key_" . $sek_sql_title . $shadowSQL ." s2
                      WHERE s1.rek_".$sek_sql_title."_pid = s2.rek_".$sek_sql_title."_pid)
                 ORDER BY
          rek_" . $sek_sql_title ."_id ASC ";
    } else {
      $stmt = "SELECT
                    rek_" . $sek_sql_title ."_pid,
                    rek_" . $sek_sql_title ."
                 FROM
                    " . $dbtp . "record_search_key_" . $sek_sql_title . "
                 WHERE
                    rek_".$sek_sql_title."_pid IN (".Misc::arrayToSQLBindStr($pids).")
                 ORDER BY
          rek_" . $sek_sql_title ."_id ASC ";

    }


    try {
      $res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  function getParentTitlesByPIDS(&$result)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $pids = array();
    for ($i = 0; $i < count($result); $i++) {
      if (!is_array($result[$i]["rek_ismemberof"]) && !empty($result[$i]["rek_ismemberof"])) {
        if (!in_array($result[$i]["rek_ismemberof"], $pids)) {
          $pids[] = $result[$i]["rek_ismemberof"];
        }
      }
      for ($y = 0; $y < count($result[$i]["rek_ismemberof"]); $y++) {
        if (!in_array($result[$i]["rek_ismemberof"][$y], $pids)) {
          $pids[] = $result[$i]["rek_ismemberof"][$y];
        }
      }
    }
    if (count($pids) == 0) {
      return array();
    }


    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "SELECT
          rek_pid,
                    rek_title
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid IN (".Misc::arrayToSQLBindStr($pids).")";

    try {
      $res = $db->fetchPairs($stmt, $pids);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    // now populate the $result variable again
    $sek_sql_title = "ismemberof_title";
    for ($i = 0; $i < count($result); $i++) {

      if (!is_array($result[$i]["rek_".$sek_sql_title])) {
        $result[$i]["rek_".$sek_sql_title] = array();
      }

      if (!is_array($result[$i]["rek_ismemberof"]) && !empty($result[$i]["rek_ismemberof"])) {
        if (!in_array($res[$result[$i]["rek_ismemberof"]], $result[$i]["rek_".$sek_sql_title])) {
          $result[$i]["rek_".$sek_sql_title][] = $res[$result[$i]["rek_ismemberof"]];
        }
      } else if (is_array($result[$i]["rek_ismemberof"])) {
        for ($y = 0; $y < count($result[$i]["rek_ismemberof"]); $y++) {
          $result[$i]["rek_".$sek_sql_title][] = $res[$result[$i]["rek_ismemberof"][$y]];
        }
      }
    }
    return $result;
  }

  function getAuthWorkflowsByPIDS(&$result, $usr_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    for ($i = 0; $i < count($result); $i++) {
      $pids[] = $result[$i]["rek_pid"];
    }

    if (count($pids) == 0) {
      return;
    }
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $pids = implode("', '", $pids);

    if (!Auth::isAdministrator() && (is_numeric($usr_id))) {
      $log->debug('Not an administrator');
      // TODO: OR rek_assigned_group_id IN (2,3))
      $stmt = "SELECT rek_pid, authi_pid, authi_role, wfl_id, wfl_title, wft_id, wft_icon, wft_order
                   FROM ".$dbtp."record_search_key
                   INNER JOIN ".$dbtp."auth_index2 ON rek_pid = authi_pid
                   INNER JOIN ".$dbtp."auth_rule_group_users ON authi_arg_id = argu_arg_id and argu_usr_id = ".
                   $db->quote($usr_id, 'INTEGER')."
                   LEFT JOIN ".$dbtp."record_search_key_assigned_user_id ON rek_pid = rek_assigned_user_id_pid
                   LEFT JOIN ".$dbtp."workflow_roles ON authi_role = wfr_aro_id OR (authi_role = 7 AND wfr_aro_id = 8 ".
                   "AND rek_status != 2 AND (rek_assigned_user_id IN (".$db->quote($usr_id, 'INTEGER').") ) )
                   LEFT JOIN ".$dbtp."workflow ON wfr_wfl_id = wfl_id
                   LEFT JOIN ".$dbtp."workflow_trigger ON wfl_id = wft_wfl_id
                                      AND (wft_pid = '-1' or wft_pid = authi_pid)
                                    AND (wft_xdis_id = -1 or wft_xdis_id = rek_display_type)
                                    AND (wft_ret_id = 0 or wft_ret_id = rek_object_type)
           WHERE rek_pid IN ('".$pids."') and (wft_options = 1 or wfl_id IS NULL)
                  ORDER BY wft_order ASC ";

    } else if (!is_numeric($usr_id)) { // no workflows for a non-logged in person -
                                       // but may get lister and/or viewer roles
      $log->debug('Not logged in');
      $stmt = "SELECT rek_pid, authi_pid, authi_role
                    FROM {$dbtp}record_search_key
                     INNER JOIN ".$dbtp."auth_index2 ON rek_pid = authi_pid
             INNER JOIN ".$dbtp."auth_rule_group_rules on authi_arg_id = argr_arg_id
                     INNER JOIN ".$dbtp."auth_rules ON ar_rule='public_list' AND ar_value='1' AND argr_ar_id=ar_id
               WHERE rek_pid IN ('".$pids."')";
    } else {
      $log->debug('Administrator');
      $stmt = "SELECT DISTINCT rek_pid, authi_arg_id, wfl_id, wfl_title, wft_id, wft_icon, wft_order ".
                 "FROM {$dbtp}record_search_key " .
          "LEFT JOIN ".$dbtp."auth_index2 on authi_pid = rek_pid " .
          "INNER JOIN ".$dbtp."workflow_trigger ON wft_options = 1 " .
                    "AND (wft_pid = '-1' or wft_pid = rek_pid) " .
                               "AND (wft_xdis_id = -1 or wft_xdis_id = rek_display_type) " .
                               "AND (wft_ret_id = 0 or wft_ret_id = rek_object_type) " .
                    "INNER JOIN ".$dbtp."workflow on wfl_id = wft_wfl_id " .
           "WHERE rek_pid IN ('".$pids."') " .
                    "ORDER BY wft_order ASC";
    }

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    if (count($res) == 0) {
      return;
    }

    $tmp = array();
    for ($i = 0; $i < count($res); $i++) {
      if (!array_key_exists($res[$i]["rek_pid"], $tmp)) {
          $tmp[$res[$i]["rek_pid"]] = array();
          $tmp[$res[$i]["rek_pid"]]["authi_role"] = array();
          $tmp[$res[$i]["rek_pid"]]["wfl_id"] = array();
          $tmp[$res[$i]["rek_pid"]]["wft_id"] = array();
          $tmp[$res[$i]["rek_pid"]]["wfl_title"] = array();
          $tmp[$res[$i]["rek_pid"]]["wft_icon"] = array();
      }

      if (!array_key_exists("authi_role", $res[$i])) {
        $tmp[$res[$i]["rek_pid"]]["authi_role"][] = null;
      } elseif (!in_array($res[$i]["authi_role"], $tmp[$res[$i]["rek_pid"]]["authi_role"])) {
        $tmp[$res[$i]["rek_pid"]]["authi_role"][] = $res[$i]["authi_role"];
      }
      if (!array_key_exists("wfl_id", $res[$i])) {
        $tmp[$res[$i]["rek_pid"]]["wfl_id"][] = null;
      } elseif (!in_array($res[$i]["wfl_id"], $tmp[$res[$i]["rek_pid"]]["wfl_id"])) {
        $tmp[$res[$i]["rek_pid"]]["wfl_id"][] = $res[$i]["wfl_id"];
      }
      if (!array_key_exists("wft_id", $res[$i])) {
        $tmp[$res[$i]["rek_pid"]]["wft_id"][] = null;
      } elseif (!in_array($res[$i]["wft_id"], $tmp[$res[$i]["rek_pid"]]["wft_id"])) {
          $tmp[$res[$i]["rek_pid"]]["wft_id"][] = $res[$i]["wft_id"];
      }
      if (!array_key_exists("wfl_title", $res[$i])) {
        $tmp[$res[$i]["rek_pid"]]["wfl_title"][] = null;
      } elseif (!in_array($res[$i]["wfl_title"], $tmp[$res[$i]["rek_pid"]]["wfl_title"])) {
        $tmp[$res[$i]["rek_pid"]]["wfl_title"][] = $res[$i]["wfl_title"];
      }
      if (!array_key_exists("wft_icon", $res[$i])) {
        $tmp[$res[$i]["rek_pid"]]["wft_icon"][] = null;
      } elseif (!in_array($res[$i]["wft_icon"], $tmp[$res[$i]["rek_pid"]]["wft_icon"])) {
          $tmp[$res[$i]["rek_pid"]]["wft_icon"][] = $res[$i]["wft_icon"];
      }
    }

    for ($i = 0; $i < count($result); $i++) {
      if (array_key_exists($result[$i]["rek_pid"], $tmp) && $tmp[$result[$i]["rek_pid"]]) {
        $result[$i] = array_merge($result[$i], $tmp[$result[$i]["rek_pid"]]);
      }
    }
  }



  function getChildCountByPIDS(&$result, $usr_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $pids = array();
    for ($i = 0; $i < count($result); $i++) {
      if (array_key_exists('rek_object_type', $result[$i]) && $result[$i]["rek_object_type"] != "3") {
        $pids[] = $result[$i]["rek_pid"];
      }
    }

    if (count($pids) == 0) {
      return;
    }
    $dbtp =  APP_TABLE_PREFIX;
    $authArray = Collection::getAuthIndexStmt(array("Lister"), "r1.rek_pid");
    $authStmt = $authArray['authStmt'];

    $stmt = "SELECT
                  rek_ismemberof, count(rek_ismemberof) as rek_ismemberof_count
              FROM
                  " . $dbtp . "record_search_key_ismemberof as r2 inner join
                  " . $dbtp . "record_search_key as r1 ON rek_pid = rek_ismemberof_pid and rek_status = 2
                  $authStmt
              WHERE
                  rek_ismemberof IN (".Misc::arrayToSQLBindStr($pids).")
              GROUP BY
                  rek_ismemberof ";
    try {
      $res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    for ($i = 0; $i < count($res); $i++) {
      $t[$res[$i]["rek_ismemberof"]] =  $res[$i]["rek_ismemberof_count"];
    }

    // now populate the $result variable again
    for ($i = 0; $i < count($result); $i++) {
      if (isset($t[$result[$i]["rek_pid"]])) {
        $result[$i]["rek_ismemberof_count"] = $t[$result[$i]["rek_pid"]];
      }
    }
  }

  function getOrgStaffIDsByPIDS(&$result)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $aut_ids = array();
    for ($i = 0; $i < count($result); $i++) {
      if ($result[$i]["rek_object_type"] == "3") {
        if (is_array($result[$i]["rek_author_id"])) {
          $aut_ids = array_merge($aut_ids, $result[$i]["rek_author_id"]);
        }
        if (is_array($result[$i]["rek_contributor_id"])) {
          $aut_ids = array_merge($aut_ids, $result[$i]["rek_contributor_id"]);
        }
      }
    }
    if (count($aut_ids) == 0) {
      return;
    }
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $aut_ids = implode(", ", $aut_ids);

    $stmt = "SELECT
                    aut_id, aut_org_staff_id
                 FROM
                    " . $dbtp . "author
                 WHERE
                    aut_id IN (".Misc::arrayToSQLBindStr($aut_ids).")";
    try {
      $res = $db->fetchAll($stmt, $aut_ids, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    $t = array();
    for ($i = 0; $i < count($res); $i++) {
      $t[$res[$i]["aut_id"]] =  $res[$i]["aut_org_staff_id"];
    }
    // now populate the $result variable again
    for ($i = 0; $i < count($result); $i++) {
      for ($y = 0; $y < count($result[$i]['rek_author_id']); $y++) {
        if (!is_array($result[$i]["rek_author_id_external"])) {
          $result[$i]["rek_author_id_external"] = array();
        }
        if (is_numeric($t[$result[$i]['rek_author_id'][$y]])) {
          $result[$i]["rek_author_id_external"][] = $t[$result[$i]['rek_author_id'][$y]];
        } else {
          $result[$i]["rek_author_id_external"][] = 0;
        }
      }
      for ($y = 0; $y < count($result[$i]['rek_contributor_id']); $y++) {
        if (!is_array($result[$i]["rek_contributor_id_external"])) {
          $result[$i]["rek_contributor_id_external"] = array();
        }
        if (is_numeric($t[$result[$i]['rek_contributor_id'][$y]])) {
          $result[$i]["rek_contributor_id_external"][] = $t[$result[$i]['rek_contributor_id'][$y]];
        } else {
          $result[$i]["rek_contributor_id_external"][] = 0;
        }
      }
    }
  }


  function getParentsByPIDS(&$result)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $pids = array();

    for ($i = 0; $i < count($result); $i++) {
      if (!empty($result[$i]["rek_ismemberof"])) {
        if (is_array($result[$i]["rek_ismemberof"])) {
          foreach ($result[$i]["rek_ismemberof"] as $mpid) {
            if (!in_array($mpid, $pids)) {
              $pids[$mpid] = $mpid;
            }
          }
        } else {
          $pids[$result[$i]["rek_ismemberof"]] = $result[$i]["rek_ismemberof"];
        }
      }
    }

    if (count($pids) == 0) {
      return;
    }

    $dbtp =  APP_TABLE_PREFIX;

    $authArray = Collection::getAuthIndexStmt(array("Lister"), "r1.rek_pid");
    $authStmt = $authArray['authStmt'];

    $stmt = "SELECT
               rek_pid, rek_title
             FROM
                " . $dbtp . "record_search_key as r1
                $authStmt
             WHERE
                rek_pid IN (".Misc::arrayToSQLBindStr($pids).") ";

    try {
      $res = $db->fetchAll($stmt, array_values($pids), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    $t = array();
    for ($i = 0; $i < count($res); $i++) {
      $t[$res[$i]["rek_pid"]] =  $res[$i]["rek_title"];
    }

    // now populate the $result variable again
    for ($i = 0; $i < count($result); $i++) {
      if (array_key_exists('rek_ismemberof', $result[$i])) {
          $temp  = $result[$i]["rek_ismemberof"];

          if (is_array($temp)) {
            $result[$i]["rek_ismemberof"] = array("rek_pid" =>  $temp);

            foreach ($temp as $tpid) {
              if (array_key_exists($tpid, $t)) {
                $result[$i]["rek_ismemberof"]["rek_title"][] = $t[$tpid];
              }
            }
          } else {
            $result[$i]["rek_ismemberof"] = array("rek_pid" => array($temp));
            $result[$i]["rek_ismemberof"]["rek_title"][] = $t[$temp];
          }
      }
    }
  }

  function getCitationIndex($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "SELECT
                rek_citation
             FROM
                " . $dbtp . "record_search_key
             WHERE
                rek_pid = ".$db->quote($pid);
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    if ($res == "") {
      $res = Record::getSearchKeyIndexValue($pid, "Title");
    }
    return $res;
  }

  function getSearchKeyIndexNextOrder($pid, $sek_title)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "SELECT
                   count(rek_".$sek_title.") as sek_count
                FROM
                   " . $dbtp . "record_search_key_".$sek_title."
                WHERE
                   rek_".$sek_title."_pid = ".$db->quote($pid);
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    if (is_numeric($res)) {
      $res++;
      return $res;
    } else {
      return 0;
    }
  }

  /**
   * Method returns the PID given the ISI Loc
   *
   * @param $isi_loc The ISI Loc to search on
   * @return string The found PID
   */
  public static function getPIDByIsiLoc($isi_loc)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "SELECT
                rek_isi_loc_pid
             FROM
                " . $dbtp . "record_search_key_isi_loc
             WHERE
                rek_isi_loc = ".$db->quote($isi_loc). " ORDER BY rek_isi_loc_id ASC ";

    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  /**
   * Retrieve PIDs by ISI Loc ID excluding any in the temporary duplicates collection
   * @param string $isi_loc
   * @return boolean|array
   */
  function getPIDsByIsiLoc($isi_loc)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $pids = null;

    $matches = array();


    if($isi_loc) {
      $sql = "SELECT DISTINCT rek_isi_loc_pid FROM ".$dbtp."record_search_key_isi_loc "
          ."WHERE rek_isi_loc = ? ";
      try
      {
        $stmt = $db->query($sql, array($isi_loc));
        $pids = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
        $log->err($e->getMessage());
        return false;
      }
    }

    return $pids;
  }

  /**
   * Method updates the Google Scholar citation count
   *
   * @param $pid The PID to update the citation count for
   * @param $count The count to update with
   * @param $link The link to update with
   * @return bool True if the update was successful else false
   */
  public static function updateGoogleScholarCitationCount($pid, $count, $link)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "UPDATE
                " . $dbtp . "record_search_key
             SET
               rek_gs_citation_count = ".$db->quote($count, 'INTEGER').",
               rek_gs_cited_by_link = ".$db->quote($link)."
             WHERE
                rek_pid = ".$db->quote($pid);
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    if ( APP_SOLR_INDEXER == "ON" ) {
      FulltextQueue::singleton()->add($pid);
    }
    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }


    return true;
  }


  /**
   * Method updates the Index with the Thomson citation count from the existing data
   *
   * @param $pid The PID to update the citation count for
   * @return bool True if the update was successful else false
   */

  public static function updateThomsonCitationCountIndex($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    // Get the current index count
    $stmt = "SELECT
                    rek_thomson_citation_count
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid = ?";

    try {
      $res = $db->fetchOne($stmt, array($pid));
      $index_count = $res;
    }
    catch(Exception $ex) {
      $log->err($ex);
    }

    // Get the count from the existing datastore
    $stmt = "SELECT
          tc_count
         FROM
                  " . $dbtp . "thomson_citations
               WHERE
                  tc_pid = ?";

    try {
      $res = $db->fetchOne($stmt, array($pid));
      $count = $res;
    }
    catch(Exception $ex) {
      $log->err($ex);
    }

    if (is_numeric($count) && ($count != $index_count)) {
      // If the count has changed, or there is no previous count, update the count
      $stmt = "UPDATE
                  " . $dbtp . "record_search_key
               SET
                 rek_thomson_citation_count = ?
               WHERE
                  rek_pid = ?";
      try {
        $db->query($stmt, array($count, $pid));
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }

      return true;
    } else {
      return true;
    }
  }

  /**
   * Updates the Google Scholar citation count for a record using the last
   * known count from the history table
   * @param $pid The PID to update the citation count for
   * @return bool True if the update was successful else false
   */
  public static function updateGoogleScholarCitationCountFromHistory($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $prev_count;

    // Get the previous count
    $stmt = "SELECT
                    rek_thomson_citation_count
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid = ".$db->quote($pid);
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }
    $prev_count = $res;
  }

  /**
   * Method updates the Thomson citation count
   *
   * @param $pid The PID to update the citation count for
   * @param $count The count to update with
   * @return bool True if the update was successful else false
   */
  public static function updateThomsonCitationCount($pid, $count, $isi_loc)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt = "UPDATE
                " . $dbtp . "record_search_key
             SET
               rek_thomson_citation_count = ".$db->quote($count)."
             WHERE
                  rek_pid = ".$db->quote($pid);
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    // Record in history
    Record::insertThomsonCitationCount($isi_loc, $count);

    if ( APP_SOLR_INDEXER == "ON" ) {
      FulltextQueue::singleton()->add($pid);
    }
    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }

    return true;
  }

  /**
   * Updates the Thomson citation count for a record using the last
   * known count from the history table
   * @param $pid The PID to update the citation count for
   * @return bool True if the update was successful else false
   */
  public static function updateThomsonCitationCountFromHistory($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $prev_count;

      // Get the isi_loc
      $stmt = "SELECT
                      rek_isi_loc
                   FROM
                      " . $dbtp . "record_search_key_isi_loc
                   WHERE
                      rek_isi_loc_pid = ".$db->quote($pid);
      try {
        $res = $db->fetchOne($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
      }
      $isi_loc = $res;
    if (!empty($isi_loc)) {
        // Get the previous count
        $stmt = "SELECT
                        tc_count
                     FROM
                        " . $dbtp . "thomson_citations
                     WHERE
                        tc_isi_loc = ".$db->quote($isi_loc)."
                     ORDER BY tc_id DESC";

        try {
          $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
          $log->err($ex);
        }
    }
    $prev_count = $res;

    // If there is a previous count in the history
    if (!empty($prev_count) || $prev_count === "0") {
      $stmt = "UPDATE
                  " . $dbtp . "record_search_key
               SET
                 rek_thomson_citation_count = ".$db->quote($prev_count)."
               WHERE
                  rek_pid = ".$db->quote($pid);
      try {
        $db->query($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }
    }
    return true;
  }

  /**
   * Method inserts a new Thomson citation count entry
   *
   * @param $pid The PID to insert the citation count for
   * @param $count The count to insert
   * @return bool True if the insert was successful else false
   */
  private static function insertThomsonCitationCount($isi_loc, $count)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "INSERT INTO
                    " . $dbtp . "thomson_citations
                 (tc_id, tc_count, tc_last_checked, tc_created, tc_isi_loc)
                 VALUES
                 (NULL, ".$db->quote($count).", '".time()."', '".time()."', ".
                $db->quote($isi_loc).")";

    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return true;
  }

  /**
   * Method updates the last time a Thomson citation count was checked
   *
   * @param $pid The PID to update the last checked date for
   * @param $count The count to update with
   * @return bool True if the update was successful else false
   */
  private static function updateThomsonCitationLastChecked($tc_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "UPDATE
               " . $dbtp . "thomson_citations
             SET
               tc_last_checked = '".time()."'
                 WHERE
                    tc_id = ".$db->quote($tc_id, 'INTEGER');
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }

    /**
   * Returns Thomson citation count history for a pid
   *
   * @param $pid The PID to get the citation count history for
   * @return array The citation count history
   */
  public static function getThomsonCitationCountHistory($pid, $limit = false, $order = 'ASC')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $limit = ($limit) ? 'LIMIT '.$limit:null;
    $order = ($order == 'ASC') ? 'ASC' : 'DESC';

    $stmt = "SELECT
          tc_last_checked,tc_created,tc_count
         FROM
               " . $dbtp . "thomson_citations
                INNER JOIN " . $dbtp . "record_search_key_isi_loc
                 ON " . $dbtp . "thomson_citations.tc_isi_loc = " . $dbtp . "record_search_key_isi_loc.rek_isi_loc
                 WHERE
                    rek_isi_loc_pid = ".$db->quote($pid)."
                 ORDER BY tc_created ".$order."
                 $limit";
    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }


    /**
   * Returns Scopus citation count history for a pid
   *
   * @param $pid The PID to get the citation count history for
   * @return array The citation count history
   */
  public static function getScopusCitationCountHistory($pid, $limit = false, $order = 'ASC')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $limit = ($limit) ? 'LIMIT '.$limit:null;
    $order = ($order == 'ASC') ? 'ASC' : 'DESC';

    $stmt = "SELECT
         sc_last_checked, sc_created, sc_count
         FROM
               " . $dbtp . "scopus_citations
                 INNER JOIN " . $dbtp . "record_search_key_scopus_id
                 ON " . $dbtp . "scopus_citations.sc_eid = " . $dbtp . "record_search_key_scopus_id.rek_scopus_id
                 WHERE
                    rek_scopus_id_pid = ".$db->quote($pid)."
                 ORDER BY sc_created ".$order."
                 $limit";
    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }


  /**
   * Method updates the Scopus citation count
   *
   * @param $pid The PID to update the citation count for
   * @param $count The count to update with
   * @return bool True if the update was successful else false
   */
  public static function updateScopusCitationCount($pid, $count, $eid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "UPDATE
                " . $dbtp . "record_search_key
             SET
               rek_scopus_citation_count = ".$db->quote($count)."
             WHERE
                rek_pid= ".$db->quote($pid);
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    // Record in history
    Record::insertScopusCitationCount($eid, $count);

    if ( APP_SOLR_INDEXER == "ON" ) {
      FulltextQueue::singleton()->add($pid);
    }
    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid='.$pid);
      $cache->poisonCache();
    }

    return true;
  }

  /**
   * Method inserts a new Scopus citation count entry
   *
   * @param $pid The PID to insert the citation count for
   * @param $count The count to insert
   * @return bool True if the insert was successful else false
   */
  private static function insertScopusCitationCount($eid,$count)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    $stmt = "INSERT INTO
                    " . $dbtp . "scopus_citations
                 (sc_id, sc_count, sc_last_checked, sc_created, sc_eid)
                 VALUES
                 (NULL, ".$db->quote($count).", '".time()."', '".time()."', ".
                $db->quote($eid).")";

    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

        return true;
  }

  /**
   * Updates the Scopus citation count for a record using the last
   * known count from the history table
   * @param $pid The PID to update the citation count for
   * @return bool True if the update was successful else false
   */
  public static function updateScopusCitationCountFromHistory($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

      // Get the eid
      $stmt = "SELECT
                      rek_scopus_id
                   FROM
                      " . $dbtp . "record_search_key_scopus_id
                   WHERE
                      rek_scopus_id_pid = ".$db->quote($pid);
      try {
        $res = $db->fetchOne($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
      }
      $eid = $res;
    if (!empty($eid)) {
        // Get the previous count
        $stmt = "SELECT
                        sc_count
                     FROM
                        " . $dbtp . "scopus_citations
                     WHERE
                        sc_eid = ".$db->quote($eid)."
                     ORDER BY sc_id DESC";

        try {
          $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
          $log->err($ex);
        }
    }
    $prev_count = $res;
    // If there is a previous count in the history
    if (!empty($prev_count) || $prev_count === "0") {
      $stmt = "UPDATE
                  " . $dbtp . "record_search_key
               SET
                 rek_scopus_citation_count = ".$db->quote($prev_count)."
               WHERE
                  rek_pid = ".$db->quote($pid);
      try {
        $db->query($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }
    }
    return true;
  }


    /**
     * Updates the Altmetric score for a record using the last
     * retrieved score from the history table
     *
     * @param $pid The PID to update the score for
     * @return bool True if the update was successful else false
     */
    public static function updateAltmetricScoreFromHistory($pid)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

        // Get the DOI
        $doi = false;
        $stmt = "SELECT
                      rek_doi
                   FROM
                      " . $dbtp . "record_search_key_doi
                   WHERE
                      rek_doi_pid = ".$db->quote($pid);
        try {
            $doi = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
        }
        if ($doi) {
            // Get the score using the DOI
            $stmt = "SELECT
                        as_score, as_amid
                     FROM
                        " . $dbtp . "altmetric
                     WHERE
                        as_doi = ".$db->quote($doi)."
                     ORDER BY as_id DESC";

            try {
                $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }
        }

        // If there is a previous count in the history
        if (count($res) > 0) {
            $stmt = "UPDATE
                  " . $dbtp . "record_search_key
               SET
                 rek_altmetric_score = ".$db->quote($res['as_score'], 'INTEGER').",
                 rek_altmetric_id = ".$db->quote($res['as_amid'], 'INTEGER')."
               WHERE
                  rek_pid = ".$db->quote($pid);
            try {
                $db->query($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }
        }
        return true;
    }



  /**
   * Retrieve PIDs by DOI excluding any in the temporary duplicates collection
   * @param string $doi
   * @return boolean|array
   */
  function getPIDsByDoi($doi)
  {
      $log = FezLog::get();
      $db = DB_API::get();
      $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = null;

      $sql = "SELECT DISTINCT rek_doi_pid FROM ".$dbtp."record_search_key_doi "
            . "WHERE rek_doi = ? ";

      try
      {
          $stmt = $db->query($sql, array($doi));
          $pids = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
          $log->err($e->getMessage());
          return false;
      }

      return $pids;
  }

  /**
  * Retrieve PIDs by DOI excluding any in the temporary duplicates collection
  * @param string $doi
  * @return boolean|array
  */
  function getPIDsByPubmedId($pubmedId)
  {
      $log = FezLog::get();
      $db = DB_API::get();
      $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = null;

      $sql = "SELECT DISTINCT rek_pubmed_id_pid FROM ".$dbtp."record_search_key_pubmed_id "
      . "WHERE rek_pubmed_id = ? ";

      try
      {
          $stmt = $db->query($sql, array($pubmedId));
          $pids = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
          $log->err($e->getMessage());
          return false;
      }

      return $pids;
  }

  /**
  * Retrieve PIDs by exact stripped title match
  * @param string $title
  * @return boolean|array
  */
  function getPIDsByTitle($title)
  {
      $log = FezLog::get();
      $db = DB_API::get();
      $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = null;

      $sql = "SELECT DISTINCT rek_pid FROM ".$dbtp."record_search_key "
            . "WHERE rek_title = ? ";

      try
      {
          $stmt = $db->query($sql, array($title));
          $pids = $stmt->fetchAll();
      }
      catch(Exception $e)
      {
          $log->err($e->getMessage());
          return false;
      }

      return $pids;
  }

  /**
   * Retrieve PIDs by Scopus ID excluding any in the temporary duplicates collection
   * @param string $scopusId
   * @return boolean|array
   */
  function getPIDsByScopusID($scopusId, $testTempCollPresence=false)
  {
      $log = FezLog::get();
      $db = DB_API::get();
      $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = null;

      $matches = array();
      //Pull out the digits from the ScopusID
      preg_match("/(\d{10,11})/", $scopusId, $matches);
      //If there's a valid 11 digit number tack on the prefix
      $sidFormatted = (array_key_exists(1, $matches)) ? $matches[1] : null;
      //Otherwise it's not a valid ScopusID and is set to null
      $sidFormatted = ($sidFormatted) ? "2-s2.0-".$sidFormatted : null;

      if($sidFormatted)
      {
          if($testTempCollPresence)
          {
              $sql = "SELECT DISTINCT rek_scopus_id_pid FROM "
                .$dbtp."record_search_key_scopus_id "
                ."WHERE rek_scopus_id = ? ";

          }
          else
          {
              $sql = "SELECT DISTINCT rek_scopus_id_pid FROM ".$dbtp."record_search_key_scopus_id "
                ."WHERE rek_scopus_id = ? ";
          }

          try
          {
              $stmt = $db->query($sql, array($sidFormatted));
              $pids = $stmt->fetchAll();
          }
          catch(Exception $e)
          {
              $log->err($e->getMessage());
              return false;
          }
      }

      return $pids;
  }

  function getScopusDocTypeCodeByDescription($sdt)
  {
      $log = FezLog::get();
      $db = DB_API::get();
      $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

      $sql = "SELECT sdt_code FROM " . $dbtp . "scopus_doctypes where sdt_description = ? LIMIT 1;";

      try
      {
          $stmt = $db->query($sql, array($sdt));
//           $res = $stmt->fetchAll();
          $res = $stmt->fetchColumn(0);
      }
      catch(Exception $e)
      {
          $log->err($e->getMessage());
          return false;
      }

      return $res;
  }

  //$get top when given an array returns listed titles only
  function getSearchKeyIndexValue($pid, $searchKeyTitle, $getLookup=true, $sek_details="", $getTop = null)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

    if (!is_array($sek_details)) {
      $sek_details = Search_Key::getBasicDetailsByTitle($searchKeyTitle);
    }
    $sek_title = Search_Key::makeSQLTableName($sek_details['sek_title']);
    if (empty($sek_title)) {
        $log->err("No search key found for pid ".$pid.": ".$searchKeyTitle);
        return false;
    }

    if ($sek_details['sek_relationship'] == 1) { //1-M so will return an array
      $order = ($sek_details['sek_cardinality'] == 1) ? " ORDER BY rek_".$sek_title."_order" : '';
      $log->debug('1-M will return array');
      $stmt = "SELECT
                    rek_".$sek_title."
                 FROM
                    " . $dbtp . "record_search_key_".$sek_title."
                 WHERE
                    rek_".$sek_title."_pid = ".$db->quote($pid).
                  $order;
      try {
          $res = ($sek_details['sek_cardinality'] == 1) ? $db->fetchCol($stmt) : $db->fetchOne($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }

      if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
        $temp = array();
        $temp_value = '';
        if(!empty($res)) {
          if (!is_array($res)) {
            eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$res.");");
            $temp[$res] = $temp_value;
          } else {
            foreach ($res as $rdata) {
              eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$rdata.");");
              $temp[$rdata] = $temp_value;
            }
          }
        }
        $res = $temp;
      }
    } else { //1-1 so will return single value
      $stmt = "SELECT
                    rek_".$sek_title."
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid = ".$db->quote($pid);
      try {
        $res = $db->fetchOne($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }

      if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
        $temp = array();
        eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$res.");");
        $temp[$res] = $temp_value;
        $res = $temp;
      }
    }
    if(is_array($getTop)){
        $accValues = array();
        foreach($res as $key => $value) {
            if (in_array($value, $getTop)) {
                $accValues[$key] =  $value;
            }
        }
        $res = $accValues;
    }
    return $res;
  }

//param $previousToDate will return the version previous
function getSearchKeyIndexValueShadow($pid, $searchKeyTitle, $getLookup=true, $sek_details='', $previousToDate='')
{
    $log = FezLog::get();
    $db = DB_API::get();


    if (!is_array($sek_details)) {
        $sek_details = Search_Key::getBasicDetailsByTitle($searchKeyTitle);
    }
    $sek_title = Search_Key::makeSQLTableName($sek_details['sek_title']);
    if (empty($sek_title)) {
        $log->err("No search key found for pid ".$pid.": ".$searchKeyTitle);
        return false;
    }

    if ($sek_details['sek_relationship'] == 1) { //1-M so will return an array
        $dateOfVersion = ($previousToDate) ? " AND rek_".$sek_title."_stamp <= ".$db->quote($previousToDate) : "";
        $order = ($sek_details['sek_cardinality'] == 1) ? " ORDER BY rek_".$sek_title."_order" : '';
        $log->debug('1-M will return array');
        $stmt = "SELECT
                rek_".$sek_title."_stamp
             FROM
                " . APP_TABLE_PREFIX . "record_search_key_".$sek_title."__shadow
             WHERE
                rek_".$sek_title."_pid = ".$db->quote($pid).
                $dateOfVersion . "
             ORDER BY rek_".$sek_title."_stamp DESC";
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        $stamp = $res;

        $stmt = "SELECT
                rek_".$sek_title."
             FROM
                " . APP_TABLE_PREFIX . "record_search_key_".$sek_title."__shadow
             WHERE
                rek_".$sek_title."_pid = ".$db->quote($pid)."
             AND rek_".$sek_title."_stamp = ".$db->quote($stamp).
             $order;
        try {
            $res = ($sek_details['sek_cardinality'] == 1) ? $db->fetchCol($stmt) : $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
            $temp = array();
            foreach ($res as $rkey => $rdata) {
                eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$rdata.");");
                $temp[$rdata] = $temp_value;
            }
            $res = $temp;
        }
        return $res;

    } else { //1-1 so will return single value
        $dateOfVersion = ($previousToDate) ? " AND rek_stamp <= ".$db->quote($previousToDate) : "";

        $stmt = "SELECT
                rek_".$sek_title."
             FROM
                " . APP_TABLE_PREFIX . "record_search_key__shadow
             WHERE
                rek_pid = ".$db->quote($pid).
                $dateOfVersion. "
             ORDER BY rek_stamp DESC" ;
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
            $temp = array();
            eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$res.");");
            $temp[$res] = $temp_value;
            $res = $temp;
        }
        return $res;
    }

}

  function buildSearchKeyJoins($options, $sort_by, $operator, $filter)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $searchKey_join = array();
    $searchKey_join[SK_JOIN] = ""; // initialise the return sql searchKey fields join string
    $searchKey_join[SK_LEFT_JOIN] = ""; // initialise the return sql left joins string - so count doesnt need to do it
    $searchKey_join[SK_WHERE] = ""; // initialise the return sql where string
    $searchKey_join[SK_SORT_ORDER] = ""; // initialise the return sql searchKey Order/Sort by join string
    $searchKey_join[SK_KEY_ID] = 1; // initialise the first join searchKey ID
    $searchKey_join[SK_MAX_COUNT] = 0; // initialise the max count of extra searchKey field joins
    $searchKey_join[SK_FULLTEXT_REL] = ""; // initialise the return sql term relevance matching when fulltext
                                           // indexing is used
    $searchKey_join[SK_SEARCH_TXT] = ""; // initialise the search info string
    $searchKey_join[SK_GROUP_BY] = ""; // initialise the group by string
    $searchKey_join[SK_ORDER_BY] = ""; // initialise the order by return string

    $searchKey_join['sk_where_AND'] = '';
    $searchKey_join['sk_where_OR'] = '';

    foreach ($options as $sek_id => $value) {
      if (strpos($sek_id, "searchKey") !== false) {
        $searchKeys[str_replace("searchKey", "", $sek_id)] = $value;
      } else if (strpos($sek_id, "manualFilter") !== false) {
        $searchKey_join[SK_WHERE] .= " ".$value." AND ";
      }
    }

    $joinType = "";
    $x = 0;
    $sortRestriction = "";
    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
                               // only mysql supports db prefixing, so will remove it - no reason not to

    $operatorToUse = trim($operator);

    /*
     * Fulltext SQL (Special Case)
     */
    // this will have to replaced with lots of union select joins like eventum
    if (array_key_exists('0', $searchKeys) && trim($searchKeys['0']) != '') {
      $joinType = " INNER JOIN ";
      if ( $operatorToUse == 'OR' ) {
        $joinType = " LEFT JOIN ";
      }

      $escapedInput = $searchKeys["0"];

      $searchKey_join[SK_KEY_ID] = 1;
      $searchKey_join[SK_SEARCH_TXT] .= "Title, Abstract, Keywords:\"".trim(htmlspecialchars($searchKeys["0"]))."\", ";

      if (APP_MYSQL_INNODB_FLAG == "ON" || APP_SQL_DBTYPE != "mysql") {

        $where_stmt .= " WHERE ";
        $names = explode(" ", $escapedInput);
        $nameCounter = 0;
        $searchFields = array("rek_pid", "rek_title", "rek_description");
        foreach ($searchFields as $sf) {
          foreach ($names as $name) {
            $nameCounter++;
            if ($nameCounter > 1) {
              $where_stmt .= " OR ";
            }
            // Only using % at the end of the like because %term% won't use an index so will be very slow
            $where_stmt .= " ".$sf." LIKE ".$db->quote($name.'%')." ";
          }
        }
        $kw_where_stmt .= " WHERE ";
        $nameCounter = 0;
        $sf = "rek_keywords";
        foreach ($names as $name) {
          $nameCounter++;
          if ($nameCounter > 1) {
            $kw_where_stmt .= " OR ";
          }
          // Only using % at the end of the like because %term% won't use an index so will be very slow
          $kw_where_stmt .= " ".$sf." LIKE ".$db->quote($name.'%')." ";
        }


        $searchKey_join[SK_JOIN] .= $joinType." (SELECT rek_pid, 1 AS Relevance ".
                            " FROM {$dbtp}record_search_key ".
        $where_stmt.
                            " UNION ".
                            " SELECT rek_keywords_pid AS rek_pid, 1 AS Relevance ".
                            " FROM {$dbtp}record_search_key_keywords ".
        $kw_where_stmt.")".
                            " AS search ON search.rek_pid = r1.rek_pid ";

        $searchKey_join[SK_GROUP_BY] = " GROUP BY r1.rek_pid ";
        $termRelevance = ", 1 as Relevance";
        $searchKey_join[SK_FULLTEXT_REL] = $termRelevance;

      } else {
        $searchKey_join[SK_JOIN] .= $joinType." (SELECT rek_pid, MATCH(rek_pid, rek_title, rek_description) AGAINST (".
                            $db->quote($input).") AS Relevance ".
                            " FROM {$dbtp}record_search_key ".
                            " WHERE MATCH (rek_pid, rek_title, rek_description) AGAINST (".$db->quote('*'.$input.'*').
                            " IN BOOLEAN MODE)".
                            " UNION ".
                            " SELECT rek_keywords_pid AS rek_pid, MATCH(rek_keywords) AGAINST (".
                            $db->quote($input).") AS Relevance ".
                            " FROM {$dbtp}record_search_key_keywords ".
                            " WHERE MATCH (rek_keywords) AGAINST (".$db->quote(''.$input.'*')." IN BOOLEAN MODE))".
                            " AS search ON search.rek_pid = r1.rek_pid ";

        $searchKey_join[SK_GROUP_BY] = " GROUP BY r1.rek_pid ";
        $termRelevance = ", SUM(search.Relevance) as Relevance";
        $searchKey_join[SK_FULLTEXT_REL] = $termRelevance;
      }
    }

    /*
     * For each search key build SQL if data was submitted
     */
    $tableJoinID = 1;
    foreach ($searchKeys as $sek_id => $searchValue ) {
      if (empty($sek_id)) continue;

      if (!empty($searchValue)) {

        $sekdet = Search_Key::getDetails($sek_id);

        if(empty($sekdet['sek_id']))
        continue;

        $options["sql"] = array();
        $temp_value = "";
        $joinID = '';
        $sqlColumnName = '';
        $operatorToUse = trim($operator);

        /*
         * joinID is the prefix when using a column in the SQL
         *
         * For search keys that have a many-to-many relationship we are
         * going to join the table to the search query and prefex it with
         * $x ie.JOIN table r3. So all columns in 'table' will need to use 'r3'
         *
         * 1-to-1 search keys will be in default table
         * So you default prefix - r1
         */
        if ($sekdet['sek_relationship'] == "1") {
          $joinID = ++$tableJoinID;
        } else {
          $joinID = $searchKey_join[SK_KEY_ID];
        }

        $sqlColumnName = "r{$joinID}.rek_".$sekdet['sek_title_db'];

        /*
         * Build the SQL for this particular search key
         */
        if (is_array($searchValue)) {

          if ( isset($searchValue['override_op']) ) {
            $operatorToUse = $searchValue['override_op'];
            unset($searchValue['override_op']);
          }

          // Multiple type is 'All Of' or 'Any of'
          $multiple_type = '';
          if ( @isset($searchValue['multiple_type']) ) {
            $multiple_type = $searchValue['multiple_type'];
            unset($searchValue['multiple_type']);

            /*
             * Multiple type is always submitted for multiselect controls,
             * so if it was the only thing in the array, nothing was actually
             * selected - so skip this
             */
            if ( count($searchValue) == 0 ) {
              continue;
            }
          }

          if ($sekdet['sek_data_type'] == "int") {

            if ( $multiple_type == 'all' ) {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = " .
                  implode(" AND $sqlColumnName = ", $searchValue);
            } else {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName IN (".
                  implode(",", $searchValue).")";
            }

            $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"";
            $temp_counter = 0;
            foreach ($searchValue as $temp_int) {
              if (is_numeric($temp_int) && (!empty($sekdet["sek_lookup_function"]))) {
                eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".$temp_int.");");
                if ($temp_counter != 0) {
                  $searchKey_join[SK_SEARCH_TXT] .= ",";
                }
                $searchKey_join[SK_SEARCH_TXT] .= " ".trim(htmlspecialchars($temp_value));
                $temp_counter++;
              }
            }
            $searchKey_join[SK_SEARCH_TXT] .= "\", ";

          } else if ($sekdet['sek_data_type'] == "date") {

            if (!empty($searchValue) && $searchValue['filter_enabled'] == 1) {
              $sqlDate = '';
              switch ($searchValue['filter_type']) {
                case 'greater':
                  $sqlDate = " >= ".$db->quote($searchValue['start_date'])." ";
                    break;
                case 'less':
                  $sqlDate = " <= ".$db->quote($searchValue['start_date'])." ";
                    break;
                case 'between':
                  $sqlDate = " BETWEEN ".$db->quote($searchValue['start_date'])." AND ".
                      $db->quote($searchValue['end_date']);
                    break;
              }

              $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName . $sqlDate;
              $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\" $sqlDate \", ";
            }
          } else {
            if ( $multiple_type == 'all' ) {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = '" .
                  implode("' AND $sqlColumnName = '", $searchValue) . "'";
            } else {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName IN ('".implode("','", $searchValue)."')";
            }
            $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".
                htmlspecialchars(implode("','", $searchValue))."\", ";
          }
        } else { // Array was not submitted for this search key

          if ($searchValue == "-1") { //where empty or not set
            $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName is null ";
          } elseif ($searchValue == "-2") { //this user
            $usr_id = Auth::getUserID();
            $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = $usr_id";
          } elseif ($searchValue == "-4") { //not published
            $published_id = Status::getID("Published");
            $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName != $published_id";
          } elseif ($searchValue == "-3") { //myself or un-assigned
            $usr_id = Auth::getUserID();

            $tmpSql = " (($sqlColumnName = '".$usr_id."') ";

            if ($sekdet['sek_relationship'] == 1) {
              $tmpSql .= "OR NOT EXISTS
                          (SELECT *
                           FROM {$dbtp}record_search_key_".$sekdet['sek_title_db']." AS sr
                           WHERE sr.rek_".$sekdet['sek_title_db']."_pid = r{$joinID}.rek_pid))";
            } else {
              $tmpSql .= "OR ($sqlColumnName IS NULL OR $sqlColumnName = ''))";
            }
            $searchKey_join["sk_where_$operatorToUse"][] =  $tmpSql;
          } else if ($sekdet['sek_data_type'] == "int") {

            if (is_numeric($searchValue)) {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = ".$searchValue;

              if (!empty($sekdet["sek_lookup_function"])) {
                eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".$searchValue.");");
                $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".
                    htmlspecialchars($temp_value)."\", ";
              } else {
                $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".
                    htmlspecialchars(trim($searchValue))."\", ";
              }
            }
          } else if (($sekdet['sek_data_type'] == 'text' || $sekdet['sek_data_type'] == 'varchar')
            && ($sekdet['sek_html_input'] == 'text' || $sekdet['sek_html_input'] == 'textarea')) {

            if ($sekdet['sek_title_db'] == "pid") {
              // Check if user has done a google like search by adding *
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName like ".
                  $db->quote(str_replace("*", "%", $searchValue))." ";
            } else {
              $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName like ".
                  $db->quote('%'.$searchValue.'%')." ";
            }

            $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";

          } else {
            $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = ".$db->quote($searchValue);
            $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
          }

        }

        /*
         * If this search key has a 1-To-Many relationship
         * it will have its own table, so we need to join to it
         */
        if ($sekdet['sek_relationship'] == 1) {
          $joinType = " INNER JOIN ";
          if ( $operatorToUse == 'OR' ) {
            $joinType = " LEFT JOIN ";
          }
          $searchKey_join[SK_JOIN] .= "\n$joinType {$dbtp}record_search_key_".$sekdet['sek_title_db'].
              " as r{$joinID} on r{$joinID}.rek_".$sekdet['sek_title_db']."_pid = r".
              $searchKey_join[SK_KEY_ID].".rek_pid ";
        }
      }
    }

    /*
     * Only do a sort if the query has be limited in some way,
     * otherwise it is far too slow
     */
    if (!empty($sort_by)) {
      //  && $tableJoinID != 1
      $sek_id = str_replace("searchKey", "", $sort_by);
      if (!is_numeric($sek_id)) {  // in case the text version of the search key was passed in get the ID
          $sek_id = Search_Key::getID($sek_id);
      }
      if ($sek_id != '') {
        if ($sek_id == '0' && (trim($searchKeys[0]) != "")) {
          if ($options["sort_order"] == 0) {
            $searchKey_join[SK_SORT_ORDER] .= " Relevance ASC, ";
          } else {
            $searchKey_join[SK_SORT_ORDER] .= " Relevance DESC, ";
          }
        } else {
          $sekdet = Search_Key::getDetails($sek_id);

          if ( !empty($sekdet['sek_id']) ) {
            if ($sekdet['sek_relationship'] == 1) {
              $searchKey_join[SK_LEFT_JOIN] .= " LEFT JOIN {$dbtp}record_search_key_".
                  $sekdet['sek_title_db']." as rsort on rsort.rek_".$sekdet['sek_title_db']."_pid = r".
                  $searchKey_join[SK_KEY_ID].".rek_pid ".$sortRestriction;
              $searchKey_join[SK_SORT_ORDER] .= " rsort";
            } else {
              $searchKey_join[SK_SORT_ORDER] .= "r".$searchKey_join[SK_KEY_ID];
            }

            if (array_key_exists('sort_order', $options) && $options["sort_order"] == "1") {
              $searchKey_join[SK_SORT_ORDER] .= ".rek_".$sekdet['sek_title_db']." DESC, ";
            } else {
              $searchKey_join[SK_SORT_ORDER] .= ".rek_".$sekdet['sek_title_db']." ASC, ";
            }

          }
        }
      }

    }

    /*
     * Create single sql WHERE clause string
     *
     * This is done so we can seperate the AND's and OR's
     * in the WHERE clause with brackets
     */
    if ( is_array($searchKey_join['sk_where_AND']) || is_array($searchKey_join['sk_where_OR']) ) {

      $sk_where_and = false;
      $searchKey_join[SK_WHERE] = " WHERE ";

      if ( is_array($searchKey_join['sk_where_AND']) ) {
        $searchKey_join[SK_WHERE] .= " ( " . implode(' AND ', $searchKey_join['sk_where_AND']) . " ) ";
        $sk_where_and = true;
      }

      if ( is_array($searchKey_join['sk_where_OR']) ) {
        if( $sk_where_and )
        $searchKey_join[SK_WHERE] .= " AND ";
        $searchKey_join[SK_WHERE] .= " ( " . implode(' OR ', $searchKey_join['sk_where_OR']) . " ) ";
      }
    }
    return $searchKey_join;
  }

  function buildSearchKeyFilterSolr($options, $sort_by, $operator = "AND", $doExactMatch = false)
  {
    $searchKey_join = array();
    $searchKey_join[SK_JOIN] = ""; // initialise the return sql searchKey fields join string
    $searchKey_join[SK_LEFT_JOIN] = ""; // initialise the return sql left joins string - so count doesnt need to do it
    $searchKey_join[SK_WHERE] = ""; // initialise the return sql where string
    $searchKey_join[SK_SORT_ORDER] = ""; // initialise the return sql searchKey Order/Sort by join string
    $searchKey_join[SK_KEY_ID] = 1; // initialise the first join searchKey ID
    $searchKey_join[SK_MAX_COUNT] = 0; // initialise the max count of extra searchKey field joins
    $searchKey_join[SK_FULLTEXT_REL] = ""; // initialise the return sql term relevance matching when fulltext
                                           // indexing is used
    $searchKey_join[SK_SEARCH_TXT] = ""; // initialise the search info string
    $searchKey_join[SK_GROUP_BY] = ""; // initialise the group by string
    $searchKey_join[SK_ORDER_BY] = ""; // initialise the order by return string

    $searchKey_join['sk_where_AND'] = '';
    $searchKey_join['sk_where_OR'] = '';

    $searchKeys = array();
    foreach ($options as $sek_id => $value) {
      if (strpos($sek_id, "searchKey") !== false) {
        $searchKeys[str_replace("searchKey", "", $sek_id)] = $value;
      } else if (strpos($sek_id, "manualFilter") !== false) {
        $searchKey_join[SK_WHERE] .= " ".$value." ";
      }
    }

    /*
     * Fulltext SQL (Special Case)
     */
    // this will have to replaced with lots of union select joins like eventum
    if (array_key_exists("0", $searchKeys)  && trim($searchKeys["0"]) != "") {
      $escapedInput = $searchKeys["0"];
      $searchKey_join[SK_KEY_ID] = 1;
      $searchKey_join[SK_SEARCH_TXT] .= "All Fields:\"".trim(htmlspecialchars($searchKeys["0"]))."\", ";

      $solr_titles = Search_Key::getSolrTitles();
      $solr_titles["citation"] = "citation_t";
      $solr_titles["content"] = "content";
      $solr_titles["text"] = "text";

			$solr_titles_temp = $solr_titles;
      foreach ($solr_titles_temp as $skey => $svalue) {
				if (is_numeric(strpos($svalue, "_dt"))) {
					$solr_titles[$skey."_year"] = $skey."_year_t";
				}
        if (is_numeric(strpos($svalue, "_mt"))) {
          $solr_titles[$skey."_exact"] = $skey."_mt_exact";
        }
        if (is_numeric(strpos($svalue, "_t"))) {
          $solr_titles[$skey."_exact"] = $skey."_t_exact";
        }
        if (is_numeric(strpos($svalue, "_i_lookup"))) {
          $solr_titles[$skey."_lookup"] = $skey."_i_lookup";
          $solr_titles[$skey."_lookup_exact"] = $skey."_i_lookup_exact";
        }
        if (is_numeric(strpos($svalue, "_mi_lookup"))) {
          $solr_titles[$skey."_lookup"] = $skey."_mi_lookup";
          $solr_titles[$skey."_lookup_exact"] = $skey."_mi_lookup_exact";
        }
      }
      foreach ($solr_titles as $skey => $svalue) {
        $escapedInput = str_replace($skey.":", $svalue.":", $escapedInput);
      }

        // negative look ahead and behind for search keys starting withing ! and the solr chars
        // Espace any solr chars NOT before a search key (with or without a !),
//      $pattern = '/(?!'.'!'.implode("|!", $solr_titles).'|'.
//                 implode("|!", $solr_titles).":".'|!'.
//                 implode(':\(|!', $solr_titles).':\('.'|!'.
//                 implode(':"|!', $solr_titles).':"'.'|'.
//
//                 implode(':\[|', $solr_titles).':\['.'|!'.
//                 implode(':\[|!', $solr_titles).':\['.'|'.
//
//
//                 implode(':\(|!', $solr_titles).':\("'.
//                 ')(?<!'.implode("|", $solr_titles).'|'.
//                 implode(":|", $solr_titles).":".'|'.
//                 implode(':\(|', $solr_titles).':\('.'|'.
//
//                 implode(':\[|', $solr_titles).':\['.'|\*|'.
//                 // implode(':\[|!', $solr_titles).':\['.'|'.
//
//
//
//                 implode(':"|', $solr_titles).':"'.'|'.
//                 implode(':\(|', $solr_titles).':\("'.
//                 ')(\+|-|&&|\|\||!|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)(?!\))(?!\])/';
//      $pattern = '/(?!'.'!'.implode("|!", $solr_titles).')(?<!'.implode("|", $solr_titles).')(\+|-|&&|\|\||!|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
      $replace = '\\\$1';
      // REFACTOR of the solr input cleaning so that it first escapes everything, then removes escapes for search key patterns
      // The REFACTOR makes the regex MUCH smaller so it doesn't get close to hitting the regex length link limit and show perform better too. Also easier to read!

      // Solr doesn't like unclosed brackets so if brackets don't match assume an simple search entering only part of a title etc so escape
      if (substr_count($escapedInput, '(') == substr_count($escapedInput, ')')) {
          $pattern ='/(\+|-|&&|\|\||!|\{|}|\/|\[|]|\^|"|~|\*|\?|:|\\\)(?!\))(?!\])/';
      }else {
          $pattern ='/(\+|-|&&|\(|\)|\|\||!|\{|}|\/|\[|]|\^|"|~|\*|\?|:|\\\)(?!\))(?!\])/';
      }

      //first escape everything
      $escapedInput = preg_replace($pattern, $replace, $escapedInput);

      $pattern2 = '/\\\?(!)?('.implode("|", $solr_titles).')(\\\)/';
      $replace2 = '$1$2';
      //then remove escapes in first parse
      $escapedInput2 = preg_replace($pattern2, $replace2, $escapedInput);

      $pattern3 = '/('.implode("|", $solr_titles).')(:|\*|:\[|:\()\\\/';

      $replace3 = '$1$2';
      // then remove escapes in third parse
      $escapedInput = preg_replace($pattern3, $replace3, $escapedInput2);



      //This loop checks to see if there is a opening unescaped [ and then unescapes everything in the brackets
        $posOpeningSquareBractet = strpos($escapedInput, '[');
        if ($posOpeningSquareBractet) {
            $flagInBrackets = false;
            for ($i=1; $i < strlen($escapedInput); $i++) {
                if (($escapedInput[$i] == '[') && ($escapedInput[$i-1] != '\\')) {
                    $flagInBrackets = true;
                }
                if (($flagInBrackets == true) && ($escapedInput[$i] == '\\')) {
                    $escapedInput = substr($escapedInput,0,$i).substr($escapedInput, $i+1);
                    $i--;
                }
                if ($escapedInput[$i] == ']') {
                    $flagInBrackets = false;
                }
            }
        }
      // match where there is only only value after the search key, not inside brackets or in double quotes (do that one later) to simplify this code
      $skPattern = '/('.implode("|", $solr_titles).')(?:|:\(|:)"([^"\)\(]+)"\)/';
      $lookups = array();
      preg_match_all($skPattern, $escapedInput, $lookups);
      for ($i=0, $j=count($lookups[0]); $i<$j; ++$i) {
          $sek = new Search_Key();
          $sekDetails = $sek->getDetailsBySolrName($lookups[1][$i]);
          $temp_value = "";
          if (!empty($sekDetails)) {
              if ($sekDetails['sek_data_type'] == 'int' && $sekDetails['sek_lookup_id_function'] != '') {
                  eval("\$temp_value = ".$sekDetails["sek_lookup_id_function"]."('".addslashes($lookups[2][$i])."');");
                  if (!empty($temp_value)) {
                    $escapedInput = str_replace($lookups[0][$i], $lookups[1][$i].":".$temp_value, $escapedInput);
                  }
              }
          }
      }
			if (is_numeric(strpos($escapedInput, " OR "))) {
      	$searchKey_join["sk_where_AND"][] = "(" .$escapedInput.")";
			} else {
      	$searchKey_join["sk_where_AND"][] = $escapedInput;
			}
    }

    // For each search key build SQL if data was submitted
    if (is_array($searchKeys)) {
      foreach ($searchKeys as $sek_id => $searchValue ) {

          //already dealt with search key '0' above don't need to do it here
        if ($sek_id == '0') { continue; }

        if (!empty($searchValue)) {

          $sekdet = Search_Key::getDetails($sek_id);
          $suffix = Record::getSolrSuffix($sekdet);
          if(empty($sekdet['sek_id'])) {
            continue;
          }

          // if we're looking for an exact match specifically, then substitute the mt_exact suffix instead
          if ($doExactMatch && strtolower($sekdet['sek_title']) == 'author') {
            $suffix = '_mt_exact';
          }

          $options["sql"] = array();
          $temp_value = "";
          $operatorToUse = trim($operator);

          $sqlColumnName = $sekdet['sek_title_db'];

          // Build the SQL for this particular search key
          if (is_array($searchValue)) {  // Array was submitted for this search key


            if (isset($searchValue['override_op']) ) {
              $operatorToUse = $searchValue['override_op'];
              unset($searchValue['override_op']);
            }

            // Multiple type is 'All Of' or 'Any of'
            $multiple_type = '';
            if ( @isset($searchValue['multiple_type']) ) {
              $multiple_type = $searchValue['multiple_type'];
              unset($searchValue['multiple_type']);

             // Multiple type is always submitted for multiselect controls, so if it was the only thing in the array, nothing was actually selected - so skip this
              if ( count($searchValue) == 0 ) {
                continue;
              }
            }

            if ($sekdet['sek_data_type'] == "int") {
              if ($searchValue['value'] != "any") {
                if ( $multiple_type == 'all' ) {
                  $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(" .
                      Record::escapeSolr(implode(" AND ", $searchValue)).")";
                } else {
                  $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".
                      Record::escapeSolr(implode(" OR ", $searchValue)).")";
                }

                $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"";
                $temp_counter = 0;
                foreach ($searchValue as $temp_int) {
                  if (is_numeric($temp_int) && (!empty($sekdet["sek_lookup_function"]))) {
                    eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".Record::escapeSolr($temp_int).");");
                    if ($temp_counter != 0) {
                      $searchKey_join[SK_SEARCH_TXT] .= ",";
                    }
                    $searchKey_join[SK_SEARCH_TXT] .= " ".trim(htmlspecialchars($temp_value));
                    $temp_counter++;
                  }
                }
                $searchKey_join[SK_SEARCH_TXT] .= "\", ";
              }
            } elseif ($sekdet['sek_data_type'] == "date") {
              if (!empty($searchValue) && $searchValue['filter_enabled'] == 1) {
                $sqlDate = '';
                switch ($searchValue['filter_type']) {
                  case 'greater':
                    $sqlDate = "[".
                        Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date']))." TO * ] ";
                      break;
                  case 'less':
                    $sqlDate = "[ * TO ".
                        Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date']))."] ";
                      break;
                  case 'between':
                    $sqlDate = " [".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date'])).
                        " TO ".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['end_date']))."]";
                      break;
                }
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName .$suffix. ":" .$sqlDate;
                $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\" $sqlDate \", ";
              }
            } else {
              if ( $multiple_type == 'all' ) {
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.": " .
                    implode(" AND ".$sqlColumnName."_ms:", Record::escapeSolr($searchValue)) . "";
              } else {
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".
                    implode(" OR ", Record::escapeSolr($searchValue)).")";
              }
              $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".
                  htmlspecialchars(implode("','", $searchValue))."\", ";
            }
          } else {
            // Array was not submitted for this search key
            if ($searchValue == "-1") {
              // where empty or not set
              $searchKey_join["sk_where_$operatorToUse"][] = "-".$sqlColumnName.$suffix.":[* TO *]";
            } else if ($searchValue == "-2") {
              // this user
              $usr_id = Auth::getUserID();
              $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".$usr_id;
            } else if ($searchValue == "-4") {
              // not published
              $published_id = Status::getID("Published");
              $searchKey_join["sk_where_$operatorToUse"][] = "-".$sqlColumnName.$suffix.":".$published_id;
            } else if ($searchValue == "-3") {
              // myself or un-assigned
              $usr_id = Auth::getUserID();
              $tmpSql = " ((".$sqlColumnName.$suffix.":".$usr_id.") ";
              $tmpSql .= "OR (-".$sqlColumnName.$suffix.":[* TO *]))";
              $searchKey_join["sk_where_$operatorToUse"][] =  $tmpSql;
            } else if ($sekdet['sek_data_type'] == "int") {
              if (is_numeric($searchValue)) {
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".
                    Record::escapeSolr($searchValue);

                if (!empty($sekdet["sek_lookup_function"])) {
                  eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".Record::escapeSolr($searchValue).");");
                  $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars($temp_value)."\", ";
                } else {
                  $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".
                      htmlspecialchars(trim($searchValue))."\", ";
                }
              }

            } else if (($sekdet['sek_data_type'] == 'text' || $sekdet['sek_data_type'] == 'varchar')
            && ($sekdet['sek_html_input'] == 'text' || $sekdet['sek_html_input'] == 'textarea')) {

              if ($sekdet['sek_title_db'] == "pid") {
                // Check if user has done a google like search by adding *
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".
                    Record::escapeSolr($searchValue).") ";
              } else {
                // surround the exact match with quotes specifically (quotes need to go outside escapeSolr
                // call as we don't want them escaped)
                if ($doExactMatch && strtolower($sekdet['sek_title']) == 'author') {
                  $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(\"".
                      Record::escapeSolr($searchValue)."\") ";
                } else {
                  $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".
                      Record::escapeSolr($searchValue).") ";
                }
              }

              $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";

            } else if ($sekdet['sek_html_input'] == 'combo') { //combo boxes are exact searches
                $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":\"".Record::escapeSolr($searchValue)."\"";
                $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
            } else {
              $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".Record::escapeSolr($searchValue)."";
              $searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
            }
          }
        }
      }

      if ( is_array($searchKey_join['sk_where_AND']) || is_array($searchKey_join['sk_where_OR']) ) {
        $sk_where_and = false;
        $searchKey_join[SK_WHERE] .= "  ";

        if ( is_array($searchKey_join['sk_where_AND']) ) {
          $searchKey_join[SK_WHERE] .= " (" . implode(' AND ', $searchKey_join['sk_where_AND']) . ") ";
          $sk_where_and = true;
        }

        if ( is_array($searchKey_join['sk_where_OR']) ) {
          if ( $sk_where_and ) {
            $searchKey_join[SK_WHERE] .= " AND ";
          }
          $searchKey_join[SK_WHERE] .= " (" . implode(' OR ', $searchKey_join['sk_where_OR']) . ") ";
        }
      }
    }

    // Only do a sort if the query has be limited in some way, otherwise it is far too slow
    if (!empty($sort_by)) { //  && $tableJoinID != 1

      $sek_id = str_replace("searchKey", "", $sort_by);
      if ($sek_id != '') {
        if ($sek_id == '0' && (array_key_exists(0, $searchKeys) && trim($searchKeys[0]) != "")) {
          if ($options["sort_order"] == 0) {
            $searchKey_join[SK_SORT_ORDER] .= " score asc ";
          } else {
            $searchKey_join[SK_SORT_ORDER] .= " score desc ";
          }
        } else {
          $sekdet = Search_Key::getDetails($sek_id);
          if ( !empty($sekdet['sek_id']) ) {
            $sort_suffix = Record::getSolrSuffix($sekdet, 1);
            if (array_key_exists('sort_order', $options) && $options['sort_order'] == '1') {
              $searchKey_join[SK_SORT_ORDER] .= $sekdet['sek_title_db'].$sort_suffix." desc ";
            } else {
              $searchKey_join[SK_SORT_ORDER] .= $sekdet['sek_title_db'].$sort_suffix." asc ";
            }
          }
        }
      }
    }
    return $searchKey_join;
  }


  function getSolrSuffix($sek_det, $sort=0, $facet=0)
  {
    $suffix = "";
    $sek_data_type = $sek_det['sek_data_type'];
    $sek_cardinality = $sek_det['sek_cardinality'];
    if (($sek_data_type == 'int') && ($sek_cardinality == 0)) {
      $suffix = "_i";
    } else if (($sek_data_type == 'int') && ($sek_cardinality == 1)) {
      $suffix = "_mi";
    } else if (($sek_data_type == 'varchar' || $sek_data_type == 'text') && $sek_cardinality == 0) {
      $suffix = "_t";
      if ($sort == 1) {
        $suffix .= "_s";
      } else if (($facet == 1) || ($sek_det['sek_html_input'] == "combo")) { //Combo boxes should be exact
        $suffix .= "_ft";
      }
    } else if (($sek_data_type == 'varchar' || $sek_data_type == 'text') && $sek_cardinality == 1) {
      $suffix = "_m";
      if ($sort == 1) {
        $suffix .= "s";
      } else if ($facet == 1) {
        $suffix .= "ft";
      } else {
        $suffix .= "t";
      }
    } elseif (($sek_data_type == 'date') && ($sek_cardinality == 0)) {
      $suffix = "_dt";
    } elseif (($sek_data_type == 'date') && ($sek_cardinality == 1)) {
      $suffix = "_mdt";
    }
    return $suffix;

  }

  function escapeSolr($string)
  {
    $solr_service = new Apache_Solr_Service();
    return $solr_service->escape($string);
  }


  /**
   * Find all records where the user is creator  (based on getAssigned)
   *
   * Note: if user has "create" on a collection and individual
   * records do not have a specific creator assigned, this may have
   * unexpected results.
   *
   * @access  public
   * @param string $username The username of the search is performed on
   * @return array $res2 The index details of records associated with the user
   */
  function getCreated($options, $current_page=0,$page_rows="ALL",$sort_by='', $sort_order=0)
  {
    $usr_id = Auth::getUserID();
    $options["searchKey".Search_Key::getID("Depositor")] = $usr_id;
    return Record::getListing($options, array("Lister"), $current_page, $page_rows, $sort_by);
  }


  /**
   * Publishs all objects that don't have a status ID set, really only used for
   * development testing, but left in for now
   *
   * @access  public
   * @return void
   */
  function publishAllUnsetStatusPids($sta_id=2)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
    $stmt = "SELECT ".APP_SQL_CACHE."   rek_pid FROM
        ".$dbtp."record_search_key
        WHERE rek_status is null";
    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    foreach ($res as $row) {
      $r = new RecordObject($row['rek_pid']);
      if ($r->getXmlDisplayId()) {
        echo $r->getTitle()."<br/>\n";
        $r->setStatusId($sta_id);
      }
    }
  }
  /**
   * Sets up a template for insertion into Fedora. Used in workflows.
   *
   * @access  public
   * @return  array Array of datastreamTitles, xmlObj and indexArray
   */
  function makeInsertTemplate()
  {
    $log = FezLog::get();
    $created_date = Date_API::getFedoraFormattedDateUTC();
    $updated_date = $created_date;
    $pid = '__makeInsertTemplate_PID__';
    $xdis_id = $_POST["xdis_id"];
    $display = new XSD_DisplayObject($xdis_id);
    list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
    // find the title elements for this display (!dc:title or MODS)
    $display->getXSD_HTML_Match();
//    $xsdmf_id = $display->xsd_html_match->getXSDMF_IDBySELNameXDIS_ID('!titleInfo!title', 'Title');
    $xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title('!titleInfo!title', 'Title', $xdis_id);
    $xsdmf_id_label = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title('!objectProperties!property!VALUE', 'Label', $xdis_id);
//    $xsdmf_id_label = $display->xsd_html_match->getXSDMF_IDBySELNameXDIS_ID('!objectProperties!property!VALUE', 'Label');
    $_POST['xsd_display_fields'][$xsdmf_id_label] = '__makeInsertTemplate_DCTitle__';

    $inherit_xsdmf_id = $display->xsd_html_match->getXSDMF_IDByXDIS_ID('!inherit_security');
    if ($inherit_xsdmf_id) {
      // fake the form input for inherit security
      $_POST['xsd_display_fields'][$inherit_xsdmf_id] = 'on';
    }

    if ($xsdmf_id) {
      // fake the form input for the object title
      $_POST['xsd_display_fields'][$xsdmf_id] = '__makeInsertTemplate_DCTitle__';
    }
//    $xsdmf_id = $display->xsd_html_match->getXSDMF_IDBySELNameXDIS_ID('!dc:title');
    $xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_Title('!dc:title', 'Title', $xdis_id);
    if ($xsdmf_id) {
      // fake the form input for the object title
      $_POST['xsd_display_fields'][$xsdmf_id] = '__makeInsertTemplate_DCTitle__';
    }
    $indexArray = array();
    $xmlObj = '<?xml version="1.0"?>'."\n";
    $xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
    $xmlObj .= Misc::getSchemaSubAttributes(
        $array_ptr, $xsd_top_element_name, $xdis_id, $pid
    ); // for the pid, fedora uri etc
    $xmlObj .= $xml_schema;
    $xmlObj .= ">\n";

    $xmlObj = Foxml::array_to_xml_instance(
        $array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid,
        $xdis_id, "", $indexArray, 0, $created_date, $updated_date,
        Auth::getUserID(), array(Auth::getUserID())
    );
    $xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
    // hose the index array as we'll generate it from the ingested object later
    $indexArray = array();
    $datastreamTitles = $display->getDatastreamTitles();
    return compact('datastreamTitles', 'xmlObj', 'indexArray', 'xdis_id');
  }

  /**
   * Inserts an object into Fedora using values in an array to build the Fedora XML
   *
   * @access  public
   * @package fedora
   * @param   array $array The mods datastream array
   * @param 	string $rels_parent_pid The parent pid of the object
   * @param 	string $history (OPTIONAL) The history to add to the the object's Premis Event log
   * @param 	string $times_cited (OPTIONAL) The times cited
   * @param	array $links (OPTIONAL) The links datastream array
   * @param	array $premis (OPTIONAL) The premis datastream array
   * @return  void
   * @see foxml.tpl.html
   * @uses
   *   - WosRecItem->save(). On the Fedora version of save function
   *   - MatchingRecords->add(). On the Fedora version of add function
   *
   */
  public static function insertFromArray(
      $mods, $rels_parent_pid, $version, $history = '', $times_cited = '',
      $links = array(), $premis= array()
  )
  {
    $log = FezLog::get();

    if (! @$mods['genre']) {
      $log->err('A genre is required');
      return false;
    }
    if (! @$mods['titleInfo']['title']) {
      $log->err('A title is required');
      return false;
    }

    $pid = Fedora_API::getNextPID();

    // Dublin Core
    $dc = array();
    $dc['title'] = $mods['titleInfo']['title'];

    $title = substr(htmlspecialchars($dc['title']), 0, 255);

    // FezMD
    $xdis_id = XSD_Display::getXDIS_IDByTitleVersion($mods['genre'], $version);
    if ($xdis_id == '') {
      $log->err('Failed to get xdis_id by title and version');
      return false;
    }
    $fezmd['xdis_id'] = $xdis_id;
    $fezmd['sta_id'] = Status::getID("Published");
    $fezmd['ret_id'] = Object_Type::getID('Record');
    $fezmd['created_date'] = Date_API::getFedoraFormattedDateUTC();;
    $fezmd['updated_date'] = $fezmd['created_date'];
    $fezmd['depositor'] = Auth::getUserID();
    $fezmd['wok_doc_type'] = $mods['identifier_wok_doc_type'];
    $fezmd['scopus_doc_type'] = $mods['identifier_scopus_doc_type'];

    // RELS-EXT
    $rels = array();
    $rels['parent_pid'] = $rels_parent_pid;

    $tpl = new Template_API();
    $tpl_file = 'foxml.tpl.html';
    $tpl->setTemplate($tpl_file);

    $tpl->assign("pid", $pid);
    $tpl->assign("title",	$title);
    $tpl->assign("mods", $mods);
    $tpl->assign("dc", $dc);
    $tpl->assign("fezmd",	$fezmd);
    $tpl->assign("rels", $rels);

    if (count($links) > 0) {
      $tpl->assign("links",	$links);
    }
    if (count($premis) > 0) {
      $tpl->assign("premis",	$premis);
    }

    $foxml = $tpl->getTemplateContents();
    $config = array(
              'indent'        => true,
              'input-xml'     => true,
              'output-xml'    => true,
              'wrap'          => 0
    );

    if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
      $tidy = new tidy;
      $tidy->parseString($foxml, $config, 'utf8');
      $tidy->cleanRepair();
      $foxml = $tidy;
    }

    $xml_request_data = new DOMDocument();

    if (!$xml_request_data->loadXML($foxml)) {
      $log->err(array("Could not load the smarty made XML into a DOM object, aborting Ingest!: \n".$foxml, __FILE__,__LINE__));
      return false;
    } else {
      if (APP_FEDORA_VERSION == "3") {
        $result = Fedora_API::callIngestObject($foxml, $pid);
      } else {
        $result = Fedora_API::callIngestObject($foxml);
      }

      if ($result) {
        Record::setIndexMatchingFields($pid);
        //Citation::updateCitationCache($pid, "");
        if (!empty($times_cited)) {
          Record::updateThomsonCitationCount($pid, $times_cited, $mods['identifier_isi_loc']);
        }
        if (!empty($history)) {
          History::addHistory($pid, null, "", "", true, $history);
        }
      }
    }

    return $pid;
  }

  /**
   * Inserts an object template into Fedora. Used in workflows.
   *
   * @access  public
   * @package ...
   * @param string $pid The persistant identifier of the object
   * @param int $xdis_id
   * @param string $title
   * @param array $dsarray The array of datastreams
   * @return void
   * @uses BatchImport->insert()
   */
  function insertFromTemplate($pid, $xdis_id, $title, $dsarray)
  {
    extract($dsarray);
    // find all instances of '__makeInsertTemplate_PID__' in xmlObj and replace with the correct PID
    // xmlObj is still a text representation at this stage.
    $xmlObj = str_replace('__makeInsertTemplate_PID__', $pid, $xmlObj);
    $xmlObj = str_replace('__makeInsertTemplate_DCTitle__', $title, $xmlObj);
    Record::insertXML($pid, compact('datastreamTitles', 'xmlObj', 'indexArray', 'xdis_id'), true);
  }

  /**
   * Inserts an object xml into Fedora. Used in workflows.
   *
   * @access  public
   * @package fedora
   * @param   string $pid The persistant identifier of the object
   * @param   array $dsarray The array of datastreams
   *          $dsarray array:
   *            'datastreamTitles' => Array of datastream titles used for proposed import record.
   *            'xmlObj'           => String of XML
   *            'indexArray'       => Empty Array
   *            'xdis_id'          => Int XSDisplay ID - from $_POST["xdis_id"]
   * @param   boolean $ingestObject Should we insert as a new object into fedora (false if updating an
   *                                exisitng object).
   * @return  void
   * @uses Record::insertFromTemplate()
   */
  function insertXML($pid, $dsarray, $ingestObject)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $existingDatastreams = array();  // may be overwritten by extract

    extract($dsarray);
    $params = array();

    $datastreamXMLHeaders = Misc::getDatastreamXMLHeaders($datastreamTitles, $xmlObj, $existingDatastreams);

    $datastreamXMLContent = Misc::getDatastreamXMLContent($datastreamXMLHeaders, $xmlObj);

    // it must be a multiple file upload so remove the generic one
    if (@is_array($datastreamXMLHeaders["File_Attachment0"])) {
      $datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "File_Attachment", true, true);
    }
    if (@is_array($datastreamXMLHeaders["Link0"])) { // it must be a multiple link item so remove the generic one
      $datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "Link", true, true);
    }

    if( APP_VERSION_UPLOADS_AND_LINKS == "ON" && !in_array("FezACML", $specify_list))
    $datastreamXMLHeaders = Misc::processLinkVersioning(
        $pid, $datastreamXMLHeaders, $datastreamXMLContent, $existingDatastreams
    );
    if ($ingestObject) {
      // Actually Ingest the object Into Fedora
      // We only have to do this when first creating the object, subsequent updates should just work with the
      // datastreams.
      // will have to exclude the non X control group xml and add the datastreams after the base ingestion.
      $xmlObj = Misc::removeNonXMLDatastreams($datastreamXMLHeaders, $xmlObj);
      $config = array(
                    'indent'       => true,
                    'input-xml'    => true,
                    'output-xml'   => true,
                    'wrap'         => 0
      );
      if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
        $tidy = new tidy;
        $tidy->parseString($xmlObj, $config, 'utf8');
        $tidy->cleanRepair();
        $xmlObj = "$tidy";
      }
      if (APP_FEDORA_VERSION == "3") {
        $result = Fedora_API::callIngestObject($xmlObj, $pid);
      } else {
        $result = Fedora_API::callIngestObject($xmlObj);
      }
      // Record the result of the ingest.
      // TODO: should be returned and handled by the caller.
      $this->ingested = $result;

      if (is_array($result)) {
        $log->err(array($xmlObj, __FILE__,__LINE__));
      }
    }

    $convert_check = false;

    // ingest the datastreams
    foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
      $dsIDName = $dsTitle['ID'];

      if (is_numeric(strpos($dsIDName, "."))) {
        $filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
        $dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
      }
      // Dublic core is special, it cannot be deleted
      if (($dsIDName == "DC") && (!$ingestObject)) {
        Fedora_API::callModifyDatastreamByValue(
            $pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
            $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], 'inherit'
        );
      } elseif (($dsTitle['CONTROL_GROUP'] == "X") && ($ingestObject)) {
        // no need to process, ingest took care of all "X" datastreams
        continue;
      } else {
        $datastreamID = $dsIDName;
        $new_loc = false;
        $new_locByLocalRef = false;
        $new_add = false;
        $mod_ByValue = false;
        $mod_ByRef = false;
        $mod_locByLocalRef = false;

        if ($dsTitle['CONTROL_GROUP'] == "X") {
          $new_loc = true;
          $mod_ByValue = true;
        } else if ($dsTitle['CONTROL_GROUP'] == "R" ) {
          // if its a redirect we don't need to upload the file
          $new_add = true;
          $mod_ByRef = true;
        } else if (($dsTitle['CONTROL_GROUP'] == "M")) { // control group == 'M'
          $new_locByLocalRef = true;
          $mod_locByLocalRef = true;
        }

        $purgeANDadd = false;  // used with older Fedora versions
        $add = false;
        $mod = false;
        $versionable = 'false';

        if ($dsTitle['CONTROL_GROUP'] == "X") {
          if (!Fedora_API::datastreamExists($pid, $dsIDName)) {
            // This really shouldn't happen with Fez controlled datastreams
            // because they are added with ingest.
            $versionable = $dsTitle['VERSIONABLE'];
            $add = true;
          } else {
            $versionable = $dsTitle['VERSIONABLE'];
            //						$versionable = 'inherit';
            $mod = true;
          }
        } else if ($dsTitle['CONTROL_GROUP'] == "R" ) {
          $location = trim($datastreamXMLContent[$dsKey]);
          if (empty($location)) {
            continue;
          }
          $location = str_replace("&amp;", "&", $location);

          $versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';
          if (!Fedora_API::datastreamExists($pid, $dsIDName)) {
            $add = true;
          } else if ( APP_FEDORA_VERSION >= '2.2' ) {
            $mod = true;
          } else if ( APP_VERSION_UPLOADS_AND_LINKS == "ON" ) {
            $mod = true;
          } else if ( APP_VERSION_UPLOADS_AND_LINKS != "ON" ) {
            $purgeANDadd = true;
          }
        } else if ($dsTitle['CONTROL_GROUP'] == "M") {
          $versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';

          if (is_numeric(strpos($dsIDName, chr(92)))) {
            $dsIDName = substr($dsIDName, strrpos($dsIDName, chr(92))+1);
          }
          if (is_numeric(strpos($dsTitle['LABEL'], chr(92)))) {
            $dsTitle['LABEL'] = substr($dsTitle['LABEL'], strrpos($dsTitle['LABEL'], chr(92))+1);
          }
          $datastreamID = Foxml::makeNCName($dsIDName);

          if (!Fedora_API::datastreamExists($pid, $datastreamID)) {
            $add = true;
          } else if ( APP_FEDORA_VERSION >= '2.2' ) {
            $mod = true;
          } else if ( APP_VERSION_UPLOADS_AND_LINKS == "ON" ) {
            $mod = true;
          } else {
            $purgeANDadd = true;
          }
        }

        if ( $purgeANDadd ) {
          // This is required for older versions of Fedora
          // that don't support versionable flag.
          Fedora_API::callPurgeDatastream($pid, $datastreamID);
        }

        if ( $purgeANDadd || $add ) {
          if ( $new_loc ) {
            Fedora_API::getUploadLocation(
                $pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], $dsTitle['LABEL'],
                $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP'], $versionable
            );
          } else if ( $new_locByLocalRef ) {
            Fedora_API::getUploadLocationByLocalRef(
                $pid, $datastreamID, $dsTitle['File_Location'], $dsTitle['LABEL'],
                $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP'], null, $versionable
            );
          } else if ( $new_add ) {
            Fedora_API::callAddDatastream(
                $pid, $datastreamID, $location,
                $dsTitle['LABEL'], $dsTitle['STATE'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP'],
                $versionable
            );
          } else {
            $log->err(array("Unable to add datastream.  Missing add type.", __FILE__,__LINE__));
          }
        } else if ($mod) {
          //First check
          if ( $mod_ByValue ) {
            $currentXML = Fedora_API::callGetDatastreamContents($pid, $datastreamID, true);
            if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
              $config = array(
                          'indent'         => true,
                          'output-xml'     => true,
                          'input-xml'     => true,
                          'wrap'         => '1000');

              // Tidy
              $tidy = new tidy();
              $tidy->parseString($currentXML, $config, 'utf8');
              $tidy->cleanRepair();
              $currentXML = tidy_get_output($tidy);


              $tidy = new tidy();
              $tidy->parseString($datastreamXMLContent[$dsKey], $config, 'utf8');
              $tidy->cleanRepair();
              $cleanXML = tidy_get_output($tidy);
            } else {
              $cleanXML = $datastreamXMLContent[$dsKey];
            }

            if ($currentXML == $cleanXML) {
            } else {
              Fedora_API::callModifyDatastreamByValue(
                  $pid, $datastreamID, $dsTitle['STATE'], $dsTitle['LABEL'],
                  $cleanXML, $dsTitle['MIMETYPE'], $versionable
              );
            }

          } else if ( $mod_ByRef ) {
            Fedora_API::callModifyDatastreamByReference(
                $pid, $datastreamID, $dsTitle['LABEL'],
                $location, $dsTitle['MIMETYPE'], $versionable
            );
            //  $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], $versionable);
          } else if ( $mod_locByLocalRef ) {
            Fedora_API::getUploadLocationByLocalRef(
                $pid, $datastreamID, $dsTitle['File_Location'], $dsTitle['LABEL'],
                $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP'], null, $versionable
            );
          } else {
            $log->err(array("Unable to modify datastream.  Missing mod type.", __FILE__, __LINE__));
          }
        }

        if (($dsTitle['CONTROL_GROUP'] == "M")) {
          Record::generatePresmd($pid, $dsIDName);
        }
      }
    }

    // run the workflows on the ingested datastreams.
    // we do this in a seperate loop so that all the supporting metadata streams are ready to go
    foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
      if ($dsTitle['CONTROL_GROUP'] == "M" ) {
        Workflow::processIngestTrigger($pid, Foxml::makeNCName($dsTitle['ID']), $dsTitle['MIMETYPE']);
        //clear the managed content file temporarily saved in the APP_TEMP_DIR
        $ncNameDelete = Foxml::makeNCName($dsTitle['ID']);
        if (is_file(APP_TEMP_DIR.$ncNameDelete)) {
          unlink(APP_TEMP_DIR.$ncNameDelete);
        }
      }
    }

    Record::setIndexMatchingFields($pid);
  }


  /**
   * @package fedora
   * @param string $pid
   * @param string $dsID
   * @uses AuthIndex->setIndexAuthBGP()
   *   This method is traced to "Regenerate Auth Index" workflow which is only used on Fedora system.
   */
  function checkQuickAuthFezACML($pid, $dsID)
  {
    $xmlObjNum = Record::getDatastreamQuickAuthTemplate($pid);
    if (is_numeric($xmlObjNum) && $xmlObjNum != "-1" && $xmlObjNum != -1) {
      $xmlObj = FezACML::getQuickTemplateValue($xmlObjNum);
      if ($xmlObj != false) {
        $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
        if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
          Fedora_API::callModifyDatastreamByValue(
              $pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
              $xmlObj, "text/xml", "true"
          );
        } else {
          Fedora_API::getUploadLocation(
              $pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID,
              "text/xml", "X", null, "true"
          );
        }
      }
    }
  }

  /**
   * getDatastreamQuickAuthTemplate
   * Find out if the current user can view this record
   *
   * @access  public
   * @return  integer : the quick auth template ID primary key for the fez_auth_quick_template table from the FezACML
   */
  function getDatastreamQuickAuthTemplate($pid)
  {
    $userPIDAuthGroups = Auth::getAuthorisationGroups($pid);
    return $userPIDAuthGroups['datastreamQuickAuth'];
  }

  function insertRecentRecords($pids)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    if (!is_array($pids) || count($pids) == 0) {
      return false;
    }
    foreach ($pids as $pid) {
      Record::insertRecentRecord($pid);
    }
  }

  function insertRecentRecord($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "recently_added_items" .
                " VALUES ( ? )";
    try {
      $db->query($stmt, $pid);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }


  }

  function deleteRecentRecords()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "recently_added_items";

    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }
  }


  function insertRecentDLRecord($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "recently_downloaded_items" .
                " VALUES ";
    $stmt .= "(".$db->quote($pid['stl_pid']). "," . $db->quote($pid['downloads']).")";
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }
  }


  function insertRecentDLRecords($pids)
  {
    foreach ($pids as $pid) {
      Record::insertRecentDLRecord($pid);
    }
  }

  function deleteRecentDLRecords()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "recently_downloaded_items";
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
    }
  }

  /**
   * @package fedora & fedora_bypass
   * @param string $pid
   * @param string $dsIDName
   */
  function generatePresmd($pid, $dsIDName)
  {
    //Jhove
    $ncName = Foxml::makeNCName($dsIDName);
    $presmd_check = Workflow::checkForPresMD($ncName);
    if ($presmd_check != false) {
      $presmd_id = $presmd_check;
      if (is_numeric(strpos($presmd_check, "/"))) {
        $presmd_id = substr($presmd_check, strrpos($presmd_check, "/")+1); // take out any nasty slashes from the ds name itself
      }

      if (Fedora_API::datastreamExists($pid, $presmd_check)) {
        Fedora_API::callPurgeDatastream($pid, $presmd_check);
      }
      Fedora_API::getUploadLocationByLocalRef(
          $pid, $presmd_id, $presmd_check, $presmd_check,
          "text/xml", "M"
      );
      if (is_file(APP_TEMP_DIR.basename($presmd_check))) {
        unlink(APP_TEMP_DIR.basename($presmd_check));
      }
    }
    //ExifTool
    Exiftool::saveExif($pid, $dsIDName);
  }

  /**
   * propagateExistingPremisDatastreamToFez
   *
   * This method looks up the PremisEvent datastream of the nominated Fedora object, and checks for its
   * existence. If found, and any events are marked TBG (To Be Generated), the details are written back
   * to the Fez premis_event table, so that the underlying object may be re-built from Fez.
   *
   * @access  public
   * @package fedora
   * @param   $pid    The PID of the Fedora object we are processing.
   * @uses Reindex->indexFezFedoraObjects()
   */
  function propagateExistingPremisDatastreamToFez($pid)
  {
    $datastreams = Fedora_API::callGetDatastreams($pid);
    if (empty($datastreams)) {
      return;     // No datastreams at all; let's bail out.
    }

    foreach ($datastreams as $ds_key => $ds_value) {
      if ($ds_value['ID'] == 'PremisEvent') {
        $value = Fedora_API::callGetDatastreamContents($pid, 'PremisEvent', true);
        //$value = $value['stream'];
        /* It's time for a spot of DOMage */
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadxml($value);
        $xpath = new DOMXPath($xmlDoc);
        $xpath->registerNamespace("premis", "http://www.loc.gov/standards/premis");
        $events = $xpath->query("//premis:event");

        foreach ($events as $event) {
          if ($event->firstChild->nodeValue == "[TBG]") {
            // Assemble $historyDetail
            $params = $event->getElementsByTagName("eventType");
            foreach ($params as $param) {
              $historyDetail = $param -> nodeValue;
            }
            // Assemble $historyDetailExtra
            $params = $event->getElementsByTagName("eventDetail");
            foreach ($params as $param) {
              $historyDetailExtra = $param -> nodeValue;
            }

            /* This field is auto-generated at the other end. We can't actually touch this. */
            // premis:eventDateTime
            /* The following fields will be populated by the workflow. We may as well just disregard these too */
            // premis:linkingAgentIdentifierType
            // premis:linkingAgentIdentifierValue
            // premis:linkingAgentIdentifierType
            // premis:linkingAgentIdentifierValue
            // premis:linkingObjectIdentifier

            // Invoke the function that writes the details back to the Fez database.
            History::addHistory($pid, null, "", "", true, $historyDetail, $historyDetailExtra);
          }
        }
      }
    }
    return;
  }


  function propagateCommentsDatastreamToFez($pid)
  {
    include_once(APP_INC_PATH . "class.user_comments.php");

    $datastreams = Fedora_API::callGetDatastreams($pid);
    if (empty($datastreams)) {
      return;     // No datastreams at all; let's bail out.
    }

    $usr_comments = new UserComments($pid);

    foreach ($datastreams as $ds_key => $ds_value) {
      if ($ds_value['ID'] == 'FezComments') {
        $value = Fedora_API::callGetDatastreamContents($pid, 'FezComments', true);
        //echo $value;exit;

        $xml = new SimpleXMLElement($value);
        //echo $xml->asXML();

        foreach ($xml->comment as $comment) {
          echo $comment->text . '<br />';
          //$usr_comments->addUserComment($comment->text, $comment->rating, $comment->user_id);
        }
      }
    }

    return;
  }

  function isDeleted($pid)
  {
    if (APP_FEDORA_APIA_DIRECT == "ON") {
      $fda = new Fedora_Direct_Access();
      return $fda->isDeleted($pid);
    }

    if(APP_FEDORA_BYPASS == 'ON') {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key
                 WHERE rek_pid = " . $db->quote($pid);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        if ($res == $pid) {
            return false;
        }
        $stmt = "SELECT rek_pid
                 FROM " . APP_TABLE_PREFIX . "record_search_key__shadow
                 WHERE rek_pid = " . $db->quote($pid);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return ($res == $pid);

    }  else {
        $res = Fedora_API::searchQuery('pid='.$pid, array('pid', 'state'));
    if ( empty($res) ) {
      //Due to issues with uppercase and lower case pids being treated differently in fedora, we will check again
      $res = Fedora_API::searchQuery('pid='.strtoupper($pid), array('pid', 'state'));
    }
        if ($res[0]['state'] == 'D') {
          return true;
        }
        return false;
    }
  }

  function markAsDeleted($pid, $date ='')
  {
    $shadow = false;
    if ($date == '') {
      $date = Date_API::getCurrentDateGMT(true);
    }

    if(APP_FEDORA_BYPASS != 'ON') {
        // tell fedora that the object is deleted.
	    $label = Record::getSearchKeyIndexValue($pid, "title", false);  // Get title of record. Sending a null label to callModifyObject deletes the object label in Fedora 3, which is used to display the title in the 'Undelete Fedora Objects' page
        Fedora_API::callModifyObject($pid, 'D', $label);
    } else {
      $shadow = true;
    }
    // delete it from the Fez index.
    Record::removeIndexRecord($pid, true, $shadow, $date);
  }


  /**
   * Marks a record as undeleted.
   *
   * @todo fedora_bypass version of undelete
   * @package fedora & fedora_bypass
   * @param string $pid
   * @param boolean $do_index
   */
  function markAsActive($pid, $do_index = true)
  {
    if ( APP_FEDORA_BYPASS == "ON") {
        $rec = new Fez_Record_SearchkeyShadow($pid);
        $rec->undeleteRecord();
    } else {
      // tell fedora that the object is active.
      Fedora_API::callModifyObject($pid, 'A', null);

      if ($do_index) {
        // add it to the Fez index.
        Record::setIndexMatchingFields($pid);
      }
    }
  }

  /**
   * Renames a datastream
   *
   * @param string $pid
   * @param string $oldName
   * @param string $newName
   * @param string $historyDescription
   * @return void
   **/
  public function renameDatastream($pid, $oldName, $newName, $historyDescription = '')
  {
    $log = FezLog::get();

    if(APP_FEDORA_BYPASS == 'ON')
    {
        $dsr = new DSResource(APP_DSTREE_PATH);
        $res = $dsr->rename($oldName, $newName, $pid);
        if($res)
        {
            if ($historyDescription == '')
            {
              $historyDescription = "Renamed the {$oldName} datastream to {$newName}";
            }
            History::addHistory($pid, null, "", "", true, $historyDescription, null);
        }
        else
        {
            $log->err("Could not rename {$oldName} to {$newName} in {$pid}");
        }

        return $res;
    }
    else
    {
        $ncOldName = Foxml::makeNCName($oldName);
        $ncNewName = Foxml::makeNCName($newName);

        if ($ncOldName == $ncNewName) {
          $log->info("Renaming datastreams failed because both the old name and the new name are the same");
          return;
        }

        // 1. Get the details to the old datastream
        $oldDatastream = Fedora_API::callGetDatastream($pid, $ncOldName);

        // if we have no details, ignore this rename
        if (!count($oldDatastream)) {
          $log->err(
              "Could not rename datastream '{$oldName}' to '{$newName}' in {$pid} "."because the original datastream doesn't exist"
          );
          return false;
        }

        // do actual rename
        $renameResult = self::renameDatastreamInternal($pid, $oldDatastream, $ncNewName);
        if (!$renameResult) {
          $log->err("Could not rename {$oldName} to {$newName} in {$pid} - renameDatastream");
          return;
        }


        // if we are renaming a file,
        if ($oldDatastream['controlGroup'] == 'M') {

          // then rename the presmd as well, start by generating the presmd filenames
          if (is_numeric(strrpos($oldName, '.'))) {
            $oldPresmdName = 'presmd_'.Foxml::makeNCName(substr($oldName, 0, strrpos($oldName, '.'))).'.xml';
          } else {
            $oldPresmdName = 'presmd_'.$ncOldName.'.xml';
          }

          if (is_numeric(strrpos($newName, '.'))) {
            $newPresmdName = 'presmd_'.Foxml::makeNCName(substr($newName, 0, strrpos($newName, '.'))).'.xml';
          } else {
            $newPresmdName = 'presmd_'.$ncNewName.'.xml';
          }

          if (Fedora_API::datastreamExists($pid, $oldPresmdName)) {
            $presmdDs = Fedora_API::callGetDatastream($pid, $oldPresmdName);
            self::renameDatastreamInternal($pid, $presmdDs, $newPresmdName);
          }

          // move exif values if they exist
          Exiftool::renameFile($pid, $oldName, $newName);

          // copy any of the generated datastreams that exist as part of this
          $prefixes = array('thumbnail_', 'web_', 'preview_', 'FezACML_');
          foreach ($prefixes as $prefix) {
            $oldSubDatastreamName = "{$prefix}{$ncOldName}";
            $newSubDatastreamName = "{$prefix}{$ncNewName}";
            if ($prefix != 'FezACML_') {
                $oldSubDatastreamName = substr($oldSubDatastreamName, 0, strrpos($oldSubDatastreamName, '.')) . '.jpg';
                $newSubDatastreamName = substr($newSubDatastreamName, 0, strrpos($newSubDatastreamName, '.')) . '.jpg';
            } else {
              $oldSubDatastreamName .= ".xml";
              $newSubDatastreamName .= ".xml";
            }

            if (Fedora_API::datastreamExists($pid, "{$oldSubDatastreamName}")) {
              $subDs = Fedora_API::callGetDatastream($pid, "{$oldSubDatastreamName}");
              self::renameDatastreamInternal($pid, $subDs, $newSubDatastreamName);
              Exiftool::renameFile($pid, "{$oldSubDatastreamName}", "{$newSubDatastreamName}");
            }
          }

          // change download stats
          Statistics::moveFileStats($pid, $oldName, $pid, $newName);
        }
    }

    // add history event about renaming file (from what to what, who and maybe why)

  }

  /**
   * Internal function to do actual renaming of datastream
   *
   * @param string $pid
   * @param array $ds datastream details to rename
   * @param string $newName
   *
   * @return bool was the rename successful
   **/
  protected function renameDatastreamInternal($pid, $ds, $newName)
  {
    $log = FezLog::get();
    if (!Fedora_API::datastreamExists($pid, $ds['ID'])) {
      $log->err("Could not rename datastream '{$ds['ID']}' to '{$newName}' in {$pid} because it doesn't exist - renameDatastreamInternal");
      return false;
    }

    // get the details
    $result = false;
    switch ($ds['controlGroup']) {
      case 'M':
      case 'X':
        $value = Fedora_API::callGetDatastreamContents($pid, $ds['ID'], true);
        $result = Fedora_API::getUploadLocation(
            $pid, $newName, $value, $ds['label'],
            $ds['MIMEType'], $ds['controlGroup'], null, $ds['versionable']
        );
          break;
      case 'R':
        Fedora_API::callAddDatastream(
            $pid, $newName, $ds['location'], $ds['label'], $ds['state'], $ds['MIMEType'],
            $ds['controlGroup'], $ds['versionable']
        );
        $result = Fedora_API::datastreamExists($pid, $newName, true);
          break;
    }

    if (!$result) {
      $log->err(
          "Could not rename datastream '{$ds['ID']}' to '{$newName}' ".
          "in {$pid} because we couldn't add the new datastream first ".
          print_r($ds, true)
      );
      return $result;
    }

    // we've successfully added the new datastream, so purge the previous one
    $result = Fedora_API::callPurgeDatastream($pid, $ds['ID']);

    if (!$result) {
      $log->err(
          "Could not rename datastream '{$ds['ID']}' to '{$newName}' ".
          "in {$pid} because we could not purge the old datastream"
      );
    }

    return $result;
  }

  /**
   * Update the label associated with a datastream
   *
   * @param string $pid
   * @param string $dsID
   * @param string $newLabel
   * @return void
   **/
  public function updateDatastreamLabel($pid, $dsID, $newLabel)
  {
    $currentDetails = Fedora_API::callGetDatastream($pid, $dsID);
    Fedora_API::callModifyDatastreamByReference(
        $pid, $dsID, $newLabel, $currentDetails['location'],
        $currentDetails['MIMEType'], $currentDetails['versionable']
    );
  }


  /**
   * Get the total number of published records in the repository
   *
   * @return int Number of published records
   **/
  public function getNumPublishedRecords()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
      SELECT
        COUNT(*) AS record_count
      FROM
        " . APP_TABLE_PREFIX . "record_search_key
      WHERE
        rek_status = 2
        AND rek_object_type = 3
      ";

    try {
      $res = $db->fetchCol($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }

    return $res[0];
  }



/**
 * This function is to be called for records that don't have a HERDC code assigned. This routine makes an
 * attempt to guess at what the HERDC code should be set to, based on various things including whether
 * or not the record was eligible for ERA. This function makes use of a custom table, and should not be
 * called from an instance of Fez without it.
 */
function getSpeculativeHERDCcode($pid)
{
  $status = Record::getIntERActStatus($pid); // Get the IntERAct status.

  // Get the content type (and possibly subtype) of the record in question.
  $record = New RecordObject($pid);
  $docType = $record->getDocumentType();
  $subType = Record::getSearchKeyIndexValue($pid, "Subtype");

  $herdcCode = ""; // Default

  /////////////////////////////////////////////////////////////////////
  // BUSINESS RULES
  /////////////////////////////////////////////////////////////////////
  // These rules are outlined by Heather Todd in a document entitled
  // "ERA smart matching rules (for existing pubs without HERDC codes)"
  /////////////////////////////////////////////////////////////////////
  if ($docType == "Conference Paper") {

    if ($status == "Y") {
      $herdcCode = "E1";
    } elseif ($status == "N") {
      $herdcCode = "EX";
    } elseif ($status == "X") {
      $herdcCode = "EX";
    }

  } elseif (($docType == "Journal Article" &&
              ($subType == "Review of research - research literature review (NOT book review" ||
               $subType == "Article")) ||
             $docType == "Online Journal Article") {

    if ($status == "Y") {
      $herdcCode = "C1";
    } elseif ($status == "N") {
      $herdcCode = "CX";
    } elseif ($status == "X") {
      $herdcCode = "C1";
    }

  } elseif ($docType == "Book" && $subType == "Non-fiction") {

    if ($status == "Y") {
      $herdcCode = "A1";
    } elseif ($status == "N") {
      $herdcCode = "AX";
    } elseif ($status == "X") {
      $herdcCode = "AX";
    }

  } elseif ($docType == "Book Chapter" && $subType == "Non-fiction") {

    if ($status == "Y") {
      $herdcCode = "B1";
    } elseif ($status == "N") {
      $herdcCode = "BX";
    } elseif ($status == "X") {
      $herdcCode = "BX";
    }

  } else {

    // For everything else in eSpace, published 2003 onwards, WITHOUT a HERDC code, and
    // NOT in the smart matching list of publications that were compared to ERA data:

    // If Document Type = Journal Article and Subtype NOT (Article or Review of Research) then Confirmed CX
    if ($docType == "Journal Article" &&
        $subType != "Article" &&
        $subType != "Review of research - research literature review (NOT book review") {

          $herdcCode = "CX";

    // If Document Type = Book and Subtype NOT non-Fiction then Confirmed AX
    } elseif ($docType == "Book" &&
              $subType != "Non-fiction") {

          $herdcCode = "AX";

    // If Document Type = Book Chapter and Subtype NOT non-Fiction then Confirmed BX
    } elseif ($docType == "Book Chapter" &&
              $subType != "Non-fiction") {

          $herdcCode = "BX";

    }
  }

  // This return value will eventually be extended into an array capable of housing
  // additional information such as whether the code is confirmed or provisional.
  return $herdcCode;
}



    /**
     * Find out what the ERA status of a given PID was in IntERAct. Warning: This function makes
     * use of a custom table - do not call it unless you have the table in your installation.
     */
    function getIntERActStatus($pid)
    {
      $log = FezLog::get();
      $db = DB_API::get();

      $stmt = "
              SELECT status
              FROM __temp_lk_interact_status
              WHERE pid = " . $db->quote($pid) . ";";
      try {
        $res = $db->fetchOne($stmt);
      } catch(Exception $ex) {
        $log->err($ex);
        return false;
      }

      if (!$res) {
        return null;
      }
      return $res;
    }
}

