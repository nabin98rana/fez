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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the Frquently Asked 
 * Questions sub-system.
 *
 * @version 1.0
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */

require_once(APP_INC_PATH . "class.misc.php");

class FAQ
{
	/**
	 * getAll
	 *
	 * Get all the FAQs (and their answers) for display on the public FAQ page.
	 */
	function getAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
				faq_id AS id,
				faq_cat_name AS category,
				faq_question AS question,
				faq_answer AS answer
			FROM
				" . APP_TABLE_PREFIX . "faq_questions
			LEFT JOIN
				" . APP_TABLE_PREFIX . "faq_categories
			ON
				faq_group = faq_cat_id
			ORDER BY
				faq_group ASC,
				faq_order ASC
			;
		";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		// Convert all line breaks to <br> tags.
		foreach ($res as &$row) {
			$row['answer'] = nl2br($row['answer']);
		}
		
		return $res;
	}
	
	
	
	function getCategoriesAll()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
				faq_cat_id AS category_id,
				faq_cat_name AS category_name,
				COUNT(faq_id) AS num_questions
			FROM
				" . APP_TABLE_PREFIX . "faq_categories
			
			LEFT JOIN
				" . APP_TABLE_PREFIX . "faq_questions
			ON
				faq_group = faq_cat_id
			GROUP BY
				faq_cat_id
			ORDER BY
				faq_cat_order ASC
			;
		";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}
	
	
	
	function getQuestionsForCategory($categoryID)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
				faq_id AS id,
				faq_question AS question
			FROM
				" . APP_TABLE_PREFIX . "faq_questions
			WHERE
				faq_group = " . $db->quote($categoryID) . "
			ORDER BY
				faq_order ASC	
			;
			";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}
	
	
	
	function getCategoryByID($categoryID)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
				faq_cat_id AS id,
				faq_cat_name AS name,
				faq_cat_order AS sequence
			FROM
				" . APP_TABLE_PREFIX . "faq_categories
			WHERE
				faq_cat_id = " . $db->quote($categoryID) . "
			;
			";
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}
	
	
	
	function addCategory()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "faq_categories
                 (
                    faq_cat_name,
                    faq_cat_order
                 ) VALUES (
                    " . $db->quote($_POST["name"]) . ",
                    " . $db->quote($_POST["order"], 'INTEGER') . "
                 );";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	
	
	
	function updateCategory()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "faq_categories
                 SET
                    faq_cat_name = " . $db->quote($_POST["name"]) . ",
                    faq_cat_order = " . $db->quote($_POST["order"], 'INTEGER') . "
                 WHERE
                    faq_cat_id=" . $db->quote($_POST["cat_id"], 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	
	
	
	function deleteCategory()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
					" . APP_TABLE_PREFIX . "faq_categories
				WHERE
					faq_cat_id = " . $db->quote($_POST["category"]) . ";";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}
	
	
	
	function getQuestionByID($questionID)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
			SELECT
				faq_id AS id,
				faq_group AS faq_group,
				faq_question AS question,
				faq_answer AS answer,
				faq_order AS sequence
			FROM
				" . APP_TABLE_PREFIX . "faq_questions
			WHERE
				faq_id = " . $db->quote($questionID) . "
			;
			";
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}
	
	
	
	function addQuestion()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$answer = nl2br($_POST["answer"]);
		$config = array('indent' => true,
						'output-xhtml' => true,
						'doctype' => omit,
						'show-body-only' => true,
						'wrap' => 0
						);
		
		$tidy = new tidy;
		$tidy->parseString($answer, $config, 'utf8');
		$tidy->cleanRepair();
		$answer = Misc::strip_breaks($tidy);
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "faq_questions
                 (
                    faq_group,
                    faq_question,
                    faq_answer,
                    faq_order
                 ) VALUES (
                    " . $db->quote($_POST["category"]) . ",
                    " . $db->quote($_POST["question"]) . ",
                    " . $db->quote($answer) . ",
                    " . $db->quote($_POST["order"], 'INTEGER') . "
                 );";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	
	
	
	function updateQuestion()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$answer = nl2br($_POST["answer"]);
		$config = array('indent' => true,
						'output-xhtml' => true,
						'doctype' => omit,
						'show-body-only' => true,
						'wrap' => 0
						);
		
		$tidy = new tidy;
		$tidy->parseString($answer, $config, 'utf8');
		$tidy->cleanRepair();
		$answer = Misc::strip_breaks($tidy);
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "faq_questions
                 SET
                    faq_group = " . $db->quote($_POST["category"]) . ",
                    faq_question = " . $db->quote($_POST["question"]) . ",
                    faq_answer = " . $db->quote($answer) . ",
                    faq_order = " . $db->quote($_POST["order"], 'INTEGER') . "
                 WHERE
                    faq_id=" . $db->quote($_POST["question_id"], 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}
	
	
	
	function deleteQuestion()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
					" . APP_TABLE_PREFIX . "faq_questions
				WHERE
					faq_id = " . $db->quote($_POST["question"]) . ";";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}

}
