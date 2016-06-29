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

if (!(stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin'))) {
    proc_nice(10);
}
array_shift($argv);
$ARGS = $argv;

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH . "class.log.php");

$useAws = false;
if (defined('AWS_ENABLED') && AWS_ENABLED == 'true') {
  $useAws = true;
}

$bgp_id = (@$ARGS[0]) ? @$ARGS[0] : @$_ENV['BGP_ID'];
$base = @$ARGS[1];
$launchTask = @$ARGS[2];

if (!is_numeric($bgp_id)) {
  echo "bgp_id is not numeric so exiting $bgp_id";
  exit;
}

if ($useAws && ($launchTask == 'staging' || $launchTask == 'production')) {
  $aws = AWS::get();
  $family = 'fez' . $launchTask;
  $result = $aws->runBackgroundTask($family, [
    'containerOverrides' => [
      [
        'environment' => [
          [
            'name' => 'BGP_ID',
            'value' => $bgp_id,
          ],
        ],
        'name' => 'fpm',
      ],
    ],
  ]);
  // if this is a fulltext lock background process then set the lock value to the task ARN
  $bgp = new BackgroundProcess($bgp_id);
  $bgp_details = $bgp->getDetails();
  if ($bgp_details['bgp_include'] == 'class.bgp_fulltext_index.php') {
    //set bgp_task to $result->ARN so we can check it later
    $log = FezLog::get();
    $log->warn("ECS task dump = ".print_r($result, true)." for bgp ".$bgp_id);
    $tasks = $result->get('tasks');
    $bgp->setTask($tasks[0]['taskArn']);
  }


} else {
  $bgp = new BackgroundProcess($bgp_id);
  $bgp->runCurrent();

  if ($useAws) {
    // Continue to process any remaining background processes (going back to 100 IDs ago)
    // before exiting the task
    BackgroundProcess::runRemaining(((int) $bgp_id - 100));
  }
}