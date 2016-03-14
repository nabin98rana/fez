<?php

if ($_SERVER['APPLICATION_ENV'] !== 'staging') {
  echo 'Not in staging..';
  exit;
}

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");

$log = FezLog::get();
$db = DB_API::get();
$path = '/var/app/current/tmp';

if (file_exists($path)) {
  $log->err('Staging import failed: A tmp directory already exists');
  exit;
}
mkdir($path);

if (! system("aws s3 cp s3://uql/fez/fezstaging.tar.gz ${path}/fezstaging.tar.gz")) {
  $log->err('Staging import failed: Unable to copy Fez staging DB from S3');
  exit;
}

system("cd ${path} && tar xzvf ${path}/fezstaging.tar.gz --no-same-owner --strip-components 1");

$files = glob($path . "/*.sql");
foreach ($files as $sql) {
  $sql = file_get_contents($sql);
  $db->query($sql);
}

$files = glob($path . "/*.txt");
foreach ($files as $txt) {
  $db->query("LOAD DATA INFILE ? INTO TABLE users FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '0x0d0a'",
    array($i));
}
if (! system("cd ${path}/.. && rm -Rf tmp")) {
  $log->err('Staging import failed: Unable to remove extracted files');
  exit;
}
