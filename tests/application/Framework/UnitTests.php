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
 * This class handles PHPUnit Unit test suites
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License x
 * @copyright (c) 2012 The University of Queensland
 */
class Framework_UnitTests 
{

    /**
     * Adds PHPUnit test cases into PHPUnit Framework test suite.
     * 
     * @todo adds PHPUnit test cases as the classes are created
     * @return PHPUnit_Framework_TestSuite 
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Fez Framework Unit');
        // @todo: Setup Unit tests, if any
         $suite->addTestSuite('Unit_WorkflowStatusTest');
         if (APP_RECORD_LOCKING != 'OFF') {
            $suite->addTestSuite('Unit_RecordLockTests');
         }
         $suite->addTestSuite('Unit_DuplicatesReportTests');
         $suite->addTestSuite('Unit_ResearcherIdTests');
//         $suite->addTestSuite('Unit_FezTest');
//         $suite->addTestSuite('Unit_FezTest');

        return $suite;
    }

    protected function setUp()
    {
        
    }

    protected function tearDown()
    {
        
    }

}