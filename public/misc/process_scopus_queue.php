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

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.scopus_service.php");
include_once(APP_INC_PATH . "class.scopus_queue.php");
include_once(APP_INC_PATH . "class.scopus_record.php");

/**
 * Get a list of UQ Scopus IDs owned by UQ
 * from the last 30 days and push them into
 * the queue table.
 */
$q = ScopusQueue::get();
$service = new ScopusService(APP_SCOPUS_API_KEY);

$xml = $service->getNextRecordSet();

while($service->getRecSetStart())
{
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $records = $doc->getElementsByTagName('identifier');

    foreach($records as $record)
    {
        $scopusId = $record->nodeValue;
        $matches = array();
        preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
        $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;

        $q->add($scopusIdExtracted);
    }

    $q->commit();
    $xml = $service->getNextRecordSet();
}
