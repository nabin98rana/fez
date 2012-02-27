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

include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.user.php");

class Masquerade
{
	/**
	* Method used to check whether a user is allowed to masquerade.
	*
	* @access  public
	* @param   string $username The user's username
	* @return  boolean TRUE if can masquerade, otherwise FALSE
	*/
	function canUserMasquerade($username)
	{
		$canMasquerade = false;
		
		$masqueradeGroupID = Group::getID(APP_WHEEL_GROUP);
		$userGroups = Group::getGroupColList(User::getUserIDByUsername($username));
		if (count($userGroups) > 0) {
			foreach ($userGroups as $ug) {
				if ($ug == $masqueradeGroupID) {
					$canMasquerade = true;
				}
			}
		}
		
		return $canMasquerade;
	}
	
	
	
	/**
	* Method used to update the session with information about the user who
	* enacted a masquerade.
	*
	* @access  public
	* @param   string $username The masquerading user's username
	*/
	function setMasquerader(&$session, $username)
	{
		$session['masquerader'] = $username;
		
		return;
	}
	
	
	
	/**
	* Method used to find out who the masquerading user is, if there is one.
	*
	* @access  public
	* @return  string Username of masquerading user
	*/
	function getMasquerader($session)
	{
		return $session['masquerader'];
	}

}
