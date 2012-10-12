#!/usr/bin/php
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
 * This script calls Fedora bypass migration in stages.
 * It can be called via command line -- see the first line before php tag.
 *
 * @version 1.0, 2012-03-08
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 *
 * @example Command line. To run this script on command line, type in the following:
 * 1. $ sudo su OR sudo bash
 *    (This depends on the Doc Root and PHP CLI permission on your server)
 * 2. $ cd DOCUMENT_ROOT/upgrade/fedoraBypassMigration
 * 3. $ ./migrate.php -h -- config=/var/www/migration.beacon.library.uq.edu.au/public/config.inc.php autoMapXSDFields=1
 */

$configFile = "../../config.inc.php";
ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

foreach ($argv as $arg){
    $arg = explode("=", $arg);
    if (!array_key_exists(0, $arg) || !array_key_exists(1, $arg)){
        continue;
    }

    switch ($arg[0]){
        case 'config':
            $configFile = $arg[1];
            break;
    }
}

if (empty($configFile)){
    echo "Forgotten to specify config file?";
    exit;
}

include_once($configFile);

include_once("MigrateFromFedoraToDatabase.php");


// Run migration functionalities
// Do it! and cross your fingers.
// Don't forget to thank your parents (see: rm_fedora.php).
$migrate = new MigrateFromFedoraToDatabase($argv);



