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
 * This is a base class for Fez Selenium Test cases, extends PHPUnit Selenium extension class.
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Functional_Base extends PHPUnit_Extensions_SeleniumTestCase 
{
    /**
     * Default time out for wait* methods
     */
    const DEFAULT_TIMEOUT = 30000;

    /**
     * @var array browsers Type of browsers used on the test
     */
    public static $browsers = array(
        array(
            'browser' => '*firefox'
        )
    );

    /**
     * @var bool Indication on whether the test will be altering the database. 
     */
    protected $_destructive;

    /**
     * Class constructor
     * @param string $name The name of a test case 
     * @param array $data Data set of a test case 
     * @param string $dataName 
     * @param array $browser Type of browsers uses on test case
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '', array $browser = array())
    {
        parent::__construct($name, $data, $dataName, $browser);

        $this->_destructive = TEST_DESTRUCTIVE;
    }


    /**
     * Hopefully closes down all the browser windows successfully
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * setUp method for test case. 
     * Set up the browser, browser URL and timeout for a test case,
     */
    protected function setUp()
    {
        $this->setBrowser(self::$browsers[0]["browser"]);

        $this->setBrowserUrl(TEST_URL);

//        $this->setTimeout(self::DEFAULT_TIMEOUT);
    }

//  /**
//   * Logs in a user who belongs to the specified role
//   *
//   * @param string $role The name of the role the user should belong to
//   */
//  protected function login($role)
//  {
//    $this->open("/");
//    $max = (time() + 86400);
//    $this->createCookie(
//        "{$this->_config->testing->cookie_name}=$role",
//        "path=/,max_age=$max,domain={$this->_config->testing->cookie_domain}"
//    );
//  }
}
