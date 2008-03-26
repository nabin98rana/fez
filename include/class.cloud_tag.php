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

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.error_handler.php");

class Cloud_Tag
{

    function getTags()
    {
           $stmt = "SELECT tag, quantity 
                    FROM (
                        SELECT rek_keywords AS tag, COUNT(rek_keywords) AS quantity
                        FROM " . APP_TABLE_PREFIX . "record_search_key_keywords kw
                        GROUP BY rek_keywords
                        ORDER BY quantity DESC
                        LIMIT 0, 30
                    ) as t1 ORDER BY tag ASC";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        }

        foreach ($res as $key => $row) {			
              $tags[$row['tag']] = $row['quantity'];
		}
		
		return $tags;
    }
    
    function getCachedTags()
    {
        $stmt = "SELECT keyword, quantity 
                 FROM " . APP_TABLE_PREFIX . "cloud_tag
                 ORDER BY keyword ASC";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        }
        
        if( count($res) == 0 )
            return "";
        
        foreach ($res as $key => $row) {			
              $tags[$row['keyword']] = $row['quantity'];
		}
		
		return $tags;
    }
    
    /**
     * Method used to assemble the HTML cloud tag construct.
     *
     * @access  public
     * @param   none
     * @return  string The HTML of the cloud tag
     */
    function buildCloudTag() {

        // Based on tutorial found at http://prism-perfect.net/archive/php-tag-cloud-tutorial/
        
		$tags = Cloud_Tag::getCachedTags();
		
		// If nothing is in the cache, build the tags
		if( !$tags )
		{
		    $tags = Cloud_Tag::getTags();
		}

        $max_size = 250; // Max font size in %
        $min_size = 100; // Min font size in %

		if (!is_array($tags)) {
			return "";
		}

        // Get the largest and smallest array values
        $max_qty = max(array_values($tags));
        $min_qty = min(array_values($tags));

        // Find the range of values
        $spread = $max_qty - $min_qty;
        if (0 == $spread) {
            $spread = 1;
        }

        // Determine the font-size increment. This is the increase per tag quantity (times used).
        $step = ($max_size - $min_size)/($spread);

        $cloudTag = '<div id="cloud-tag">';
        // Loop through tag array
        foreach ($tags as $key => $value) {
            $size = $min_size + (($value - $min_qty) * $step);
            $size = ceil($size);     // uncomment for sizes in whole %:
            $cloudTag .= '<a href="' . APP_BASE_URL . 'list/cat=quick_filter&amp;search_keys%5B0%5D=' . urlencode(htmlspecialchars($key, ENT_QUOTES)) . '" style="font-size: ' . $size . '%" ';
            if ($value == 1) {
                $cloudTag .= 'title="' . $value . ' record tagged with ' . htmlspecialchars($key, ENT_QUOTES) . '">' . htmlspecialchars($key, ENT_QUOTES) . '</a> ';
            } else {
                $cloudTag .= 'title="' . $value . ' records tagged with ' . htmlspecialchars($key, ENT_QUOTES) . '">' . htmlspecialchars($key, ENT_QUOTES) . '</a> ';
            }
			$cloudTag .= " &nbsp; ";
        }
        $cloudTag .= '</div>';

        return $cloudTag;

    }
    
    
    function saveTags($tags)
    {
        foreach ( $tags as $keyword => $quantity )
        {
            $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "cloud_tag " . 
                    "(keyword, quantity) VALUES ('$keyword', $quantity)";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
        }
    }
    
    function deleteSavedTags()
    {
        $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "cloud_tag";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
    }

}



// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Cloud Tag Class');
}

?>
