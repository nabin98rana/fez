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
    function getXMLFromSherpaRomeo($journalName)
    {
        $log = FezLog::get();
        if (!empty($journalName)) {
            $uRL = SHERPA_ROMEO_URL.'?jtitle='.urlencode($journalName).'&ak='.SHERPA_ROMEO_API;
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

    function saveXMLData($xml)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);
        $journal_name = $xmlDoc->getElementsByTagName("jtitle")->item(0)->nodeValue;
        $issn = $xmlDoc->getElementsByTagName("issn")->item(0)->nodeValue;
        $colour = $xmlDoc->getElementsByTagName("romeocolour")->item(0)->nodeValue;


        return true;
    }
}