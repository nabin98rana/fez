<?php

class SherpaRomeo
{
    /**
    * Method used to
    *
    * @access
    * @param
    * @return
    */
    function getXMLFromSherpaRomeo($searchTerm, $searchParam = 'jtitle')
    {
        $log = FezLog::get();
        if (!empty($searchTerm)) {
            $uRL = SHERPA_ROMEO_URL.'?'.$searchParam.'='.urlencode($searchTerm).'&ak='.SHERPA_ROMEO_API;
            $ch = curl_init($uRL);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $log->err("Sherpa Romeo timeout or error");
                return false;
            } else {
                curl_close($ch);
                return $response;
            }
        }
    return false;
    }



    function saveXMLData($xml, $issn = NULL)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);
        $outcome = $xmlDoc->getElementsByTagName("outcome")->item(0)->nodeValue;
        $issnReturned = $xmlDoc->getElementsByTagName("issn")->item(0)->nodeValue;
        if ( ($outcome != 'notFound') && (empty($issn) || $issnReturned == $issn) ) {
            $issn = $issnReturned;
            $journal_name = $xmlDoc->getElementsByTagName("jtitle")->item(0)->nodeValue;
            $colour = $xmlDoc->getElementsByTagName("romeocolour")->item(0)->nodeValue;
        } else {
            $journal_name = 'Not found in Sherpa Romeo';
            $colour = 'Not found in Sherpa Romeo';
        }
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "sherpa_romeo
                 (
                    srm_journal_name,
                    srm_xml,
                    srm_colour,
                    srm_issn,
                    srm_date_updated
                 ) VALUES (
                    " . $db->quote($journal_name) . ",
                    " . $db->quote($xml) . ",
                    " . $db->quote($colour) . ",
                    " . $db->quote($issn) . ",
                        now() )
                 ON DUPLICATE KEY UPDATE
                 srm_journal_name = " . $db->quote($journal_name) . ",
                 srm_xml = " . $db->quote($xml) . ",
                 srm_colour = " . $db->quote($colour) . ",
                 srm_date_updated = now() ";
        try {
            $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return true;
    }

    function loadXMLData($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT srm_xml FROM fez_sherpa_romeo where srm_issn =".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        } catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    function getJournalColour($pid)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT srm_colour as colour, jni_issn as issn
                FROM fez_matched_journals
                INNER JOIN fez_journal ON mtj_jnl_id = jnl_id
                INNER JOIN fez_journal_issns ON jni_jnl_id =  jnl_id
                INNER JOIN fez_sherpa_romeo ON srm_issn = jni_issn
			    WHERE mtj_pid = ". $db->quote($pid)." ORDER BY jni_issn_order";
        try {
            $res = $db->fetchRow($stmt);
        } catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if ($res['colour'] == 'Not found in Sherpa Romeo')
            return false;
        return $res;

    }
}