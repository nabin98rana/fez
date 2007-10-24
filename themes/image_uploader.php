<?php

include_once('../config.inc.php');
$username = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($username);
if (!$isAdministrator) {
	echo "not allowed";
	Error_Handler::logError("not allowed",__FILE__,__LINE__);
	exit;
}


$theme_base = $_REQUEST['themeBase'];

$safe_name = strrev(substr(preg_replace('/[^\w\d\.]/', '_', strrev(basename($_FILES['ifile']['name']))), 0, 25));

copy($_FILES['ifile']['tmp_name'], APP_PATH.'themes/'.$theme_base.'/images/'.$safe_name);
  
?>
<html><body>
	
<div>Your file was uploaded as <?php echo 'themes/'.$theme_base.'/images/'.$safe_name; ?></div>

<div><a href="javascript:void(null)" onclick="javascript:window.close();" >Close</a>	</div>
	
</body></html>