<?php

include_once("../config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.cloud_tag.php");

$tags = Cloud_Tag::getTags();

if( count($tags) > 0 )
{
    Cloud_Tag::deleteSavedTags();
    Cloud_Tag::saveTags($tags);
}

?>