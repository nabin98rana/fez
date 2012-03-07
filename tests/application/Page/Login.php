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
     * @var string username used on this test.
     * By default we are using a test Admin login credentials.
     */
    protected $_username = TEST_ADMIN_USER;
    /**
     * @var string password used on this test. 
     * By default we are using a test Admin login credentials.
     */
    protected $_password = TEST_ADMIN_PASSWORD;
    
    /**
     * @var string Login button element 
     */
    protected $_login_button   = "css=.login-btn";
    
    /**
     * @var string Log Out button element 
     */
    protected $_logout_button  = "css=.logout-btn";
    
    /**
     * @var string Username Input element
     */
    protected $_username_input = "css=input#username";
    
    /**
     * @var string Password Input element
     */
    protected $_password_input = "css=input#passwd";
    
    /**
     * @var string Submit login form element 
     */
    protected $_submit_login   = "css=form#login_form input[name='Submit']";
    
    /**
     * @var string Wrapper element for the logged in message  
     */
    protected $_loggedin_element = "css=.logged-in-msg";
    
    /**
     * @var string Logged in message 
     */
    protected $_loggedin_text = "You are logged in as";
    
    
    /**
     * Class constructor.
     * Verifies mandatory elements that a Login page should have.
     * 
     * @param PHPUnit_Extensions_SeleniumTestCase $selenium 
     */
    public function __construct($selenium)
    {
        $this->_selenium = $selenium;
        $this->_page_title = "Login - " . APP_NAME;
        $this->_page_url = "/login.php";
        
        $this->verifyPageByTitle();
        $this->verifyLoginForm();
    }

    /**
     * Verify the existence of Login Form
     */
    public function verifyLoginForm()
    {
        // Is there any username, password & login button on a Login Form?
        $this->_selenium->verifyElementPresent($this->_username_input);
        $this->_selenium->verifyElementPresent($this->_password_input);
        $this->_selenium->verifyElementPresent($this->_submit_login);
    }
    
    
    /**
     * Verify login functionality for all type of users.
     * Make sure valid username/password are available.
     * We are verifying the login text, as the page loaded after user login can be vary
     * Verifying the page loaded after user login can be done for user-specific login test.
     * 
     * @param string $username
     * @param string $password
     * @return boolean False when there is no username & password
     */
    public function verifyLogin($username="", $password="")
    {
        $this->_login($username, $password);
        $this->_selenium->waitForText($this->_loggedin_element, $this->_loggedin_text);
    }

    
    /**
     * Login
     * @param string $username
     * @param string $password
     * @return boolean 
     */
    protected function _login($username = "", $password = "")
    {
        // Assign username & password
        if (!empty($username)){
            $this->_username = $username;
        }
        if (!empty($password)){
            $this->_password = $password;
        }
        
        // get lost without username/password
        if (empty($this->_username) && empty($this->_password)){
            return false;
        }
        
        $this->_selenium->type($this->_username_input, $this->_username);
        $this->_selenium->type($this->_password_input, $this->_password);
        $this->_selenium->clickAndWait($this->_submit_login);
        
        return true;
    }
    
    
    /**
     * Verifies log out functionality
     */
    public function verifyLogout()
    {
        $this->_login();
        $this->_selenium->waitForElementPresent($this->_logout_button);
        $this->_selenium->clickAndWait($this->_logout_button);
        $this->_selenium->waitForElementPresent($this->_login_button);
    }
    
}
