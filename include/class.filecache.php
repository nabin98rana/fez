<?php


class fileCache {

	var $path;
	var $md5;
	var $pid;
	var $cachePath;
	var $flushCache;

	function fileCache($pid, $cacheid, $flushCache = false) 
	{

		$this->flushCache = $flushCache;
		if($this->flushCache == 1 || $this->flushCache == true) {
			$cacheid = str_replace('&flushcache='.$flushCache, '', $cacheid);
		}

		$this->pid = $pid;
		$this->cacheFileName = md5($cacheid);
		$this->cachePath = $this->getPathFileOnDisk();

	}

	/**
	 * Check if a cache file exists and then display it
	 *
	 * @param bool $dontUseCache  dont show cache file if its exists
	 *
	 * @access public
	 */
	function checkForCacheFile($dontUseCache = false) 
	{

		if(file_exists($this->cachePath.$this->cacheFileName) && !$this->flushCache) {
			Statistics::addBuffer($this->pid);
			 
			$htmlContent = file_get_contents($this->cachePath.$this->cacheFileName);
			 
			$views = Record::getSearchKeyIndexValue($this->pid, "Views");
			$dls = Record::getSearchKeyIndexValue($this->pid, "File Downloads");
			 
			$pat = array('/<fez:statsAbs>\d+<\/fez:statsAbs>/', '/<fez:statsDownloads>\d+<\/fez:statsDownloads>/');
			$rep = array("<fez:statsAbs>$views</fez:statsAbs>", "<fez:statsDownloads>$dls</fez:statsDownloads>");

			$htmlContent = preg_replace($pat, $rep, $htmlContent);
			 
			$datastreams = Fedora_API::callGetDatastreams($this->pid, $requestedVersionDate, 'A');
			$datastreams = Misc::cleanDatastreamListLite($datastreams, $this->pid);

			foreach ($datastreams as $ds) {
				if($ds['controlGroup'] == 'M') {
					$dls = Statistics::getStatsByDatastream($this->pid, $ds['ID']);
					$base64 = base64_encode($ds['ID']);
	     
					$pat = "/<fez:ds_$base64>\d+<\/fez:ds_$base64>/";
					$rep = "<fez:ds_$base64>$dls</fez:ds_$base64>";
				}
				$htmlContent = preg_replace($pat, $rep, $htmlContent);
			}
			 
			 
//			$htmlContent = preg_replace($pat, $rep, $htmlContent);
			 
			echo $htmlContent;
			exit();
		}
	  
		ob_start();
	  
	}

	/**
	 * Get contents from buffer and save to disk
	 *
	 * @param bool $save   whether we just output the buffer
	 *                     and dont save it to disk
	 *
	 * @access public
	 */
	function saveCacheFile($save = true) 
	{
		$log = FezLog::get();

		$content = ob_get_flush();
	  
		/*
		 * Sometimes we just want to echo results but
		 * not save them to disk
		 * ie. When someone tries to view an invalid pid
		 */
		if($save) {
			 
			if(!is_dir($this->cachePath)) {
				$ret = mkdir($this->cachePath, 0775, true);
				 
				if(!$ret) {
					$log->err(array("Cache Page Failed - Could not create folder " . $this->cachePath, __FILE__ , __LINE__ ));
					return;
				}
			}
			 
			$handle = fopen($this->cachePath.$this->cacheFileName, 'w');
			if(!$handle) {
				$log->err(array("Cache Page Failed - Could not open cache file for saving " . $this->cachePath, __FILE__ , __LINE__ ));
				return;
			}
			 
			fwrite($handle, $content);
			fclose($handle);
			 
		}

	}


	function poisonCache() 
	{

		@unlink($this->cachePath.$this->cacheFileName);

	}

	function getPathFileOnDisk() 
	{

		$md5arr = str_split($this->cacheFileName, 2);

		// If the incoming request is for a custom view, we need to specify a dedicated cache path
		$customViewComponent = "";
		if (@$_SERVER['HTTP_HOST'] != APP_HOSTNAME) {
			$customViewComponent = $_SERVER['HTTP_HOST'] . "/";
		}
		
		return APP_FILECACHE_DIR . $customViewComponent . $md5arr[0]. '/'. $md5arr[1] .'/';

	}
}
