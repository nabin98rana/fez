<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');
require_once(__DIR__ . '/class.datastream.php');

/**
 * Class that contains utilities for Flint.
 *
 */
class Flint
{
    function returnFlintLanguages() {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT B.*, COUNT(rek_subject) AS record_count FROM " . APP_TABLE_PREFIX . "controlled_vocab_relationship
            LEFT JOIN " . APP_TABLE_PREFIX . "controlled_vocab AS A  ON cvo_id = cvr_parent_cvo_id
            LEFT JOIN " . APP_TABLE_PREFIX . "controlled_vocab AS B ON B.cvo_id = cvr_child_cvo_id
            LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_subject ON rek_subject = cvr_child_cvo_id
            LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_subject_pid = rek_pid AND rek_status = '2'
            WHERE A.cvo_title = 'AIATSIS codes' GROUP BY B.cvo_id";

        try {
            $res = $db->fetchAll($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    function returnInterviewees() {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT rek_contributor, COUNT(rek_contributor) AS interviewee_count FROM " . APP_TABLE_PREFIX . "record_search_key_contributor
        LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof ON rek_ismemberof_pid = rek_contributor_pid
        WHERE rek_ismemberof IN ('".APP_FLINT_COLLECTION."')
        GROUP BY rek_contributor";

        try {
            $res = $db->fetchAll($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }
}
