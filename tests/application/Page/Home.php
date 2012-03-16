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
 * Serves as an interface of the Homepage for our Selenium test cases, 
 * provides interactions within the page. 
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Page_Home extends Page_Base
{
    /**
     * Class constructor.
     * Ensures the homepage is loaded on the browser.
     * 
     * @param PHPUnit_Extensions_SeleniumTestCase $selenium 
     */
    public function __construct($selenium)
    {
        $this->_selenium = $selenium;
        $this->_page_title = "Home - " . APP_NAME;
        $this->_page_url = "/";
        
        $this->verifyPageByTitle();
    }

    
    /**
     * Click on the Login link on the Homepage.
     * @return Page_Login 
     */
    public function clickLogin()
    {
        $this->_selenium->click("css=a.login-btn");
        $this->_selenium->waitForPageToLoad(self::DEFAULT_TIMEOUT);
        return new Page_Login($this->_selenium);
    }
    
    /**
     * Click on the News section on Homepage.
     * @todo: Find/create a DOM identifier to verify this test
     */
    public function clickNews()
    {
        $this->_selenium->click("link=News");
        // $this->_selenium->waitForElementPresent("");
    }

    /**
     * Click on Recently Added link on the homepage.
     * @todo: Find/create a DOM identifier to verify this test
     */
    public function clickRecentlyAdded()
    {
        $this->_selenium->click("link=Recently Added");
        // atm we don't have any identifier to test the content of News
        // $this->_selenium->waitForElementPresent("");
    }

    
    /**
     * Click on Recently Popular link on the homepage.
     */
    public function clickRecentlyPopular()
    {
        $this->_selenium->click("link=Recently Popular");
        $this->_selenium->waitForTextPresent("Most Downloaded in the Last Week:");
    }
    
    
    /**
     * Click on Tags link on the homepage.
     * @todo: Find/create a DOM identifier to verify this test
     */
    public function clickTags()
    {
        $this->_selenium->click("link=Tags");
        // atm we don't have any identifier to test the content of News
        // $this->_selenium->waitForElementPresent("");
    }

}