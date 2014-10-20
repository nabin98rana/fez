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

class Refereed
{
    //This function will set the Refereed Source to the value given, if it is a more important indicator than the one
    //currently set. It will also set refereed to true if not already unless $setRefereed = false
    function saveIfHigher($pid, $refereedSource, $history = '', $setRefereed = true) {
        $db = DB_API::get();
        $log = FezLog::get();
        $controlVocabId = Controlled_Vocab::getID($refereedSource, 'Refereed Source');
        if (!empty($controlVocabId)) {
            $record = new RecordObject($pid);
            if ($setRefereed && !Refereed::isRefereed($pid)) {
                $record->addSearchKeyValueList(array("Refereed"), array('on'), true, $history);
            }
            $currentRefereedSource = Refereed::getRefereedSource($pid);
            if (!$currentRefereedSource || Refereed::isHigher($refereedSource, $currentRefereedSource)) {
                $record->addSearchKeyValueList(array("Refereed Source"), array($controlVocabId), true, $history);
            }
        } else {
            $log->err('Message: No Control Vocab ID for '.$refereedSource.' on Pid '.$pid.', File: '.__FILE__.', Line: '.__LINE__);
        }
    }

    function save($pid, $refereedSource, $history = '', $setRefereed = false) {
        $db = DB_API::get();
        $log = FezLog::get();
        $controlVocabId = Controlled_Vocab::getID($refereedSource, 'Refereed Source');
        if (!empty($controlVocabId)) {
            $record = new RecordObject($pid);
            if ($setRefereed && !Refereed::isRefereed($pid)) {
                $record->addSearchKeyValueList(array("Refereed"), array('on'), true, $history);
            }
            $record->addSearchKeyValueList(array("Refereed Source"), array($controlVocabId), true, $history);
        } else {
            $log->err('Message: No Control Vocab ID for '.$refereedSource.' on Pid '.$pid.', File: '.__FILE__.', Line: '.__LINE__);
        }
    }

    function isRefereed($pid) {
        $db = DB_API::get();
        $log = FezLog::get();
        $stmt = "SELECT rek_refereed FROM " . APP_TABLE_PREFIX . "record_search_key_refereed WHERE rek_refereed_pid = " . $db->quote($pid);

        try {
            $result = $db->fetchOne($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return array();
        }

        return !empty($result);
    }

    //Return text value
    function getRefereedSource($pid) {
        $refereedSourceCV = Refereed::getRefereedSourceCV($pid);
        return Controlled_Vocab::getTitle($refereedSourceCV);
    }

    function getRefereedSourceCV($pid) {
        $db = DB_API::get();
        $log = FezLog::get();
        $stmt = "SELECT rek_refereed_source FROM " . APP_TABLE_PREFIX . "record_search_key_refereed_source WHERE rek_refereed_source_pid = " . $db->quote($pid);

        try {
            $result = $db->fetchOne($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $result;
    }

    function isHigher($A, $B) {

        //Lower the number the more important the source is
        $controlVocabList = array(
            "Ulrichs" => 1,
            "Thomson Reuters" => 2,
            "ERA Journal List 2012" => 3,
            "ERA Journal List 2015" => 4,
            "ERA Journal List 2010" => 5,
            "Other" => 6,
            "Not peer peviewed" => 7,
            "Not yet assessed" => 8
        );
        return $controlVocabList[$A] < $controlVocabList[$B];
    }

}