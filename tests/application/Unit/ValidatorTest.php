<?php

require_once '../../../public/config.inc.php';

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function testUsername()
	{
		$userVld = new Fez_Validate_Username();
		
		$goodUsername = 'little-Johhny@alibrary.some.whr';
		$valid = $userVld->isValid($goodUsername);
		$this->assertTrue($valid);
		
		$goodIrishUsername = "pattyO'Malley@alibrary.some.whr";
		$valid = $userVld->isValid($goodUsername);
		$this->assertTrue($valid);
		
		$hasBadChars = 'bad!!Johh+ny$Badu$ern?me';
		$valid = $userVld->isValid($hasBadChars);
		$this->assertFalse($valid);
		
		$isTooLong = 'kkgkyfgk252345gjhj2k345fgjh23g4f7654gv5jh23g4f5jh23g4f52345jhgfdsgh';
		$valid = $userVld->isValid($isTooLong);
		$this->assertFalse($valid);
		
		$isBlank = '';
		$valid = $userVld->isValid($isBlank);
		$this->assertFalse($valid);
		
		$isJustSpaces = '    ';
		$valid = $userVld->isValid($isJustSpaces);
		$this->assertFalse($valid);
	}
}