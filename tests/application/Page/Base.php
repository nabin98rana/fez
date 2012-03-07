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
 * Base class for Fez Page Object 
 * 
 * @version 1.0, 2012-03-05
 * @package Tests
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Page_Base
{
    /**
     * Default time out for wait* methods
     */
    const DEFAULT_TIMEOUT = 30000;

    /**
     * @var PHPUnit_Extensions_SeleniumTestCase
     */
    protected $_selenium;

    /**
     * @var string Title of test page
     */
    protected $_page_title;

    /**
     * @var string URL of test page 
     */
    protected $_page_url;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Confirms that we are on the right page by verifying the page title.
     */
    public function verifyPageByTitle()
    {
        // Open requested page, if we are not there already.
        if ($this->_selenium->getTitle() != $this->_page_title) {
            $this->_selenium->open($this->_page_url);
        }
    }
}