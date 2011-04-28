<?php

//include_once("../config.inc.php");
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
//require_once("../filemanager/inc/config.php");
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."filemanager".DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."config.php");
require_once(CLASS_MANAGER);
define('URL_AJAX_FILE_MANAGER', CONFIG_URL_HOME);
require_once(CLASS_SESSION_ACTION);
$sessionAction = new SessionAction();
require_once(DIR_AJAX_INC . "class.manager.php");

include_once(APP_INC_PATH . "class.auth.php");
Auth::checkAuthentication(APP_SESSION);

$path = $_GET['path'];
$canViewFolders = @$_SESSION['canViewFolders'];

if( is_array($canViewFolders) )
{
    $PathOK = false;
    foreach ( $canViewFolders as $folder )
    {
        if( strpos($path, strtolower($folder)) )
        {
            $PathOK = true;
        }
    }
    
    if( !$PathOK )
    {
        $_GET['path'] = CONFIG_SYS_DEFAULT_PATH;
        
    }
}

$manager = new manager();
$manager->setSessionAction($sessionAction);
$fileList = $manager->getFileList();

//print_r($fileList);
$numFiles = count($fileList);
$alertMsg = ' We cannot allow this file due to its filename, please rename it. Please check that the new name conforms to the following:<br/>';
$alertMsg = $alertMsg.' - only upper or lowercase alphanumeric characters or underscores (a-z, A-Z, _ and 0-9 only, NO SPACES)<br/>';
$alertMsg = $alertMsg.' - with only numbers and lowercase characters in the file extension,<br/>';
$alertMsg = $alertMsg.' - under 45 characters,<br/>';
$alertMsg = $alertMsg.' - with only one file extension (one period (.) character) and <br/>';
$alertMsg = $alertMsg.' - starting with a letter. Eg "s12345678_phd_thesis.pdf"';
for ($x = 0; $x < $numFiles; $x++) {
    $regexp = '/^[a-zA-Z][a-zA-Z0-9_]*[\.][a-z0-9]+$/';
    if (!preg_match($regexp, $fileList[$x]['name']) || strlen($fileList[$x][$name]) > 45) {
        $fileList[$x]['is_readable'] = 0;
        $fileList[$x]['name'] = "<span style='color:red;'>".$fileList[$x]['name'] . "</span>" . $alertMsg;
    }
}


$folderInfo = $manager->getFolderInfo();
$rel_url = APP_RELATIVE_URL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="Logan Cai" />
<meta name="website" content="http://www.phpletter.com" /> 

<script type="text/javascript" src="<?php echo $rel_url; ?>js/ajaxfilemanager_compressed.js"></script>

<script type="text/javascript">
function enableEditable()
{
		$("#fileList tr[@id^=row] td.left").editable("<?php echo appendQueryString(CONFIG_URL_SAVE_NAME, makeQueryString(array('path'))); ?>",
		 {
					 submit    : 'Save',
					 width	   : '150',
					 height    : '14',
					 loadtype  : 'POST',
					 event	   :  'dblclick',
					 indicator : "<img src='<?php echo $rel_url; ?>theme/<?php $config_theme_name; ?>images/loading.gif'>",
					 tooltip   : '<?php echo TIP_DOC_RENAME; ?>'
		 }
		 
		 );	 	
}
	var tb_pathToImage = "theme/<?php echo CONFIG_THEME_NAME; ?><?php echo $rel_url; ?>images/loadingAnimation.gif";
	var urlPreview = '<?php echo appendQueryString(CONFIG_URL_PREVIEW, makeQueryString(array('path'))); ?>';
	var msgNotPreview = '<?php echo PREVIEW_NOT_PREVIEW; ?>';
	var urlCut = '<?php echo appendQueryString(CONFIG_URL_CUT, makeQueryString(array('path'))); ?>';
	var urlCopy = '<?php echo appendQueryString(CONFIG_URL_COPY, makeQueryString(array('path'))); ?>';
	var urlPaste = '<?php echo appendQueryString(CONFIG_URL_PASTE, makeQueryString(array('path'))); ?>';
	var warningCutPaste = 'Are you sure to move selected documents to current folder?';
	var warningCopyPaste = 'Are you sure to copy selected documents to current folder?';
	var urlDelete = '<?php echo appendQueryString(CONFIG_URL_DELETE, makeQueryString(array('path'))); ?>';
	var action = '<?php echo $sessionAction->getAction(); ?>';
	var numFiles = <?php echo $sessionAction->count(); ?>;
	var urlRename = '<?php echo appendQueryString(CONFIG_URL_SAVE_NAME, makeQueryString(array('path'))); ?>';
	var warningCloseWindow = 'Are you sure to close the window?';
	var numRows = <?php echo (($folderInfo['subdir']  + $folderInfo['file']) + 1); ?>; 
	var urlImgPreview = '<?php echo appendQueryString(CONFIG_URL_IMAGE_PREVIEW, makeQueryString(array('path'))); ?>';
	var urlDownload = '<?php echo appendQueryString(CONFIG_URL_DOWNLOAD, makeQueryString(array('path'))); ?>';
	var urlTextEditor = '<?php echo appendQueryString(CONFIG_URL_TEXT_EDITOR, makeQueryString(array('path'))); ?>';
	var wordCloseWindow = 'Close';
	var urlImageEditor = '<?php echo appendQueryString(CONFIG_URL_IMAGE_EDITOR, makeQueryString(array('path'))); ?>';
	var editableExts = '<?php echo implode(',', getValidTextEditorExts()); ?>';
	var wordPreviewClick = 'Click here to preview it.';
	var supporedPreviewExts = 'gif,bmp,txt,jpg,png,tif,html,htm,js,css,xml,xsl,dtd';
	var elementId = <?php  echo (!empty($_GET['elementId'])?"'" . $_GET['elementId'] . "'":'null'); ?>;
