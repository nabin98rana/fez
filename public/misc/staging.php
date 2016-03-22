<?php

if ($_SERVER['APPLICATION_ENV'] !== 'staging') {
  echo 'Not in staging..';
  exit;
}
set_time_limit(0);
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.aws.php");

$log = FezLog::get();
$db = DB_API::get();
$path = '/var/app/current/tmp';
$aws = new AWS();

if (file_exists($path)) {
  $log->err('Staging import failed: A tmp directory already exists');
  exit;
}
mkdir($path);

if (! exec("AWS_ACCESS_KEY_ID=" .
  AWS_KEY. " AWS_SECRET_ACCESS_KEY=" .
  AWS_SECRET .
  " bash -c aws s3 cp s3://uql-fez-staging/fezstaging.tar.gz ${path}/fezstaging.tar.gz")
) {
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
chdir($path);
foreach ($files as $txt) {
  $db->query(
    "LOAD DATA LOCAL INFILE '" . basename($txt) . "' INTO TABLE " . basename($txt, '.txt') .
    " FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n'"
  );
}
if (! system("cd ${path}/.. && rm -Rf tmp")) {
  $log->err('Staging import failed: Unable to remove extracted files');
  exit;
}
