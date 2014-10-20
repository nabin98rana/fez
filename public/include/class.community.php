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
 * Class to handle the business logic related to the administration
 * of communities in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");

class Community
{
	/**
	 * Method used to get the default community XDIS_ID
	 *
	 * Developer Note: Need to make this able to be set in Administrative interface and stored in the Fez database.
	 *
	 * @access  public
	 * @return  integer $community_xdis_id The XSD Display ID of a Fez community
	 */
	function getCommunityXDIS_ID()
	{
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$community_xdis_id = 11;
		return $community_xdis_id;
	}


	/**
	 * Method used to get the list of communities available in the
	 * system.
	 *
	 * @access  public
	 * @param   integer $current_row The point in the returned results to start from.
	 * @param   integer $max The maximum number of records to return
	 * @return  array The list of communities
	 */
	function getList($current_row = 0, $max = 25, $sort_by="Title")
	{
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 1; // communities only
		$list = Record::getListing($options, array("Lister"), 0, 1000, "Title");

		return $list;

	}

	/**
	 * Method used to get the list of communities available in the
	 * system.
	 *
	 * @access  public
	 * @param   integer $current_row The point in the returned results to start from.
	 * @param   integer $max The maximum number of records to return
	 * @return  array The list of communities
	 */
	function getCreatorList($current_row = 0, $max = 25, $sort_by="Title")
	{
		$roles = explode(',',APP_CREATOR_ROLES);
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 1; // communities only
		$list = Record::getListing($options, $roles, $current_row, $max, "Title", true);
		return $list;
	}

	function getCreatorListAssoc($current_row = 0, $max = 25, $sort_by="Title")
	{
		$roles = explode(',',APP_CREATOR_ROLES);
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 1; // communities only
		$list = Record::getListing($options, $roles, 0, 1000, "Title", true);
		$list = $list['list'];
		$returnList = array();
		foreach ($list as $element) {
			$returnList[$element['rek_pid']] = $element['rek_title'];
		}
		return $returnList;
	}

    /**
     * Method used to get an associative array of community ID and title
     * of all communities available in the system.
     *
     * @access  public
     * @return  array The list of collections
     */
	function getAssocList()
	{
		$options = array();
		$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$options["searchKey".Search_Key::getID("Object Type")] = 1; // communities only
		$list = Record::getListing($options, array("Lister"), 0, 1000, "Title", true);
		$list = $list['list'];
		$returnList = array();
		foreach ($list as $element) {
			$returnList[$element['rek_pid']] = $element['rek_title'];
		}
		return $returnList;
	}

}