$(document).ready(
	function()
	{
		
		//tableRuler('#tableList tbody tr');
		$('#edit').hide();	
		enableEditable();
		initAction();
		enableDownload();
		enablePopup();
		
	} );

	
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $rel_url; ?>css/filemanager_css.php" />
<title>Ajax File Manager</title>
</head>
<body>
	<div id="container-filemanager">
		<div id="leftCol-filemanager">
			<form action="<?php echo $rel_url; ?>popup.php" method="POST" name="formAction" id="formAction">
			<input type="hidden" name="cat" value="file_manager" />
			<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>" />
			<input type="hidden" name="action_value" value="" id="action_value" />
			<input type="hidden" name="currentFolderPath"  value="<?php echo $folderInfo['path']; ?>" />
			<div id="body"><?php include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ajax_get_file_list.php'); ?></div>
			<div id="footer">
			<div id="divFormFile">
		      <input type="submit" class="button" id="upload" value="Upload to Fez" />
			</div>
            </div>
			</form>
			
		</div>
		<div id="rightCol">
			<fieldset id="fileFieldSet" style="display:none" >
				<legend>File Information:</legend>
				<table cellpadding="0" cellspacing="0" class="tableSummary" id="fileInfo">
					<tbody>
						<tr>
							<th>Name:</th>
							<td colspan="3" id="fileName"></td>
						</tr>
						<tr>
							<th>Created At:</th>
							<td colspan="3" id="fileCtime"></td>

						</tr>
						<tr>
							<th>Modified At:</th>
							<td colspan="3" id="fileMtime"></td>
						</tr>
						<tr>
							<th>File Size:</th>
							<td id="fileSize"></td>
							<th>File Type:</th>
							<td id="fileType"></td>
						</tr>
						<tr>
							<th>Writable?</th>
							<td id="fileWritable"><span class="flagYes">&nbsp;</span></td>
							<th>Readable?</th>
							<td id="fileReadable"><span class="flagNo">&nbsp;</span></td>
						</tr>
					</tbody>
				</table>
			</fieldset>
			<fieldset id="folderFieldSet" >
				<legend>Folder Information</legend>
				<table cellpadding="0" cellspacing="0" class="tableSummary" id="folderInfo">
					<tbody>
						<tr>
							<th>Path:</th>
							<td colspan="3" id="folderPath"><?php echo transformFilePath($folderInfo['path']); ?></td>
						</tr>
						<tr>
							<th>Created At:</th>
							<td colspan="3" id="folderCtime"><?php echo date('d/M/Y H:i:s',$folderInfo['ctime']); ?></td>

						</tr>
						<tr>
							<th>Modified At:</th>
							<td colspan="3" id="folderMtime"><?php echo date('d/M/Y H:i:s',$folderInfo['mtime']); ?></td>
						</tr>
						<tr>
							<th>Subfolders:</th>
							<td id="folderSubdir"><?php echo $folderInfo['subdir']; ?></td>
							<th>Files:</th>
							<td id="folderFile"><?php echo $folderInfo['file']; ?></td>
						</tr>
						<tr>
							<th>Writable?</th>
							<td id="folderWritable"><span class="<?php echo ($folderInfo['is_readable']?'flagYes':'flagNo'); ?>">&nbsp;</span></td>
							<th>Readable?</th>
							<td id="folderReadable"><span class="<?php echo ($folderInfo['is_writable']?'flagYes':'flagNo'); ?>">&nbsp;</span></td>
						</tr>


					</tbody>
				</table>
			</fieldset>
			
			
		</div>
	</div>
	<div class="clear"></div>
</body>
</html>
