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
 * Class to manage all tasks related to the DB abstraction module. This is only
 * useful to mantain a data dictionary of the current database schema tables.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

class DB_API
{

    /**
     * Connects to the database and creates a data dictionary array to be used
     * on database related schema dynamic lookups.
     *
     * @access public
     */
    function DB_API()
    {
    	
    }
    
	/**
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public static function get($index = 'db') 
    {
		if (!class_exists("Zend_Registry")) {
			die("Zend could not be found. Either Zend is incorrectly installed, or you need to refactor your Fez config file.");
		}
    	return Zend_Registry::get($index);
    }
}
?>