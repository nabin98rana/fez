<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');
require_once(__DIR__ . '/autoload.php'); // since AWS SDK is pulled in via composer, this is how to include it

/**
 * Class for use of Amazon Web Services APIs via the AWS SDK for PHP.
 *
 */
class AWS
{

  /**
   * @var FezLog
   */
  private $log;

  /**
   * @var Aws\Sdk
   */
  private $sdk;


  /**
   * @var String
   */
  private $s3Bucket;

  /**
   * @var String
   */
  private $s3SrcPrefix;

  /**
   * @var int
   */
  private $launchedTasks;

  /**
   * AWS constructor.
   */
  public function __construct($s3Bucket = AWS_S3_BUCKET, $s3SrcPrefix = AWS_S3_SRC_PREFIX) {
    $this->log = FezLog::get();
    $this->sdk = new Aws\Sdk([
      'region'  => AWS_REGION,
      'version' => 'latest'
    ]);
    $this->launchedTasks = 0;
    $this->s3Bucket = $s3Bucket;
    $this->s3SrcPrefix = $s3SrcPrefix;
    putenv("AWS_ACCESS_KEY_ID=" . AWS_KEY);
    putenv("AWS_SECRET_ACCESS_KEY=" . AWS_SECRET);
  }

  /**
   *
   * @return AWS
   */
  public static function get()
  {
    return Zend_Registry::get('aws');
  }

