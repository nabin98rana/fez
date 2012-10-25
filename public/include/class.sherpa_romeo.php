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

    /**
     * Method used to find Sherpa Romeo details
     *
     * @access  public
     * @param   string $issn
     * @return  boolean
     */
    function getJournalColourFromIssn($issn)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $regexp = '/[0-9]{4}-[0-9]{3}[0-9X]/';
        preg_match_all($regexp, $issn, $matches);

        //Some issns are stored
        $issn = $matches[0][0];
        $stmt = "SELECT srm_issn AS issn, srm_colour AS colour FROM " . APP_TABLE_PREFIX . "sherpa_romeo WHERE srm_issn = ".$db->quote($issn);
        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    /**
     * Method used to find Sherpa Romeo details
     *
     * @access  public
     * @param   string $journalName
     * @return  boolean
     */
    function getJournalColourFromName($journalName)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT srm_issn AS issn, srm_colour AS colour FROM " . APP_TABLE_PREFIX . "sherpa_romeo WHERE srm_journal_name = ".$db->quote($journalName);
        try {
            $res = $db->fetchRow($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    function getJournalColourFromNameComment($journalName)
    {
        return SherpaRomeo::convertSherpaRomeoToLink(SherpaRomeo::getJournalColourFromName($journalName));
    }

    function getJournalColourFromIssnComment($issn)
    {
        return SherpaRomeo::convertSherpaRomeoToLink(SherpaRomeo::getJournalColourFromIssn($issn));
    }

    function convertSherpaRomeoToLink($res)
    {
        if (array_key_exists(colour, $res)) {

            if ($res['colour']=='green'){
                $text = "Can archive pre-print and post-print or publisher's version/PDF";
                $colour = '#CCEBD6';
            } elseif (colour=='blue') {
                $text = "Can archive post-print (ie final draft post-refereeing) or publisher's version/PDF";
                $colour = '#D6EBFF';
            } elseif ($res['colour']=='yellow') {
                $text = "Can archive pre-print (ie pre-refereeing)";
                $colour = '#FFFFCC';
            } elseif ($res['colour']=='white') {
                $text = "Archiving not formally supported";
                $colour = '#FCFCFC';
            } elseif ($res['colour']=='grey') {
                $text = "RoMEO ungraded journal, but more information available";
                $colour = '#CACACA';
            }

            $sROutput = "<span style='background-color:".$colour."' id='sherpa'><a href='http://www.sherpa.ac.uk/romeo/search.php?issn=".$res['issn']."'>";
            $sROutput .= $text;
            $sROutput .= "</a></span>";
        }
        return $sROutput;
    }

    function saveXMLData($xml, $issn = NULL)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);
        $outcome = $xmlDoc->getElementsByTagName("outcome")->item(0)->nodeValue;
        if (empty($issn)) {
            $issn = $xmlDoc->getElementsByTagName("issn")->item(0)->nodeValue;
        }
        if ($outcome != 'notFound') {
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
        $stmt = "SELECT srm_xml FROM " . APP_TABLE_PREFIX . "sherpa_romeo where srm_issn =".$db->quote($issn);
        try {
            $res = $db->fetchOne($stmt);
        } catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;
    }

    function getJournalColourFromPid($pid)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        /* $stmt = "SELECT srm_colour as colour, jni_issn as issn
                FROM " . APP_TABLE_PREFIX . "matched_journals
                INNER JOIN " . APP_TABLE_PREFIX . "journal ON mtj_jnl_id = jnl_id
                INNER JOIN " . APP_TABLE_PREFIX . "journal_issns ON jni_jnl_id =  jnl_id
                INNER JOIN " . APP_TABLE_PREFIX . "sherpa_romeo ON srm_issn = jni_issn
			    WHERE mtj_pid = ". $db->quote($pid)." ORDER BY jni_issn_order"; */
        $stmt = "SELECT colour, issn FROM (
                    SELECT srm_colour AS colour, jni_issn AS issn FROM fez_record_search_key AS t2
                    INNER JOIN " . APP_TABLE_PREFIX . "matched_journals ON rek_pid = mtj_pid
                    INNER JOIN " . APP_TABLE_PREFIX . "journal_issns ON mtj_jnl_id = jni_jnl_id
                    INNER JOIN " . APP_TABLE_PREFIX . "sherpa_romeo ON srm_issn = jni_issn
                    WHERE mtj_pid = ". $db->quote($pid)."
                    UNION
                    SELECT srm_colour AS colour, rek_issn AS issn FROM " . APP_TABLE_PREFIX . "record_search_key AS t1
                    INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_issn ON rek_pid = rek_issn_pid
                    INNER JOIN " . APP_TABLE_PREFIX . "sherpa_romeo ON rek_issn = srm_issn
                    WHERE rek_pid = ". $db->quote($pid).") AS t3";

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
    /* This function will get all ISSN from the pids and fill the Sherpa Romeo table with data.
     * You will need a free API key to do more that 500 per day.
     * $overwrite : Boolean wheather to overwrite data or not
     */
    function getDataFromSherpaRomeo($reloadAll=false) {
        $log = FezLog::get();
        $db = DB_API::get();
        $regexp = '/[0-9]{4}-[0-9]{3}[0-9X]/';
        if ($reloadAll) {
            $stmt = "SELECT DISTINCT rek_issn FROM " . APP_TABLE_PREFIX . "record_search_key_issn";
        } else {
            $stmt = "SELECT DISTINCT rek_issn FROM " . APP_TABLE_PREFIX . "record_search_key_issn
            LEFT JOIN " . APP_TABLE_PREFIX . "journal_issns
            ON rek_issn = jni_issn WHERE jni_issn IS NULL";
        }
        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        $sr = new SherpaRomeo();

        foreach ($res as $journal) {
            preg_match_all($regexp, $journal['rek_issn'], $matches);
            foreach ($matches[0] as $match) {
                if ($sr::loadXMLData($match)) {
                    continue;
                }
                $xml = $sr::getXMLFromSherpaRomeo($match,'issn');
                $sr::saveXMLData($xml, $match);
            }
        }
    }
}