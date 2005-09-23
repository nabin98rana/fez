<?php

// thumbnail auto workflow script

// we are running inside the WorkflowStatus::run function

$pid = $this->pid;
$dsTitle = $this->dsTitle;
$dsIDName = $dsTitle['ID'];
$thumbnail = makeThumbnail($dsIDName);
if ($thumbnail) {
    Fedora_API::getUploadLocationByLocalRef($pid, $thumbnail, $thumbnail, $thumbnail, $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
    if (is_numeric(strpos($thumbnail, "/"))) {
        $thumbnail = substr($thumbnail, strrpos($thumbnail, "/")+1); // take out any nasty slashes from the ds name itself
    }
    $thumbnail = str_replace(" ", "_", $thumbnail);
    Record::insertIndexMatchingField($pid, 122, "varchar", $thumbnail); // add the thumbnail to the fez index
}

function makeThumbnail($filename) {
    $filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1)));
    $getString = APP_RELATIVE_URL."webservices/wfb.thumbnail.php?image="
        .urlencode($filename)."&height=50&width=50&ext=jpg";
    $http_req = new HTTP_Request($getString, array("http" => "1.0"));
    $http_req->setMethod("GET");
    $http_req->sendRequest();
    $xml = $http_req->getResponseBody();
    if (is_numeric(strpos($filename, "/"))) {
        return APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", 
                substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".jpg";
    } else {
        return APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".jpg";
    }
}




?>
