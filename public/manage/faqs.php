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

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.faq.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
$tpl->assign("active_nav", "admin");

$tpl->assign("type", "faqs");

if ($isAdministrator) {

    if (@$_GET["cat"] == "category-edit") {
        $catInfo = FAQ::getCategoryByID(@$_GET["id"]);
        $questions = FAQ::getQuestionsForCategory(@$_GET["id"]);
        $tpl->assign("cat", $catInfo);
        $tpl->assign("questions", $questions);
        $tpl->assign("mode", 'category-edit');

    } elseif (@$_GET["cat"] == "category-add") {
        $tpl->assign("mode", 'category-add');

    } elseif (@$_GET["cat"] == "category-update") {

        if (@$_POST["action"] == 'save') {
            FAQ::updateCategory();
            $tpl->assign("mode", 'cat-saved');
        } elseif (@$_POST["action"] == 'add') {
            FAQ::addCategory();
            $tpl->assign("mode", 'cat-added');
        } elseif (@$_POST["action"] == 'delete') {
            FAQ::deleteCategory();
            $tpl->assign("mode", 'cat-deleted');
        }

        $categories = FAQ::getCategoriesAll();
        $tpl->assign("categories", $categories);

    } elseif (@$_GET["cat"] == "question-add") {
        $categories = FAQ::getCategoriesAll();
        $tpl->assign("cat_id", @$_GET["id"]);
        $tpl->assign("categories", $categories);
        $tpl->assign("mode", 'question-add');

    } elseif (@$_GET["cat"] == "question-edit") {
        $questionInfo = FAQ::getQuestionByID(@$_GET["id"]);
        $categories = FAQ::getCategoriesAll();
        $tpl->assign("question", $questionInfo);
        $tpl->assign("categories", $categories);
        $tpl->assign("mode", 'question-edit');

    } elseif (@$_GET["cat"] == "question-update") {

        if (@$_POST["action"] == 'save') {
            FAQ::updateQuestion();
            $tpl->assign("msg", 'question-saved');
        } elseif (@$_POST["action"] == 'add') {
            FAQ::addQuestion();
            $tpl->assign("msg", 'question-added');
        } elseif (@$_POST["action"] == 'delete') {
            FAQ::deleteQuestion();
            $tpl->assign("msg", 'question-deleted');
        }

        $catInfo = FAQ::getCategoryByID(@$_POST["category"]);
        $questions = FAQ::getQuestionsForCategory(@$_POST["category"]);
        $tpl->assign("cat", $catInfo);
        $tpl->assign("questions", $questions);
        $tpl->assign("mode", 'category-edit');

    } else {
  		$categories = FAQ::getCategoriesAll();
      $zf = new Fez_Filter_RichTextHtmlpurify();
      $categories = $zf->filter($categories);
      $tpl->assign("categories", $categories);
	}

} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>