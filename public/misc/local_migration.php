<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
if (php_sapi_name()!=="cli") {
  exit;
}


include_once(APP_INC_PATH . "/../upgrade/fedoraBypassMigration/MigrateFromFedoraToDatabase.php");
$migrate = new MigrateFromFedoraToDatabase();

$migrate->fixRekUpdatedDate();
$migrate->addPidsSecurity();
