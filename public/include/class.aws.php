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
  }

  /**
   * @param string $queueUrl The queue to send the message to
   * @param string $message The message to send
   */
  public function sendSqsMessage($queueUrl, $message) {
    $sqs = $this->sdk->createSqs([

    ]);
    $sqs->sendMessage(array(
      'QueueUrl'    => $queueUrl,
      'MessageBody' => $message,
    ));
  }
}
