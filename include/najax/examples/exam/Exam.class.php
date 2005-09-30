<?php

require_once('questions.inc.php');

class Exam
{
	var $questions;

	function loadQuestions()
	{
		global $examQuestions;

		if ( ! isset($this->questions)) {

			$this->questions = array();

			foreach ($examQuestions as $question) {

				$this->questions[] = $question[0];
			}

			return true;
		}

		return false;
	}

	function cleanAnswers()
	{
		@session_start();

		$_SESSION['examData'] = array();
	}

	function getAnswers($id)
	{
		global $examQuestions;

		if ( ! array_key_exists($id, $examQuestions)) {

			return null;
		}

		$answers = array();

		for ($iterator = 1; $iterator < sizeof($examQuestions[$id]) - 1; $iterator ++) {

			$answers[] = $examQuestions[$id][$iterator];
		}

		@session_start();

		$result = array();

		if (array_key_exists($id, $_SESSION['examData'])) {

			$result['answer'] =& $_SESSION['examData'][$id];

		} else {

			$result['answer'] = -1;
		}

		$result['data'] =& $answers;

		return $result;
	}

	function submitAnswer($question, $id)
	{
		@session_start();

		if ( ! array_key_exists('examData', $_SESSION)) {

			$_SESSION['examData'] = array();
		}

		if ( ! array_key_exists($question, $_SESSION['examData'])) {

			$_SESSION['examData'][$question] = $id;

			return true;
		}

		return false;
	}

	function fetchResults()
	{
		global $examQuestions;

		@session_start();

		if (array_key_exists('examData', $_SESSION)) {

			if (sizeof($_SESSION['examData']) == sizeof($examQuestions)) {

				$result = array();

				$result['data'] =& $examQuestions;

				$result['answers'] =& $_SESSION['examData'];

				return $result;
			}
		}

		return null;
	}

	function najaxGetMeta()
	{
		NAJAX_Client::privateMethods($this, array('loadQuestions', 'cleanAnswers'));

		NAJAX_Client::mapMethods($this, array('getAnswers', 'submitAnswer', 'fetchResults'));
	}
}

?>