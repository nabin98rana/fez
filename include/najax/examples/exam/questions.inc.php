<?php

$examQuestions = array();

$examQuestions[] = array(
'What function would you use to tell the browser the content type of the data being output?',
'imagejpeg()',
'imageoutput()',
'flush()',
'header()',
'imageload()',
3);

$examQuestions[] = array(
'What parameters does the func_get_args() function take?',
'0: It doesn\'t take any parameters',
'1: name of function to check',
'2: name of function to check, boolean "count optional arguments"',
0);

$examQuestions[] = array(
'What does the array_shift() function do?',
'Add an element to an array',
'Removes an element from an array',
'Shifts all elements towards the back of the array',
'Switches array keys and values',
'Clears the array',
1);

$examQuestions[] = array(
'What function would you use to delete a file?',
'unlink()',
'delete()',
'fdelete()',
'file_delete()',
0);

$examQuestions[] = array(
'What is the difference between exec() and pcntl_exec()?',
'Nothing, they are the same',
'pcntl_exec() forks a new process',
'pcntl_exec() can only be called from a child process',
'None of the above',
3);

$examQuestions[] = array(
'If $string is "Hello, world!", how long would the output of sha1($string) be?',
'It varies',
'16 characters',
'20 characters',
'24 characters',
'32 characters',
'40 characters',
5);

$examQuestions[] = array(
'If the input of chr() is $a and the output is $b, what function would take input $b and produce output $a?',
'chr()',
'rch()',
'ord()',
'strrev()',
'chr(chr())',
'chrrev()',
2);

$examQuestions[] = array(
'If $a is "Hello, world!", is this statement true or false: md5($a) === md5($a)',
'True',
'False',
0);

$examQuestions[] = array(
'Which function returns true when magic quotes are turned on?',
'get_magic_quotes()',
'magic_quotes_get()',
'magic_quotes()',
'get_magic_quotes_gpc()',
'get_quotes()',
3);

$examQuestions[] = array(
'The functions get_required_files() and get_included_files() are identical, true or false?',
'True',
'False',
0);

$examQuestions[] = array(
'If $arr was an array of ten string elements with specific keys, what would array_values(ksort($arr)) do?',
'Create a new array of just the values, then sort by the keys',
'Create a new array of just the values, then ignore the sort as there are no keys',
'Sort the array by key, then return a new array with just the values',
'Trigger a warning',
'None of the above',
3);

$examQuestions[] = array(
'What is the return value of array_unique()?',
'Boolean',
'Integer',
'Array',
'It varies',
'None of the above',
2);

?>