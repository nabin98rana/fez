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
// |          Aaron Brown <a.brown@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.sherpa_romeo.php");
include_once(APP_INC_PATH . "class.ulrichs.php");

$query = $_GET['query'];
$xsdmf_id = $_GET['xsd_display_fields'];

$sek_comment_function = Search_Key::getCommentFunctionByXSDMF_ID($xsdmf_id);

$comment = false;

if (strlen($query) > 2 && preg_match('/[0-9]{4}-[0-9]{3}[0-9X]/', $query)) {
    eval('$comment = '.$sek_comment_function.'("'.$query.'");');
}

//This is a hack for adding in Ulrichs data on top of Sherpa Romeo
$ulrichsLink = '';
if ($sek_comment_function = "SherpaRomeo::getJournalColourFromISSNComment") {
    $return = Ulrichs::getEmbarboStatusInfo($xsdmf_id,$query);
    if (!empty($return['title_id'])) {
        $ulrichsLink = " or <a href='http://ezproxy.library.uq.edu.au/login?url=http://ulrichsweb.serialssolutions.com/title/" . $return['title_id'] . "' target='_blank'>Ulrichs information</a>";
    }
}

$return['comment'] = $comment.$ulrichsLink;


echo json_encode($return);
