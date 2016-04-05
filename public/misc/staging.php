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
$path = '/tmp/staging';
$aws = new AWS();

system("rm -Rf ${path}");
mkdir($path);
chdir($path);

if (! system("AWS_ACCESS_KEY_ID=" .
  AWS_KEY. " AWS_SECRET_ACCESS_KEY=" .
  AWS_SECRET .
  " bash -c \"aws s3 cp s3://uql-fez-staging/fezstaging.tar.gz ${path}/fezstaging.tar.gz\"")
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

$dsn = "mysql:host=".APP_SQL_DBHOST.";dbname=".APP_SQL_DBNAME;
$con = new PDO($dsn, APP_SQL_DBUSER, APP_SQL_DBPASS,
    array(
        PDO::MYSQL_ATTR_LOCAL_INFILE => 1,
    ));

foreach ($files as $txt) {
  $tbl = basename($txt, '.txt');

  $sql = "LOCK TABLE ${tbl} WRITE; LOAD DATA LOCAL INFILE '" . basename($txt) . "' INTO TABLE ${tbl}" .
      " FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n'; UNLOCK TABLE ${tbl};";
  $stmt = $con->prepare($sql);

  $stmt->execute();
}
