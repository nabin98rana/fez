<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
// | Authors: |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.rhd_student_retrieval.php');
include_once(APP_INC_PATH . 'class.background_process.php');

class BackgroundProcess_Import_New_Rhd_Students extends BackgroundProcess
{

    /**
     * @var FezLog
     */
    private $log;


    function __construct()
    {
        parent::__construct();
        $this->include = 'class.bgp_import_new_rhd_students.php';
        $this->name = 'Import new RHD students';
        $this->log = FezLog::get();
    }

    function run()
    {
        $this->setState(BGP_RUNNING);
        extract(unserialize($this->inputs));

        if (SINET_LOAD_ENABLED) {
            $sinetDb = $this->getSinetDbConnection();

            $rhdr = new RhdStudentRetrieval($sinetDb);
            $students = $rhdr->retrieveStudentsForFez();

            $this->log->info('New RHD students count: ' . count($students));

            if (count($students) > 0) {
                $db = FezDb::get();

                $rhdi = new FezAuthorInsertion($db);
                $inserted = $rhdi->insert($students);

                $this->log->info('RHD students inserted: ' . $inserted);
            }
        }

        $this->setState(BGP_FINISHED);
    }

    /**
     * Get a connection to the sinet load db
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    private function getSinetDbConnection()
    {
        // todo: move vars into config
        $params = array(
            'host'     => SINET_LOAD_DB_HOST,
            'username' => SINET_LOAD_DB_USER,
            'password' => SINET_LOAD_DB_PASSWD,
            'dbname'   => SINET_LOAD_DB_NAME,
            'charset'  => 'utf8'
        );

        try {
            $db = Zend_Db::factory(APP_SQL_DBTYPE, $params);
            $db->getConnection();
            return $db;
        } catch (Exception $ex) {
            $this->log->err($ex);
        }
    }
}
