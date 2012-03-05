<?php

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

/**
 * This class handles Selenium test cases 
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Framework_FunctionalTests 
{

    /**
     * Adds Selenium test cases into PHPUnit Framework test suite.
     * @return PHPUnit_Framework_TestSuite 
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez Framework Functional');
        $suite->addTestSuite('Functional_HomepageTest');

        return $suite;
    }

    /**
     * @todo Set up database for tests.
     * Db schema for tests should be a duplicate of application's db schema, 
     * while db records are something like 'permanent' records 
     */
    protected function setUp()
    {
//        Database schema setup for tests
//        $db = Zend_Registry::get('db');
//        $createSql = file_get_contents(FILE_DB_CREATE);
//        $insertSql = file_get_contents(FILE_DB_INSERT);
//        $db->query($createSql);
//        $db->query($insertSql);
    }

    protected function tearDown()
    {
        
    }

}
