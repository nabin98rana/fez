<?php

class TestEvents
{
	var $myVar = 0;

	function Test() {}
}

require_once('../../najax.php');

$serverEvents = array();

class ServerObserver extends NAJAX_Observer
{
	function updateObserver($event, $arg)
	{
		global $serverEvents;

		if ($arg == null) {

			$arg = "null";
		}

		$serverEvents[] = $event . ' => ' . str_replace("\n", "\n" . str_repeat(' ', strlen($event) + 4), var_export($arg, true));

		if ($event == 'dispatchLeave') {

			$arg['response']['output'] = "<strong>Server Events:</strong>\n\n" . join('<hr />', $serverEvents);
		}
	}
}

NAJAX_Server::addObserver(new ServerObserver());

if (NAJAX_Server::runServer()) {

	exit;
}

?>
<?= NAJAX_Utilities::header('../..') ?>

<script type="text/javascript">

var obj = <?= NAJAX_Client::register(new TestEvents()) ?>;

obj.test();

document.write('<pre>');
document.write(obj.fetchOutput());
document.write('</pre>');

</script>