  /**
   * @param string $queueUrl The queue to send the message to
   * @param string $message The message to send
   * @param array $attributes The message attributes to send
   * @return bool Whether the message was sent successfully
   */
  public function sendSqsMessage($queueUrl, $message, $attributes= []) {
    $sqs = $this->sdk->createSqs();

    $m = [
      'QueueUrl' => $queueUrl,
      'MessageBody' => $message,
    ];
    if (count($attributes) > 0) {
      $m['MessageAttributes'] = $attributes;
    }

    try {

      $sqs->sendMessage($m);
      return true;

    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }

  /**
   * @param string $service The name of the service to describe
   * @return \Aws\Result|bool The result of describing the service or false if
   *                          an error occurred attempting to describe the service
   */
  public function describeEcsService($service) {
    $ecs = $this->sdk->createEcs();

    try {
      $result = $ecs->describeServices([
        'cluster' => AWS_ECS_CLUSTER,
        'services' => [
          $service
        ]
      ]);
      return $result;

    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }

  /**
   * @param string $family
   * @param array $overrides
   * @return bool
   */
  public function runBackgroundTask($family, $overrides) {
    // Only launch one task per run..
    if ($this->launchedTasks > 0) {
      return true;
    }
    // Make sure there aren't any existing running tasks (except the service tasks)
    // TODO(am): Use a config var instead of hard coding the number..
    if ($this->countTasksRunningOrPendingInFamily($family) === 1) {
      $this->launchedTasks++;
      return $this->runTask($family, $overrides, 1);
    }
    return false;
  }

  /**
   * @param string $family
   * @return int|bool
   */
  public function countTasksRunningOrPendingInFamily($family) {
    $ecs = $this->sdk->createEcs();

    try {
      $result = $ecs->listTasks([
        'cluster' => AWS_ECS_CLUSTER,
        'desiredStatus' => 'RUNNING',
        'family' => $family
      ]);
      $count = count($result['taskArns']);

      $result = $ecs->listTasks([
        'cluster' => AWS_ECS_CLUSTER,
        'desiredStatus' => 'PENDING',
        'family' => $family
      ]);
      $count += count($result['taskArns']);

      return $count;

    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }

  /**
   * @param string $taskDefinition
   * @param array $overrides
   * @param int $count
   * @return \Aws\Result|bool
   */
  public function runTask($taskDefinition, $overrides, $count) {
    $ecs = $this->sdk->createEcs();

    try {
      $result = $ecs->runTask([
        'cluster' => AWS_ECS_CLUSTER,
        'count' => $count,
        'overrides' => $overrides,
        'taskDefinition' => $taskDefinition,
      ]);
      return $result;

    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }

  /**
   * @param string $taskARN
   * @param \Aws\Result $result
   * @return \Aws\Result|bool
   */
  private function isTaskInTaskResult($taskARN, $result) {
    if ($result->hasKey('taskArns')) {
      foreach ($result->get('taskArns') as $task) {
        if ($task == $taskARN) {
          return true;
        }
      }
    }
    return false;
  }


  /**
   * @param string $taskARN
   * @param string $family
   * @return \Aws\Result|bool
   */
  public function isTaskRunning($taskARN, $family) {
    $ecs = $this->sdk->createEcs();

    try {
      $result = $ecs->listTasks([
          'cluster' => AWS_ECS_CLUSTER,
          'family' => $family,
          'desiredStatus' => 'RUNNING',
      ]);
      if ($this->isTaskInTaskResult($taskARN, $result) == true) {
        return true;
      }
      // Also check if it's pending
      $result = $ecs->listTasks([
          'cluster' => AWS_ECS_CLUSTER,
          'family' => $family,
          'desiredStatus' => 'PENDING'
      ]);
      return $this->isTaskInTaskResult($taskARN, $result);
    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }


  /**
   * @param string $src
   * @param array $files array of full file path strings $files
   * @return boolean
   */
  public function postFile($src, $files)
  {
    // Create an Amazon S3 client using the shared configuration data.
    $client = $this->sdk->createS3();

    if (count($files) < 1) {
      $this->log->err('No files in request');
      return false;
    }

    $maxSize = 9999999999;

    $results = [];
    foreach ($files as $file) {
      $fileSize = filesize($file);

      if ($fileSize > $maxSize) {
        $this->log->err('File size greater than maximum allowed');
        return false;
      }
      $baseFile = basename($file);
      $mimeType = Misc::mime_content_type($file);
      $key = $this->createPath($src, $baseFile);
      $meta = [
          'key'  => $baseFile,
          'type' => $mimeType,
          'name' => $baseFile,
          'size' => $fileSize,
      ];
      try {
        $client->putObject([
            'Bucket' => $this->s3Bucket,
            'Key' => $key,
            'SourceFile' => $file,
            'ContentType' => $mimeType,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => $meta
        ]);
        $results[] = $meta;
      } catch (\Aws\S3\Exception\S3Exception $e) {
        $this->log->err($e->getMessage());
        return false;
      }
    }

    return true; // TODO: return false when the response didn't work, log an error
  }

  /**
   * @param string $src
   * @param string $content
   * @param string $fileName
   * @param string $mimeType
   * @return boolean
   */
  public function postContent($src, $content, $fileName, $mimeType)
  {
    // Create an Amazon S3 client using the shared configuration data.
    $client = $this->sdk->createS3();

    if (strlen($fileName) < 1) {
      $this->log->err('No file name in request');
      return false;
    }

    $maxSize = 9999999999;

    $results = [];

    $fileSize = sizeof($content);

    if ($fileSize > $maxSize) {
      $this->log->err('File size greater than maximum allowed');
      return false;
    }

    $meta = [
        'key'  => $fileName,
        'type' => $mimeType,
        'name' => $fileName,
        'size' => $fileSize,
    ];

    try {
      $key = $this->createPath($src, $fileName);
      $client->putObject([
          'Bucket' => $this->s3Bucket,
          'Key' => $key,
          'Body' => $content,
          'ContentType' => $mimeType,
          'ServerSideEncryption' => 'AES256',
          'Metadata' => $meta
      ]);
      $results[] = $meta;
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }

    return true; // TODO: return false when the response didn't work, log an error
  }

  /**
   * @param string $src
   * @param string $id
   * @return string
   */
  public function getById($src, $id)
  {
    $id = basename($id);
    $src = empty($this->s3SrcPrefix) ? $src : $this->s3SrcPrefix . '/' . $src;
    $src = rtrim($src, '/');
    $path = $this->createPath($src, $id);
    $resource = AWS_FILE_SERVE_URL . '/' . $path;

    // expiration date must be in Unix time format and Coordinated Universal Time (UTC)
    date_default_timezone_set('UTC');
    $expires = time() + 86400;
    date_default_timezone_set(APP_DEFAULT_USER_TIMEZONE);

    $json = '{"Statement":[{"Resource":"'.$resource.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';

    $signedUrl = $this->getSignedUrl($resource, $json, $expires, 'canned');

    return $signedUrl;
  }

  /**
   * @param string $match
   * @return boolean
   */
  public function deleteMatchingObjects($match) {
    if (strlen($match) > 1) {
      try {
        $match = $this->createPath('', $match);
        $client = $this->sdk->createS3();
        $client->deleteMatchingObjects($this->s3Bucket, $match);
      } catch (\Aws\S3\Exception\S3Exception $e) {
        $this->log->err($e->getMessage());
        return false;
      }
      return true;
    } else {
      return false;
    }
  }

  /**
   * @param string $path - the path inside the bucket
   * @return array  - iterator
   */
  public function listObjects($path) {
    $iterator = array();
    try {
      $client = $this->sdk->createS3();
      $path = $this->createPath($path, '');
      $iterator = $client->getIterator('ListObjects', array(
          'Bucket' => $this->s3Bucket,
          "Prefix" => $path . '/'
      ));
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
    }
    return $iterator;
  }

  /**
   * @param string $key - the full path to the file eg data/UQ_3/hai.jpg
   * @param string $versionId (optional) - the version to return. If empty will return latest.
   * @return array $result - the object
   */
  public function getObject($key, $versionId = NULL) {
    try {
      $client = $this->sdk->createS3();
      $key = $this->createPath($key, '');
      $args = array(
          'Bucket' => $this->s3Bucket,
          'Key' => $key,
      );
      if (!is_null($versionId)) {
        $args['VersionId'] = $versionId;
      }
      $result = $client->getObject($args);

    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
    }
    return $result;
  }

  /**
   * @param string $prefix
   * @return boolean
   */
  public function putObject($prefix)
  {
    // Create an Amazon S3 client using the shared configuration data.
    $client = $this->sdk->createS3();
     $key = $this->createPath('', $prefix);
    try {
      $client->putObject([
        'Bucket' => $this->s3Bucket,
        'Key' => $key,
      ]);
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }

    return true;
  }

  /**
   * @param string $src
   * @param string $id
   * @return boolean
   */
  public function checkExistsById($src, $id) {
    $id = basename($id);
    try {
      $client = $this->sdk->createS3();
      $path = $this->createPath($src, $id);
      $found = $client->doesObjectExist($this->s3Bucket, $path);
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }
    return $found;
  }

  /**
   * @param string $src
   * @param string $id
   * @return boolean
   */
  public function deleteById($src, $id) {
    $id = basename($id);
    try {
      $client = $this->sdk->createS3();
      $key = $this->createPath($src, $id);
      $client->deleteObject(
        array(
            'Bucket' => $this->s3Bucket,
            'Key'    => $key
        ));
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }
    return true;
  }

  /**
   * @param string $src
   * @param string $id
   * @return boolean
   */
  public function purgeById($src, $id) {
    $id = basename($id);
    try {
      $client = $this->sdk->createS3();
      $prefix = $this->createPath($src, $id);
      // get all versions of the key
      $versions = $client->listObjectVersions(array(
          'Bucket' => $this->s3Bucket,
          'Prefix' =>  $prefix,
      ))->getPath('Versions');

      // clean out anything we got back that isn't this ID
      $cleanVersions = array();
      foreach ($versions as $dv) {
        if ($dv['Key'] == $prefix) {
          $cleanVersions[] = $dv;
        }
      }
      // delete all the versions
      if (count($cleanVersions) > 0) {
        $result = $client->deleteObjects(array(
          'Bucket' => $this->s3Bucket,
          'Delete' => [
            'Objects' => array_map(function ($version) {
              return array(
                'Key' => $version['Key'],
                'VersionId' => $version['VersionId']
              );
            }, $cleanVersions),
          ]));
      }
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }
    return true;
  }

  /**
   * @param string $src
   * @param string $id
   * @param string $newSrc
   * @param string $newId
   * @param string $newLabel
   * @return boolean
   */
  public function rename($src, $id, $newSrc, $newId, $newLabel) {
    $id = basename($id);
    try {
      $client = $this->sdk->createS3();
      $key = $this->createPath($src, $id);
      $newKey = $this->createPath($newSrc, $newId);
      //TODO: may need to add 'label' metadata as new param inputs
      $client->copyObject(
          array(
              'Bucket' => $this->s3Bucket,
              'Key'    => $key
          ),
          array(
              'Bucket' => $this->s3Bucket,
              'Key'    => $newKey
          )
      );

      $client->deleteObject(
          array(
              'Bucket' => $this->s3Bucket,
              'Key'    => $key
          ));
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return false;
    }
    return true;
  }


  /**
   * @param string $src
   * @param string $id
   * @return String Response
   */
  public function getMetadata($src, $id)
  {
    try {
      $client = $this->sdk->createS3();
      $key = $this->createPath($src, $id);
      $result = $client->getObject(array(
          'Bucket' => $this->s3Bucket,
          'Key' => $key
      ));
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return "";
    }
    return $result['Metadata'];
  }

  /**
   * @param string $src
   * @param string $id
   * @param array $params
   * @return String Response
   */
  public function getFileContent($src, $id, $params = [])
  {
    try {
      $client = $this->sdk->createS3();
      $key = $this->createPath($src, $id);
      $args = array(
          'Bucket' => $this->s3Bucket,
          'Key' => $key
      );
      if (count($params) > 0) {
        $args = array_merge($args, $params);
      }
      $result = $client->getObject($args);
    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
      return "";
    }
    return (string) $result['Body'];
  }

  /**
   * @param string $prefix
   * @return array
   */
  public function listObjectsInBucket($prefix)
  {
    $objects = [];
    try {
      $client = $this->sdk->createS3();
      $prefix = $this->createPath($prefix, '');
      $result = $client->listObjects([
        'Bucket' => $this->s3Bucket,
        'Prefix' => $prefix,
      ]);

      return $result['Contents'];

    } catch (\Aws\S3\Exception\S3Exception $e) {
      $this->log->err($e->getMessage());
    }
    return $objects;
  }

  /**
   * @param string $resource
   * @param string $policy
   * @param int $expires
   * @param string $type Either 'canned' or 'custom'
   * @return string
   */
  private function getSignedUrl($resource, $policy, $expires, $type = 'canned')
  {
    $privateKeyFilename = AWS_CF_PRIVATE_KEY_FILE;
    $keyPairId = AWS_CF_KEY_PAIR_ID;

    $encodedPolicy = $this->urlSafeBase64Encode($policy);

    $signature = $this->rsaSha1Sign($policy, $privateKeyFilename);
    $encodedSignature = $this->urlSafeBase64Encode($signature);

    $resource .= strpos($resource, '?') === false ? '?' : '&';

    switch ($type) {
      case 'canned':
        $resource .= "Expires=" . $expires;
        break;
      case 'custom':
      default:
        $resource .= "Policy=" . $encodedPolicy;
        break;
    }
    $resource .= "&Signature=" . $encodedSignature . "&Key-Pair-Id=" . $keyPairId;

    return str_replace('\n', '', $resource);
  }

  /**
   * @param string $policy
   * @param string $privateKeyFilename
   * @return string
   */
  private function rsaSha1Sign($policy, $privateKeyFilename)
  {
    $signature = '';

    // load the private key
    $fp = fopen($privateKeyFilename, 'r');
    $privateKey = fread($fp, 8192);
    fclose($fp);
    $privateKeyId = openssl_pkey_get_private($privateKey);

    // compute signature
    openssl_sign($policy, $signature, $privateKeyId, OPENSSL_ALGO_SHA1);

    // free the key from memory
    openssl_free_key($privateKeyId);

    return $signature;
  }

  /**
   * @param string $value
   * @return string
   */
  private function urlSafeBase64Encode($value)
  {
    $encoded = base64_encode($value);
    // replace unsafe characters +, = and / with
    // the safe characters -, _ and ~
    return str_replace(
        ['+', '=', '/'],
        ['-', '_', '~'],
        $encoded
    );
  }

  /**
   * @param string $src
   * @param string $id
   * @return string
   */
  private function createPath($src, $id)
  {
    if (!empty($this->s3SrcPrefix) && strpos($src, $this->s3SrcPrefix) !== 0) {
      $src = $this->s3SrcPrefix . '/' . $src;
    }
    $src = rtrim($src, '/');
    $path = empty($id) ? $src : $src . '/' . $id;
    return $path;
  }

}
