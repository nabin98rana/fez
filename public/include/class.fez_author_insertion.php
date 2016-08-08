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
class FezAuthorInsertion
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
     * Checks if the passed in user's usernames already exist in
     * fez, returns a list of usernames for those already existing
     *
     * @param array $users
     *
     * @return array
     */
    public function getExistingUsers($users)
    {
        // OK so there is no IN limit, it's up to max allowed packet size
        // but lets just set one and paginate the query so we don't break stuff
        $inLimit = 200;

        // isolate the usernames of the users, using fancy PHP 5.5 function
        $staffUserNames = array_column($users, 'aut_org_username');
        $studentUserNames = array_column($users, 'aut_student_username');
        $userNames = array_merge($staffUserNames, $studentUserNames);

        // we'll push the ones which exist onto here
        $existingUserNames = [];

        // pull a page of userNames and grab existing ones,
        // adding them to the existingUsernames pile
        while (count($userNames) > 0) {
            $splice = array_splice($userNames, 0, $inLimit, []);

            if ($results = $this->listExistingAuthors($splice)) {
                // use the three dots of sorcery
                array_push($existingUserNames, ...$results);
            }
        }

        return $existingUserNames;
    }

    /**
     * Pulls out a complete list of RHD students, with the option
     * to paginate
     *
     * @param array $users
     *
     * @return mixed
     */
    private function listExistingAuthors($users)
    {
        $selectStaff = $this->db->select()
            ->from('fez_author', ['LOWER(aut_org_username)'])
            ->where('aut_org_username IN (?)', $users);

        $selectStudent = $this->db->select()
            ->from('fez_author', ['LOWER(aut_student_username)'])
            ->where('aut_student_username IN (?)', $users);

        $select = $this->db->select()
            ->union($selectStaff, $selectStudent);

        return $this->db->fetchCol($select);
    }


    /**
     * Insert non existing users
     *
     * @param array $users
     *
     * @return int  - number inserted
     */
    public function insertNew($users)
    {
        $existingUsers = $this->getExistingUsers($users);
        return $this->insert($users, $existingUsers);
    }

    /**
     * Insert users
     * 
     * Add users, ignoring any in the second argument
     *
     * @param array $users
     * @param array $existingUsernames  optional
     *
     * @return int  - number inserted
     */
    public function insert($users, $existingUsernames=null)
    {
        $successful = 0;

        foreach ($users as $user) {
            if (is_null($existingUsernames) ||
                (!in_array(strtolower($user['aut_org_username']), $existingUsernames) &&
                !in_array(strtolower($user['aut_student_username']), $existingUsernames))
            ) {
                if ($this->db->insert('fez_author', $user)) {
                    ++$successful;
                }
            }
        }

        return $successful;
    }
}
