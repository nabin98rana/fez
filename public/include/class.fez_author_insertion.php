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
// | Authors: |
// +----------------------------------------------------------------------+
//
//


/**
 * Class RhdStudentInsertion
 *
 * Adds RHD students to fez
 */
class RhdStudentInsertion
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $db;

    /**
     * RhdStudentInsertion constructor.
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    function __construct(Zend_Db_Adapter_Abstract $db)
    {
        $this->db = $db;
    }

    /**
     * Insert rhd students
     *
     * @param Array $students
     *
     * @return int  - number inserted
     */
    public function insertStudents($students)
    {
        $successful = 0;
        
        foreach ($students as $user) {
            if ($this->db->insert('fez_author', $user)) {
                ++$successful;
            }
        }

        return $successful;
    }
}
