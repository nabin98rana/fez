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
 * Class RhdStudentRetrieval
 *
 * Finds new RHD students, and retrieves them
 */
class RhdStudentRetrieval
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $sinetDb;

    /**
     * RhdStudentRetrieval constructor.
     *
     * @param Zend_Db_Adapter_Abstract $sinetDb
     */
    function __construct(Zend_Db_Adapter_Abstract $sinetDb)
    {
        $this->sinetDb = $sinetDb;
    }

    /**
     * Get list of rhd students, formatted suitably for inserting into
     * fez author
     *
     * @param int $limit  optional
     * @param int $offset optional
     *
     * @return Array
     */
    public function retrieveStudentsForFez($limit = null, $offset = null)
    {
        // get a list of students
        $students = $this->rhdRetrieveStudents($limit, $offset);

        $userInsert = [];

        foreach ($students as $user) {
            $userInsert[] = [
                'aut_org_username'   => lcfirst($user->USERNAME),
                'aut_org_student_id' => $user->EMPLID,
                'aut_display_name'   => $user->LNAME . ', ' . $user->FNAME, // meh...
                'aut_fname'          => $user->FNAME,
                'aut_lname'          => $user->LNAME,
                'aut_title'          => $user->TITLE,
                'aut_created_date'   => $this->client->raw('NOW()')
            ];
        }

        return $userInsert;
    }


    /**
     * Pulls out a complete list of RHD students, with the option
     * to paginate
     *
     * @param null $limit
     * @param null $offset - limit is required for this to function
     *
     * @return mixed
     */
    private function rhdRetrieveStudents($limit = null, $offset = null)
    {
        $select = $this->sinetDb->select();

        $select->from(['pulc' => 'PS_UQ_LIB_CLASS'], [])
            ->join(
                ['pulp' => 'PS_UQ_LIB_PERS'], 'pulc.EMPLID = pulp.EMPLID',
                [
                    'pulp.EMPLID',
                    'pulp.FIRST_NAME' => 'FNAME',
                    'pulp.LAST_NAME'  => 'LNAME',
                    'pulp.OPRID'      => 'USERNAME',
                    'pulp.TITLE'
                ]
            )
            ->where('pulc.ACAD_CAREER = ?', 'PGRS')
            ->where('pulc.SUBJECT = ?', 'RSCH')
            ->group('pulp.EMPLID');

        if ($limit) {
            if ($offset) {
                $select->limit($limit, $offset);
            } else {
                $select->limit($limit, 0);
            }
        }

        return $select->fetchAll();
    }
}
