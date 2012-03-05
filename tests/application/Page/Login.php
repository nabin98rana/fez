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
 * Serves as an interface of the Login page, provides interactions within the page 
 * as required by Selenium test cases. 
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Page_Login extends Page_Base 
{

    /**
     * Class constructor.
     * Ensures Login page is loaded on the browser.
     * 
     * @param PHPUnit_Extensions_SeleniumTestCase $selenium 
     */
    public function __construct($selenium)
    {
        $this->_selenium = $selenium;
        $this->_page_title = "Login - " . APP_NAME;
        
        // Open login page, if we are not there already.
        if ($this->_selenium->getTitle() != $this->_page_title) {
            $this->_selenium->open("/login.php");
        }
    }

    /**
     * Verify the existence of Login Form
     */
    public function verifyLoginForm()
    {
        $this->_selenium->verifyElementPresent("id=login_form");
    }
}
