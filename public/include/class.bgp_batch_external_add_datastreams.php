<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.uploader.php');
include_once(APP_INC_PATH.'class.batchimport.php');
include_once(APP_INC_PATH.'class.background_process.php');

class BackgroundProcess_Batch_External_Add_Datastreams extends BackgroundProcess
{
	function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_batch_external_add_datastreams.php';
		$this->name = 'Batch Add Datastreams (External)';
	}

	function run()
	{
        $this->setState(BGP_RUNNING);
		extract(unserialize($this->inputs));
		if (empty($pid)) {
		    return;
        }
        $aws = new AWS(AWS_S3_CACHE_BUCKET);
        $processedFileName = $pid . '.processed.txt';
        $dataPath = Uploader::getUploadedFilePath($pid);
        if (! $aws->checkExistsById($dataPath, '')) {
            // No files to be processed
            $this->setState(BGP_FINISHED);
            return;
        }
        if ($aws->checkExistsById($dataPath, $processedFileName)) {
            // Files have already (or are being) processed
            $this->setState(BGP_FINISHED);
            return;
        }
        touch(APP_TEMP_DIR . $processedFileName);
        $aws->postFile($dataPath, [APP_TEMP_DIR . $processedFileName], TRUE, 'plain/text');
        $filesToCleanup = array();
        $tmpFilesArray = Uploader::generateFilesArray($pid, 0);
        if (!empty($tmpFilesArray['_files']) && count($tmpFilesArray['_files']) > 0) {
            $files = $tmpFilesArray['_files'];
            for ($i = 0; $i < count($files); $i++) {
                $ds = $files[$i];
                if (! is_file($ds)) {
                    continue;
                }
                BatchImport::handleStandardFileImport($pid, $ds, Misc::shortFilename($ds));
                $filesToCleanup[] = basename($ds);
            }
            Record::setIndexMatchingFields($pid);
        }
        // Cleanup
        foreach ($filesToCleanup as $file) {
            $aws->deleteById($dataPath, $file);
        }
        $aws->deleteById($dataPath, $processedFileName);
        $aws->deleteById($dataPath, '');
        $this->setState(BGP_FINISHED);
	}
}
