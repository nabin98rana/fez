<?php

include_once(APP_INC_PATH . "class.org_structure.php");


class AuthorAffiliations
{
	function getList($pid)
	{
		$stmt = "SELECT * FROM ". APP_TABLE_PREFIX ."author_affiliation 
			WHERE af_pid = '".$pid."' ";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        // Add text versions of the author and school
        foreach ($res as $key => $item) {
        	$res[$key]['author_name'] = Author::getFullname($item['af_author_id']);
        	$res[$key]['school_name'] = Org_Structure::getTitle($item['af_org_id']);
        }
        return $res;
	}

    /**
     * Method used to remove an author affiliation from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove($af_id)
    {
		if (!is_numeric($af_id)) {
			return false;
		}
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author_affiliation
                 WHERE
                    af_id = ".$af_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }

	
	function save($af_id, $pid, $af_author_id, $af_percent_affiliation, $af_org_id)
	{
		if (!is_numeric($af_author_id) || !is_numeric($af_percent_affiliation)) {
			return -1;
		}
		if (($af_percent_affiliation > 100) || ($af_percent_affiliation < 1)) {
			return -1;
		}
		if (empty($af_id)) {
			$stmt = "INSERT ";
		} else {
			$stmt = "UPDATE ";
		}
		$stmt .= APP_TABLE_PREFIX . "author_affiliation SET 
			af_pid='".$pid."',
			af_author_id=".$af_author_id.",
			af_percent_affiliation=".$af_percent_affiliation.",
			af_org_id=".$af_org_id."
		";
		if (!empty($af_id)) {
			$stmt .= "WHERE af_id=".$af_id."";
		}
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
		
        return $GLOBALS["db_api"]->dbh->getLastInsertId(APP_TABLE_PREFIX . "author_affiliation", 'af_id');
	}
}

?>