<?php

include_once(APP_INC_PATH . "class.custom_view.php");
include_once(APP_INC_PATH . "class.record_view.php");
include_once(APP_INC_PATH . "class.sherpa_romeo.php");


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
	 * @param bool $dontUseCache  don't show cache file if its exists
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

			$pat = array('/<!--fez:statsAbs-->\d+<!--\/fez:statsAbs-->/', '/<!--fez:statsDownloads-->\d+<!--\/fez:statsDownloads-->/');
			$rep = array("<!--fez:statsAbs-->$views<!--/fez:statsAbs-->", "<!--fez:statsDownloads-->$dls<!--/fez:statsDownloads-->");

			$htmlContent = preg_replace($pat, $rep, $htmlContent);
			$datastreams = Fedora_API::callGetDatastreams($this->pid);
			$datastreams = Misc::cleanDatastreamListLite($datastreams, $this->pid);

			foreach ($datastreams as $ds) {
				if($ds['controlGroup'] == 'M') {
					$dls = Statistics::getStatsByDatastream($this->pid, $ds['ID']);
					$base64 = base64_encode($ds['ID']);

					$pat = "/<!--fez:ds_$base64-->\d+<!--\/fez:ds_$base64-->/";
					$rep = "<!--fez:ds_$base64-->$dls<!--/fez:ds_$base64-->";
				}
				$htmlContent = preg_replace($pat, $rep, $htmlContent);
			}

            //Replace the navigation bar with appropriate next and prev
            $pieces = explode("<!--sectionnextprevstart-->", $htmlContent);
            $part1 = $pieces[0];
            $part2 = '';
            list($prev, $next) = RecordView::getNextPrevNavigation($this->pid);

            if (!empty($pieces[1])) {
                $pieces = explode("<!--sectionnextprevend-->", $pieces[1]);
                $part2 = $pieces[1];

                $navigationBar = '<!--sectionnnextprevstart-->';

                if ($prev['rek_pid'] || $next['rek_pid']){
                    $navigationBar .= '<tr><th>Browse Collection:</th><td>';
                    if ($prev['rek_pid']) {
                        $navigationBar .= '<a href="/view/'.$prev['rek_pid'].'">Prev: <i>'.$prev['rek_title'].'</i></a>';
                        if ($next['rek_pid']) {
                            $navigationBar .= '<br />';
                        }
                    }
                    if ($next['rek_pid']) {
                        $navigationBar .= '<a href="/view/'.$next['rek_pid'].'">Next: <i>'.$next['rek_title'].'</i></a>';
                    }
                    $navigationBar .= '</td></tr>';
                }
                $navigationBar .= '<!--sectionnextprevend-->';
                $part2 = $navigationBar.$part2;
            }

            $htmlContent = $part1.$part2;
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
	function saveCacheFile($header, $save = true)
	{
		$log = FezLog::get();

		$content = $header.ob_get_flush();

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
		$this->poisonAllCaches();
	}

	function poisonAllCaches()
	{
		$locations = $this->getAllCacheLocations();
		foreach ($locations as $dir) {
			@unlink($dir . $this->cacheFileName);
		}

		return;
	}

    function checkCacheFileExists()
    {
        return (file_exists($this->cachePath.$this->cacheFileName));
    }

	function getPathFileOnDisk()
	{
		$md5arr = str_split($this->cacheFileName, 2);

		// If the incoming request is for a custom view, we need to specify a dedicated cache path.
		$hosts = Custom_View::getCviewListUniqueHosts();
		$customViewComponent = APP_HOSTNAME;
		foreach ($hosts as $host) {
			if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == $host) {
				$customViewComponent = $host;
			}
		}

		return APP_FILECACHE_DIR . $customViewComponent . "/" . $md5arr[0]. '/'. $md5arr[1] .'/';
	}

	function getAllCacheLocations()
	{
		$md5arr = str_split($this->cacheFileName, 2);
		$cacheDirectories = Custom_View::getCviewListUniqueHosts();	// Custom Views
		$cacheDirectories[] = APP_HOSTNAME;							// Default
		foreach ($cacheDirectories as &$dir) {
			$dir = APP_FILECACHE_DIR . $dir . "/" . $md5arr[0]. '/'. $md5arr[1] .'/';
		}

		return $cacheDirectories;
	}
}
