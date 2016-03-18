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
   * AWS constructor.
   */
  public function __construct() {
    $this->log = FezLog::get();
    $this->sdk = new Aws\Sdk([
      'region'  => AWS_REGION,
      'version' => 'latest'
    ]);
    putenv("AWS_ACCESS_KEY_ID=" . AWS_KEY);
    putenv("AWS_SECRET_ACCESS_KEY=" . AWS_SECRET);
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
   * @param string $src
   * @param array of full file path strings $files
   * @return boolean
   */
  public function postFile($src, $files)
  {
    // Create an Amazon S3 client using the shared configuration data.
    $client = $this->sdk->createS3();

    if (sizeof($files) < 1) {
      $this->log->err('No files in request');
      return false;
    }

    $maxSize = 9999999999;

    $results = [];
    foreach ($files as $file) {

      if (sizeof($file) > $maxSize) {
        $this->log->err('File size greater than maximum allowed');
        return false;
      }

      $mimeType = Misc::mime_content_type($file);
      $key = '' . uniqid() . '.' . pathinfo($file, PATHINFO_EXTENSION);

      $meta = [
          'key'  => $key,
          'type' => $mimeType,
          'name' => $file->getClientOriginalName(),
          'size' => $file->getSize(),
      ];

      $client->putObject([
          'Bucket' => AWS_S3_BUCKET . '/' . $src,
          'Key' => $key,
          'SourceFile' => $file,
          'ContentType' => $mimeType,
          'ServerSideEncryption' => 'AES256',
          'Metadata' => $meta
      ]);
      $results[] = $meta;
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
    $resource = AWS_FILE_SERVE_URL . '/' . $src . '/' . $id;

    // expiration date must be in Unix time format and Coordinated Universal Time (UTC)
    date_default_timezone_set('UTC');
    $expires = time() + 86400;
    //date_default_timezone_set(Config::get('app.timezone'));

    $json = '{"Statement":[{"Resource":"'.$resource.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';

    $signedUrl = $this->getSignedUrl($resource, $json, $expires, 'canned');

    return $signedUrl;
  }

  /**
   * @param string $src
   * @param string $id
   * @return Json Response
   */
  public function getMetadata($src, $id)
  {
    $client = $this->sdk->createS3();

    $result = $client->getObject(array(
        'Bucket' => AWS_S3_BUCKET . '/' . $src,
        'Key' => $id
    ));

    return $result['Metadata'];
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



}
