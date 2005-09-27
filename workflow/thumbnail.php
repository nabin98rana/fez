<?php

// thumbnail auto workflow script

// we are running inside the WorkflowStatus::run function

$pid = $this->pid;
$dsInfo = $this->dsInfo;
$dsIDName = $dsInfo['ID'];
$filename=$dsIDName;
$filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1)));
$getString = APP_RELATIVE_URL."webservices/wfb.thumbnail.php?image="
.urlencode($filename)."&height=50&width=50&ext=jpg";
$http_req = new HTTP_Request($getString, array("http" => "1.0"));
$http_req->setMethod("GET");
$http_req->sendRequest();
$xml = $http_req->getResponseBody();
if (is_numeric(strpos($filename, "/"))) {
    $thumbnail = APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", 
            substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".jpg";
} else {
    $thumbnail = APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".jpg";
}

if ($thumbnail) {
    Fedora_API::getUploadLocationByLocalRef($pid, $thumbnail, $thumbnail, $thumbnail, 'image/jpeg', 'M');
    if (is_numeric(strpos($thumbnail, "/"))) {
        $thumbnail = substr($thumbnail, strrpos($thumbnail, "/")+1); // take out any nasty slashes from the ds name itself
    }
    $thumbnail = str_replace(" ", "_", $thumbnail);
    Record::insertIndexMatchingField($pid, 122, "varchar", $thumbnail); // add the thumbnail to the fez index
}




?>
