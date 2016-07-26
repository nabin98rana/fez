<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.exiftool.php");

class bookReaderImplementation
{
    private $log;
    private $bookDir;
    private $useS3;

    public function __construct($bookDir)
    {
        $this->log = FezLog::get();
        $this->bookDir = $bookDir;
        if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
          $this->useS3 = true;
        } else {
          $this->useS3 = false;
        }
    }

    public function getCloudfrontURL($pid, $resource, $file) {
      $aws = AWS::get();
      $path = AWS_S3_SRC_PREFIX.'/'.str_replace('../', '', BR_IMG_DIR) . $pid . '/' .$resource .'/' ;
      $cfURL = $aws->getById($path, $file);
      return $cfURL;
    }

    /**
     * Return the number of pages in this resource minus '.' and '..'.
     * @return int
     */
    public function countPages($pid = false, $dsID = false)
    {
        if ($this->useS3) {
          //Just get it from exiftool because S3 doesn't have a way to count object
          $exifDetails = Exiftool::getDetails($pid, $dsID);
          if (is_numeric($exifDetails['exif_page_count'])) {
            return $exifDetails['exif_page_count'];
          } else {
            return 0;
          }
        } else {
          if(is_dir($this->bookDir))
          {
            return count(array_filter(scandir($this->bookDir),
                array($this, 'ct')));
          }
        }
    }

    public function ct($element)
    {
        return !in_array($element, array('.','..'));
    }
}