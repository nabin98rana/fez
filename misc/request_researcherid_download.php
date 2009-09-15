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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once("../config.inc.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.researcherid.php");

// A-4834-2009 - Annette Dobson
// A-4835-2009 - Wayne Hall
// A-4836-2009 - Alan Lopez
// A-4837-2009 - Jackob Najman
// A-4838-2009 - Neville Owen
// A-4841-2009 - Gail Williams
// A-4839-2009 - Richard Taylor
// A-4840-2009 - Harvey Whiteford
// A-3541-2009 - Amberyn Thomas 

$researcher_ids = array('A-3541-2009');
//$researcher_ids = array('A-4834-2009', 'A-4835-2009', 'A-4836-2009', 'A-4837-2009', 'A-4838-2009', 'A-4841-2009', 'A-4839-2009', 'A-4840-2009');
//$employee_ids = array('0042414', '0019904', '0030530', '0038034', '0008872', '0032765', '0009029', '0052278', '0020332');
//$researchers_id_type = 'employeeIDs';
//$researcher_id_type = 'employeeID';
$researchers_id_type = 'researcherIDs';
$researcher_id_type = 'researcherID';

ResearcherID::downloadRequest($researcher_ids, $researchers_id_type, $researcher_id_type);

?>