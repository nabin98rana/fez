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

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH . "class.origami.php");

class BackgroundProcess_Reindex_Origami_Images extends BackgroundProcess
{
	function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_reindex_origami_images.php';
		$this->name = 'Reindex Origami Images';
	}

	function run()
	{
		$this->setState(BGP_RUNNING);

		$cnt    = 0;
		$pids   = Reindex::getPIDlist();
		$total  = count($pids);

		foreach ($pids as $pid) {

			$ds = Fedora_API::callGetDatastreams($pid);

			foreach ($ds as $stream) {

				if((strpos($stream['ID'], 'web_') === false) &&
				(strpos($stream['ID'], 'preview_') === false) &&
				(strpos($stream['ID'], 'thumbnail_') === false) &&
				($stream['MIMEType'] == 'image/jpeg' ||
				$stream['MIMEType'] == 'image/jpg' ||
				$stream['MIMEType'] == 'image/tif' ||
				$stream['MIMEType'] == 'image/tiff')) {

					$this->setStatus("Creating Title for DS - " .$stream['ID']);
					Origami::createTitles($pid, $stream['ID'], $stream['MIMEType']);
				}

			}

			$cnt++;
			if(($cnt % 1000) == 0) {
				$this->setStatus("Processed ($cnt\\$total) pids");
			}
		}

		$this->setState(BGP_FINISHED);
	}
}
