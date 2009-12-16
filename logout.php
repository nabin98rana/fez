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
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.configuration.php");
include_once(APP_INC_PATH . "class.auth.php");

// IMPORTANT! everytime you destroy a cookie and you are using save_session_handler (database storage for sessions for instance) then you need to reset the save_session_hanlder
// see the unresolved php bug for details http://bugs.php.net/bug.php?id=32330
foreach($_SESSION as $k => $v) {
	unset($_SESSION[$k]);
}
if (isset($_COOKIE['_saml_idp'])) {
	setcookie(session_name(), '', time()-42000, '/');
}
foreach($_COOKIE as $k => $v) {
	if (is_numeric(strpos($k, "_shibsession_"))) {
		setcookie($k, '', time()-42000, '/');
	}
}


Zend_Session::destroy();

Auth::redirect(APP_RELATIVE_URL . "index.php?err=6");
