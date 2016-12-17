<?php

include_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "config.inc.php");
include_once(APP_INC_PATH . "class.filecache.php");
include_once(APP_INC_PATH . "class.dsresource.php");
include_once(APP_INC_PATH . "class.datastream.php");

class bookReaderPDFConverter
{
  private $bookreaderDataPath;
  private $s3bookreaderDataPath;
  private $sourceFilePath;
  private $sourceFileStat = array();
  private $log;
  private $queue = array();
  var $useS3;

  public function __construct()
  {
    $this->log = FezLog::get();
    if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
      $this->useS3 = true;
    } else {
      $this->useS3 = false;
    }
  }

  /**
   * Set input resource (PDF) parameters.
   * @param  $pid
   * @param  $sourceFile
   * @return void
   */
  public function setSource($pid, $sourceFile, $altFilename = null)
  {
    $sourceFile = trim($sourceFile);
    //Is the source file on the filesystem or do we need to download it?
    if (strstr($sourceFile, 'http://') || strstr($sourceFile, 'https://')) {
      $this->sourceFilePath = $this->getURLSource($sourceFile);

    } else {
      if ($this->useS3) {
        $tmpPth = Misc::getFileTmpPath($sourceFile);
        BatchImport::getFileContent($sourceFile, $tmpPth, false);
        $this->sourceFilePath = $tmpPth;
      } else {
        $this->sourceFilePath = $sourceFile;
      }
    }

    if ($altFilename) {
      $this->sourceInfo($altFilename);
    } else {
      $this->sourceInfo();
    }
    if (strstr($pid, ':')) {
      $pid = str_replace(':', '_', $pid);
    }
    //If this is to store in s3, save to a temp folder mirroring the non-s3 path
    if ($this->useS3) {
      $this->bookreaderDataPath = Misc::getFileTmpPath($pid . '/' . $this->sourceFileStat['filename']);
      $s3Prefix = '';
      if (defined('AWS_S3_SRC_PREFIX') && !empty(AWS_S3_SRC_PREFIX)) {
        $s3Prefix = AWS_S3_SRC_PREFIX . '/';
      }
      $this->s3bookreaderDataPath = $s3Prefix . str_replace('../', '', BR_IMG_DIR) . $pid . '/' . $this->sourceFileStat['filename'];
    } else {
      $this->bookreaderDataPath = APP_PATH . BR_IMG_DIR . $pid . '/' . $this->sourceFileStat['filename'];
    }
  }

  /**
   * Set queue of pdfs to process. The queue array elements are arrays
   * in the form array($pid,$sourcePath,$conversionMethod).
   * @param array $queue
   * @return void
   */
  public function setQueue(array $queue)
  {
    $this->queue = $queue;
  }

  /**
   * Create a queue based on PID. Includes all pdf resources for that PID.
   * @param  $pid
   * @param string $convMeth
   * @return void
   */
  public function setPIDQueue($pid, $convMeth = 'pdfToJpg')
  {
    $q = array();

    $datastreams = Fedora_API::callGetDatastreams($pid);
    $srcURL = APP_FEDORA_GET_URL . "/" . $pid . '/';
    foreach ($datastreams as $ds) {

      if ($ds['MIMEType'] == 'application/pdf' || $ds['MIMEType'] == 'application/pdf;') {
        if ($this->useS3) {
          $fullURL = $ds['ID'];
          Datastream::setBookreader($pid, $ds['ID'], 1);
        } else {
          $fullURL = $srcURL . $ds['ID'];
        }
        $q[] = array($pid, $fullURL, $convMeth);
      }
    }


    $this->queue = $q;
  }

  /**
   * Check if resource images have been generated by
   * performing a page count.
   * @param  $resourcePath
   * @return bool
   */
  public function resourceGenerated($resourcePath)
  {

    if ($this->useS3) {
      $aws = new AWS(AWS_S3_CACHE_BUCKET);
      $objects = $aws->listObjects($resourcePath);
      $pageCount = 0;
      foreach ($objects as $object) {
        $pageCount++;
      }
    } else {
      if (is_dir($resourcePath)) {
        $pageCount = count(array_filter(scandir($resourcePath),
            array($this, 'ct')));
      } else {
        $pageCount = 0;
      }
    }
    return ($pageCount > 0) ? true : false;
  }

  public function ct($element)
  {
    return !in_array($element, array('.', '..'));
  }

  /**
   * Gather and store information about the source PDF.
   * @return void
   */
  protected function sourceInfo($altFilename = null)
  {
    $parts = pathinfo($this->sourceFilePath);
    if ($altFilename) {
      $altFilename = explode('.pdf', $altFilename);
      $altFilename = $altFilename[0];
      $parts['filename'] = $altFilename;
    }

    $this->sourceFileStat = $parts;
  }

  /**
   * Download a pdf from a URL in chunks and save to a tmp location.
   * @param  $url
   * @return string
   */
  protected function getURLSource($url)
  {
    $parts = pathinfo($url);
    $fhurl = fopen($url, 'rb');
    if ($fhurl == false) {
      return false;
    }
    $tmpPth = Misc::getFileTmpPath($parts['basename']);
    $fhfs = fopen($tmpPth, 'ab');

    while (!feof($fhurl)) {
      fwrite($fhfs, fread($fhurl, 1024));
    }

    fclose($fhurl);
    fclose($fhfs);

    return $tmpPth;
  }

  /**
   * Create a directory for this PDF's images if required
   * @return bool|int
   */
  protected function makePath()
  {
    $dir = 0;
    if (!is_dir($this->bookreaderDataPath)) {
      $dir = mkdir($this->bookreaderDataPath, 0755, true);
    }

    return $dir;
  }

  /**
   * Run the selected conversion method.
   * @param  $conversionType
   * @param bool $forceRegenerate
   * @return void
   */
  public function run($conversionType, $forceRegenerate = false)
  {
    if (method_exists($this, $conversionType)) {
      if ($this->useS3) {
        $checkPath = $this->s3bookreaderDataPath;
      } else {
        $checkPath = $this->bookreaderDataPath;
      }

      //Generate the resource images if they're not already there or if we are forcing it to do so.
      $resourceGenerated = ($forceRegenerate) ? false :
          $this->resourceGenerated($checkPath);
      if (!$resourceGenerated) {
        $this->$conversionType();
      }

      //Delete the tmp source file if there is one.
      if (strstr($this->sourceFilePath, Misc::getFileTmpPath())) {
        unlink($this->sourceFilePath);
      }
    } else {
      $this->log->err('Conversion method does not exist:' . __FILE__ . ':' . __LINE__);
    }
  }

  /**
   * Run the bookreader job queue.
   * @param string $pid
   * @param bool $forceRegenerate
   * @return void
   */
  public function runQueue($pid, $forceRegenerate = false)
  {
    foreach ($this->queue as $job) {
      if (is_array($job) && count($job) >= 3) {
        $this->setSource($job[0], $job[1], $job[3]);
        $this->run($job[2], $forceRegenerate);
        if (APP_FILECACHE == "ON") {
          $cache = new fileCache($pid, 'pid=' . $pid);
          $cache->poisonCache();
        }
      } else {
        $this->log->err('Malformed job in bookreader queue:' . __FILE__ . ':' . __LINE__);
      }
    }
  }

  /**
   * Perform a conversion of the PDF to jpg format. One image per page.
   * @return void
   *
   */
  protected function pdfToJpg()
  {

    $this->makePath();
    if ($this->useS3 == true || is_writable($this->bookreaderDataPath)) {
      $cmd = GHOSTSCRIPT_PTH . ' -q -dBATCH -dNOPAUSE -dJPEGQ=80 -sDEVICE=jpeg -r150 -sOutputFile=' .
          $this->bookreaderDataPath . '/' . $this->sourceFileStat['filename'] . '-%04d.jpg ' .
          realpath($this->sourceFilePath);

      shell_exec(escapeshellcmd($cmd));
    } else {
      $this->log->err('Unable to write page images to directory:' . __FILE__ . ':' . __LINE__);
    }
    // if using s3, it will have put the page images into a temp dir, now they need uploading
    if ($this->useS3) {
      $aws = new AWS(AWS_S3_CACHE_BUCKET);
      //upload the files (and tell postFile to delete them after each upload is a success)
      $files = Misc::getFileList($this->bookreaderDataPath, true, false);
      $aws->postFile($this->s3bookreaderDataPath, $files, true);
    }
  }
}