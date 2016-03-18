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

    $message = [
      'QueueUrl' => $queueUrl,
      'MessageBody' => $message,
    ];
    if (count($attributes) > 0) {
      $message['MessageAttributes'] = $attributes;
    }

    try {

      $sqs->sendMessage($message);
      return true;

    } catch (Exception $ex) {
      $this->log->err($ex->getMessage());
      return false;
    }
  }
}
