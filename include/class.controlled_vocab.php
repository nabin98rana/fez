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
 * Class to handle controlled vocabularies.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


class Controlled_Vocab
{

    /**
     * Method used to remove a given list of controlled vocabularies.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }


    /**
     * Method used to add a new controlled vocabulary to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 (
                    cvo_title
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["cvo_title"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			// get last db entered id
			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
			Controlled_Vocab::associateParent($HTTP_POST_VARS["parent_id"], $new_id);

			return 1;
        }
    }


    /**
     * Method used to add a new controlled vocabulary to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function associateParent($parent_id, $child_id)
    {
        global $HTTP_POST_VARS;
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship
                 (
                    cvr_parent_cvo_id,
                    cvr_child_cvo_id					
                 ) VALUES (
                    " .$parent_id. ",
                    " .$child_id. "					
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }
    /**
     * Method used to update details of a controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($cvo_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 SET 
                    cvo_title = '" . Misc::escapeString($HTTP_POST_VARS["cvo_title"]) . "'
                 WHERE cvo_id = $cvo_id";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }


    /**
     * Method used to get the title of a specific controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  string The title of the controlled vocabulary
     */
    function getTitle($cvo_id)
    {
        $stmt = "SELECT
                    cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=$cvo_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
			     WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)
                 ORDER BY
                    cvo_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getList($parent_id=false)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = ".$parent_id." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
                return $res;
            }
        }
    }

    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getAssocListFullDisplay($parent_id=false, $indent="")
    {
        $stmt = "SELECT
                    cvo_id,
					concat('".$indent."',cvo_title) as cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = ".$parent_id." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";

        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
				$newArray = array();
				$tempArray = array();
				if ($parent_id != false) {
					$indent .= "---------";
				}
				foreach ($res as $key => $data) {
					if ($parent_id != false) {
						$newArray[$key] = $data;

					}
					$tempArray = Controlled_Vocab::getAssocListFullDisplay($key, $indent);					
					if (count($tempArray) > 0) {
						if ($parent_id == false) {
							$newArray['data'][$key] = Misc::array_merge_preserve($newArray[$key], $tempArray);
							$newArray['title'][$key] = $data;
						} else {
							$newArray = Misc::array_merge_preserve($newArray, $tempArray);
						}
					}
				}
				$res = $newArray;
                return $res;
            }
        }
    }

    /**
     * Method used to get the details of a specific controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  array The controlled vocabulary details
     */
    function getDetails($cvo_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=$cvo_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Controlled Vocabulary Class');
}
?>
