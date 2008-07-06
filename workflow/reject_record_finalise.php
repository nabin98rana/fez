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
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

$this->getRecordObject();

$this->rec_obj->getObjectAdminMD();
$usrDetails = User::getDetailsByID($this->rec_obj->depositor);

$inReview = Status::getID("In Creation");
$this->rec_obj->setStatusId($inReview);
$this->rec_obj->updateFezMD_User("usr_id", $this->rec_obj->depositor);

$mail = new Mail_API;
$mail->setTextBody(stripslashes($_REQUEST['email_body']));
$subject = '['.APP_NAME.'] - Your Record has been rejected';
$from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
$to = $usrDetails['usr_email'];
$mail->send($from, $to, $subject, false);

History::addHistory($this->rec_obj->getPid(), null, '', '', true, 'Record Rejected');
?>