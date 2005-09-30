<?php

class Calculator
{
	var $result;

	function Calculator() { $this->result = 0; }

	function Add($a) { $this->result += $a; return true; }
	function Sub($a) { $this->result -= $a; return true; }
	function Mul($a) { $this->result *= $a; return true; }
	function Div($a) { if ($a == 0) return false; $this->result /= $a; return true; }

	function Clear() { $this->result = 0; return true; }
}

define('NAJAX_AUTOHANDLE', true);

require_once('../../najax.php');

?>
<?= NAJAX_Utilities::header('../..'); ?>

<input type="text" id="operationValue" value="1" style="font: normal 0.8em tahoma, verdana, arial, serif; width: 10em;" />
&nbsp;&nbsp;&nbsp;
<span id="operationResult" style="font: normal 0.8em tahoma, verdana, arial, serif;"></span>

<br />

<button onclick="add()" style="font: normal 1em tahoma, verdana, arial, serif; width: 2em;">+</button>
<button onclick="sub()" style="font: normal 1em tahoma, verdana, arial, serif; width: 2em;">-</button>
<button onclick="mul()" style="font: normal 1em tahoma, verdana, arial, serif; width: 2em;">*</button>
<button onclick="div()" style="font: normal 1em tahoma, verdana, arial, serif; width: 2em;">/</button>

<br />

<button onclick="clearResult()" style="font: normal 0.8em tahoma, verdana, arial, serif; width: 10em;">Clear</button>

<script type="text/javascript">

var calc = <?= NAJAX_Client::register(new Calculator()) ?>;

var operationValue = document.getElementById('operationValue');

var operationResult = document.getElementById('operationResult');

function getValue() { return parseInt(operationValue.value); }

function update() { operationResult.innerHTML = 'Result: <strong>' + calc.result + '</strong>'; }

function add() { if (calc.add(getValue())) update(); }
function sub() { if (calc.sub(getValue())) update(); }
function mul() { if (calc.mul(getValue())) update(); }
function div() { if (calc.div(getValue())) update(); }

function clearResult() { if (calc.clear()) update(); }

update();

</script>