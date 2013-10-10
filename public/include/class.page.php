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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to About / Contact pages.
 *
 * @version 1.0
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */

require_once(APP_INC_PATH . "class.misc.php");

class Page
{
	function getAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				pge_id AS id,
				pge_title AS title
			FROM
				" . APP_TABLE_PREFIX . "pages
			ORDER BY
				pge_title ASC
			;
		";

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}



	function getPage($pageID)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
			SELECT
				pge_id AS id,
				pge_title AS title,
				pge_content AS content
			FROM
				" . APP_TABLE_PREFIX . "pages
			WHERE
				pge_id = " . $db->quote($pageID) . "
			;
			";

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
    if (empty($res)) {
      $res = array('content' => '');
    }
		return $res;
	}



	function updatePage()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$content = $_POST["content"];
		$config = array('indent' => true,
						'output-xhtml' => true,
						'doctype' => omit,
						'show-body-only' => true,
						'wrap' => 0
						);

		$tidy = new tidy;
		$tidy->parseString($content, $config, 'utf8');
		$tidy->cleanRepair();
		$content = $tidy;

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "pages
                 SET
                    pge_title = " . $db->quote($_POST["title"]) . ",
                    pge_content = " . $db->quote($content) . "
                 WHERE
                    pge_id=" . $db->quote($_POST["id"]);
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
}
