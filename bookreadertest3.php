<?php

require_once('include/class.bookreaderimplementation.php');

$govGaz = 'pidimages/UQ_7001/Queensland_Gov_Gazette_1863.pdf';

$bri = new bookReaderImplementation($govGaz);
$numpages = $bri->countPages();

function avgImgs()
{
    global $govGaz, $numpages;
    $files = array_filter(scandir($govGaz), function($element){return !in_array($element, array('.','..'));});
    $accum = 0;
    foreach($files as $file)
    {
        $accum += filesize($govGaz . '/' . $file);
    }
    $avg = $accum / $numpages;
    return $avg;
}

echo avgImgs() /1000;