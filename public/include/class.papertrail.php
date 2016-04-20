<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');

/**
 * Class for use of the Papertrail API.
 *
 */
class Papertrail {

  /**
   * @var FezLog
   */
  private $log;

  /**
   * @var string
   */
  private $apiToken;

  const PAPERTRAIL_SEARCH_API_URL = 'https://papertrailapp.com/api/v1/events/search.json';

  /**
   * Papertrail constructor.
   */
  public function __construct() {
    $this->log = FezLog::get();
    $this->apiToken = APP_PAPERTRAIL_TOKEN;
  }

  /**
   * @param $searchString
   * @return string Search response
   */
  public function search($searchString) {
    return $this->doRequest(self::PAPERTRAIL_SEARCH_API_URL . '?' . urlencode($searchString));
  }

  /**
   * Execute a cURL request on the API
   * @param array $url
   * @return string The API response
   */
  private function doRequest($url)
  {
    $curlHandle = curl_init($url);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
      'X-Papertrail-Token:' . $this->apiToken,
      'Accept: application/json'
    ));
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    $curlResponse = curl_exec($curlHandle);

    if (!$curlResponse) {
      $this->log->err(curl_errno($curlHandle));
    } elseif (is_numeric(strpos($curlResponse, 'service-error'))) {
      $this->log->err($curlResponse);
    }

    curl_close($curlHandle);

    return $curlResponse;
  }
}