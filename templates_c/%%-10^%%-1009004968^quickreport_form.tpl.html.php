<?php /* Smarty version 2.6.2, created on 2005-01-28 14:37:40
         compiled from quickreport_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'quickreport_form.tpl.html', 163, false),array('modifier', 'escape', 'quickreport_form.tpl.html', 228, false),)), $this); ?>

<?php if ($this->_tpl_vars['new_issue_id'] != "" && $_POST['report_stays'] != 'yes'): ?>
<table width="500" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <b>Thank you, the new issue was created successfully. Please choose 
            from one of the options below:</b>
            <ul>
              <li><a href="view.php?id=<?php echo $this->_tpl_vars['new_issue_id']; ?>
" class="link">Open the Issue Details Page</a></li>
              <li><a href="list.php" class="link">Open the Issue Listing Page</a></li>
              <?php if ($this->_tpl_vars['app_setup']['support_email'] == 'enabled' && $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer']): ?>
              <li><a href="emails.php" class="link">Open the Emails Listing Page</a></li>
              <?php endif; ?>
              <li><a href="new.php" class="link">Report a New Issue</a></li>
            </ul>
            <b>Otherwise, you will be automatically redirected to the Issue Details Page in 5 seconds.</b>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php echo '
<script language="JavaScript">
<!--
setTimeout(\'openDetailPage()\', 5000);
function openDetailPage()
{
'; ?>

    window.location.href = 'view.php?id=<?php echo $this->_tpl_vars['new_issue_id']; ?>
';
<?php echo '
}
//-->
</script>
'; ?>

<?php else: ?>
<?php if ($this->_tpl_vars['new_issue_id'] != ""): ?>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default" align="center">
			<font color="red">Last submitted at <?php echo $this->_tpl_vars['timenow']; ?>
 = <?php echo $this->_tpl_vars['last_submitted_via']; ?>
 - <?php echo $this->_tpl_vars['last_category']; ?>
 - <?php echo $this->_tpl_vars['last_subcategory']; ?>
 assigned to Issue ID -> <a href="view.php?id=<?php echo $this->_tpl_vars['new_issue_id']; ?>
" class="link"><?php echo $this->_tpl_vars['new_issue_id']; ?>
</a></font>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php endif; ?>

<?php echo '
<script language="JavaScript">
<!--
function ChangeSelected(sel,state){
    for(var i=0; i<sel.options.length; i++){
        sel.options[i].selected=state;
    }
}
function clearNameUser() {
  var element6 = getPageElement(\'custom_fields[6]\');
  element6.selectedIndex = 0;
}
var required_custom_fields = new Array();
var instant_required_custom_fields = new Array();
var custom_fields = new Array();
var instant_custom_fields = new Array();
function lookupHistory(x) {
	if (x == 6) {
		var searchVal = getPageElement(\'custom6\'); 
		window.open(\'list.php?cat=search&pagerRow=0&created_date%5BYear%5D=&created_date%5BMonth%5D=&created_date%5BDay%5D=&created_date%5Bfilter_type%5D=&updated_date%5BYear%5D=&updated_date%5BMonth%5D=&updated_date%5BDay%5D=&updated_date%5Bfilter_type%5D=&last_response_date%5BYear%5D=&last_response_date%5BMonth%5D=&last_response_date%5BDay%5D=&last_response_date%5Bfilter_type%5D=&first_response_date%5BYear%5D=&first_response_date%5BMonth%5D=&first_response_date%5BDay%5D=&first_response_date%5Bfilter_type%5D=&closed_date%5BYear%5D=&closed_date%5BMonth%5D=&closed_date%5BDay%5D=&closed_date%5Bfilter_type%5D=&show_authorized_issues=&show_notification_list_issues=&keywords=&projects=all&users=&status=&category=&priority=&custom_fields%5B6%5D=\'+(searchVal.value).replace(" ", "+"));		
	} else {
		if (x == 8) {
			var searchVal = getPageElement(\'custom8\'); 
			window.open(\'list.php?cat=search&pagerRow=0&created_date%5BYear%5D=&created_date%5BMonth%5D=&created_date%5BDay%5D=&created_date%5Bfilter_type%5D=&updated_date%5BYear%5D=&updated_date%5BMonth%5D=&updated_date%5BDay%5D=&updated_date%5Bfilter_type%5D=&last_response_date%5BYear%5D=&last_response_date%5BMonth%5D=&last_response_date%5BDay%5D=&last_response_date%5Bfilter_type%5D=&first_response_date%5BYear%5D=&first_response_date%5BMonth%5D=&first_response_date%5BDay%5D=&first_response_date%5Bfilter_type%5D=&closed_date%5BYear%5D=&closed_date%5BMonth%5D=&closed_date%5BDay%5D=&closed_date%5Bfilter_type%5D=&show_authorized_issues=&show_notification_list_issues=&keywords=&projects=all&users=&status=&category=&priority=&custom_fields%5B8%5D=\'+(searchVal.value).replace(" ", "+"));		
		} else {
			var searchVal = getPageElement(\'custom_fields[\'+x+\']\'); 
			window.open(\'list.php?cat=search&pagerRow=0&created_date%5BYear%5D=&created_date%5BMonth%5D=&created_date%5BDay%5D=&created_date%5Bfilter_type%5D=&updated_date%5BYear%5D=&updated_date%5BMonth%5D=&updated_date%5BDay%5D=&updated_date%5Bfilter_type%5D=&last_response_date%5BYear%5D=&last_response_date%5BMonth%5D=&last_response_date%5BDay%5D=&last_response_date%5Bfilter_type%5D=&first_response_date%5BYear%5D=&first_response_date%5BMonth%5D=&first_response_date%5BDay%5D=&first_response_date%5Bfilter_type%5D=&closed_date%5BYear%5D=&closed_date%5BMonth%5D=&closed_date%5BDay%5D=&closed_date%5Bfilter_type%5D=&show_authorized_issues=&show_notification_list_issues=&keywords=&projects=all&users=&status=&category=&priority=&custom_fields%5B\'+x+\'%5D=\'+(searchVal.value).replace(" ", "+"));		
		}
	}
}

/*function lookupHistory(x) {
	var searchVal = getPageElement(\'custom_fields[\'+x+\']\'); //.replace(\' \', \'+\'));
	window.open(\'list.php?cat=search&pagerRow=0&created_date%5BYear%5D=&created_date%5BMonth%5D=&created_date%5BDay%5D=&created_date%5Bfilter_type%5D=&updated_date%5BYear%5D=&updated_date%5BMonth%5D=&updated_date%5BDay%5D=&updated_date%5Bfilter_type%5D=&last_response_date%5BYear%5D=&last_response_date%5BMonth%5D=&last_response_date%5BDay%5D=&last_response_date%5Bfilter_type%5D=&first_response_date%5BYear%5D=&first_response_date%5BMonth%5D=&first_response_date%5BDay%5D=&first_response_date%5Bfilter_type%5D=&closed_date%5BYear%5D=&closed_date%5BMonth%5D=&closed_date%5BDay%5D=&closed_date%5Bfilter_type%5D=&show_authorized_issues=&show_notification_list_issues=&keywords=&projects=all&users=&status=&category=&priority=&custom_fields%5B\'+x+\'%5D=\'+(searchVal.value).replace(" ", "+"));		
}*/
function validateForm(f)
{
    checkRequiredCustomFields(f, required_custom_fields);
    if (hasSelected(f.category, -1)) {
        errors[errors.length] = new Option(\'Category\', \'category\');
    }
    if (hasSelected(f.subcategory, 0)) {
        errors[errors.length] = new Option(\'Subcategory\', \'subcategory\');
    }
'; ?>

<?php if ($this->_tpl_vars['prj_id'] == 2): ?>
<?php echo '
    if (!hasOneSelected(f, \'users\')) {
        errors[errors.length] = new Option(\'Assigned To\', \'users\');
    }
    if (!hasOneSelected(f, \'loggedby\')) {
        errors[errors.length] = new Option(\'Logged By\', \'loggedby\');
    }
'; ?>

<?php endif; ?>
<?php echo '

    '; ?>

    <?php echo '
}

function validateInstantForm(f)
{
    checkRequiredInstantCustomFields(f, instant_required_custom_fields);
'; ?>

<?php if ($this->_tpl_vars['prj_id'] == 2): ?>
<?php echo '
    if (!hasOneSelected(f, \'users\')) {
        errors[errors.length] = new Option(\'Assigned To\', \'users\');
    }
    if (!hasOneSelected(f, \'loggedby\')) {
        errors[errors.length] = new Option(\'Logged By\', \'loggedby\');
    }
'; ?>

<?php endif; ?>
<?php echo '
    '; ?>

    <?php echo '
}
//-->
</script>
'; ?>


<table width="80%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="report_form" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post" enctype="multipart/form-data"  onSubmit="javascript:return checkFormSubmission(this, 'validateForm');redirect(0);">
<input type="hidden" name="cat" value="report">
<input type="hidden" name="attached_emails" value="<?php echo $this->_tpl_vars['attached_emails']; ?>
">
  <tr valign="top">
	<td></td>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default" nowrap>
            <b>Create New Quick Issue Clicker</b>
          </td>
          <td align="right" class="default">
            (Current Team: <?php echo $this->_tpl_vars['current_project_name']; ?>
) 
          </td>
        </tr>

		<?php if (( $this->_tpl_vars['prj_id'] == 2 )): ?>
		<tr>
		 <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
			 <b>Logged By: *</b>
		 </td>
		 <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" nowrap>
			<select name="loggedby" class="default" tabindex="1" size="5" onChange="javascript:return selectOption(document.report_form, 'users', document.report_form.loggedby[document.report_form.loggedby.selectedIndex].value)">
			  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $_POST['loggedby']), $this);?>

			</select>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'loggedby')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		  </td>
		</tr>
		<tr>
		<td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
			 <b>Assigned To: *</b>
		 </td>
		 <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" nowrap>
			<select name="users" class="default" tabindex="2" size="5">
			  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $_POST['users']), $this);?>

			</select>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'users')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		  </td>
		</tr>

		<?php endif; ?>

        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['custom_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 1 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2 || ( ( $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 7 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3 ) && $this->_tpl_vars['prj_id'] == 2 )): ?>
			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3): ?>
			  <?php $this->assign('lastcustom', $_POST['custom_fields'][3]); ?>
			<?php else: ?>
			  <?php $this->assign('lastcustom', ""); ?>
			<?php endif; ?>
        <tr id="choicecustom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b><?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
:<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_report_form_required']): ?> *<?php endif; ?></b>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_report_form_required']): ?>
            <script language="JavaScript">
            <!--
            custom_fields[custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', '<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
');
            required_custom_fields[required_custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>'multiple'<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'combo'): ?>'combo'<?php else: ?>'whitespace'<?php endif; ?>);
            //-->
            </script>
            <?php endif; ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'text'): ?>
            <input class="default" type="text" id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" maxlength="255" size="50">
            <?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'textarea'): ?>
            <textarea name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" rows="10" cols="60"></textarea>
            <?php else: ?>
            <select <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple' && ( $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] != 7 && $this->_tpl_vars['prj_id'] == 2 )): ?><option value="-1">Please choose an option</option><?php endif; ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 1): ?>
              		<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => '2'), $this);?>

				<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3): ?>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['user_primary_campus_id']), $this);?>

				<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 7): ?>
              		<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => '34'), $this);?>

				<?php else: ?>
		            <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['lastcustom']), $this);?>

			    <?php endif; ?>
            </select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']); ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "custom_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'] != ""): ?>
            <span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
            <?php endif; ?>
          </td>
        </tr>
			<?php endif; ?>
        <?php endfor; endif; ?>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Category: *</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_category')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" nowrap>
            <select name="category" class="default" tabindex="1" onChange="redirect(this.options.selectedIndex);">
              <option value="-1">Please choose a category</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['cats'],'selected' => $_POST['category']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'category')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
		<tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Subcategory: *</b> 
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="subcategory" class="default" tabindex="1">
              <option value=0>Please choose a subcategory</option>
            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'subcategory')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
          </td>
        </tr>
<!--
        <?php if (isset($this->_sections['r'])) unset($this->_sections['r']);
$this->_sections['r']['name'] = 'r';
$this->_sections['r']['loop'] = is_array($_loop=$this->_tpl_vars['custom_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['r']['show'] = true;
$this->_sections['r']['max'] = $this->_sections['r']['loop'];
$this->_sections['r']['step'] = 1;
$this->_sections['r']['start'] = $this->_sections['r']['step'] > 0 ? 0 : $this->_sections['r']['loop']-1;
if ($this->_sections['r']['show']) {
    $this->_sections['r']['total'] = $this->_sections['r']['loop'];
    if ($this->_sections['r']['total'] == 0)
        $this->_sections['r']['show'] = false;
} else
    $this->_sections['r']['total'] = 0;
if ($this->_sections['r']['show']):

            for ($this->_sections['r']['index'] = $this->_sections['r']['start'], $this->_sections['r']['iteration'] = 1;
                 $this->_sections['r']['iteration'] <= $this->_sections['r']['total'];
                 $this->_sections['r']['index'] += $this->_sections['r']['step'], $this->_sections['r']['iteration']++):
$this->_sections['r']['rownum'] = $this->_sections['r']['iteration'];
$this->_sections['r']['index_prev'] = $this->_sections['r']['index'] - $this->_sections['r']['step'];
$this->_sections['r']['index_next'] = $this->_sections['r']['index'] + $this->_sections['r']['step'];
$this->_sections['r']['first']      = ($this->_sections['r']['iteration'] == 1);
$this->_sections['r']['last']       = ($this->_sections['r']['iteration'] == $this->_sections['r']['total']);
?>
			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 4 || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 5 || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 6 || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 7 || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 8): ?>
        <tr id="choicecustom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b><?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_title']; ?>
:<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_report_form_required'] || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 6): ?> *<?php endif; ?></b>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_report_form_required']): ?>
            <script language="JavaScript">
            <!--
            custom_fields[custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', '<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_title']; ?>
');
            required_custom_fields[required_custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>'multiple'<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'combo'): ?>'combo'<?php else: ?>'whitespace'<?php endif; ?>);
            //-->
<!--
            </script>
            <?php endif; ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 6 || $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 8): ?>
				<select size="10" class="default" multiple id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]" onChange="javascript:showSelectionsFill('report_form', 'custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]', <?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
);">
				  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['library_staff']), $this);?>

				</select>
			<input type="button" name="reset_nameOfUser" value="Select None" class="default" onClick="javascript:resetCustom(<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
);custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
.value='';">
			<br />
			<div class=default>Quick Lookup:</div><?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 6):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => "custom_fields[6]",'callbacks' => "new Array('showSelectionsFill(\'report_form\', \'custom_fields[6]\', 6)')")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  else:  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => "custom_fields[8]",'callbacks' => "new Array('showSelectionsFill(\'report_form\', \'custom_fields[8]\', 8)')")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
					<img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" align="absmiddle" onClick="javascript:lookupHistory(<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
)">
					<input type="button" value="Clear" class="default" name="btnClearCustom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
" onClick="javascript:custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
.value='';">
			<div class="default" id="selection_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]"></div>
			<input type="text" name="custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
" id="custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
" value="" size="60" class="default"><?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 6):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'custom6')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  else:  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'custom8')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
			<?php endif; ?>

            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'text'): ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] != 6 && $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] != 8): ?>
		            <input class="default" type="text" id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]" maxlength="255" size="50">
				<?php endif; ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id'] == 4): ?>
					<img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" align="absmiddle" onClick="javascript:lookupHistory(<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
)">
				<?php endif; ?>
            <?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'textarea'): ?>
            <textarea name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]" rows="10" cols="60"></textarea>
            <?php else: ?>
            <select <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] != 'multiple'): ?><option value="-1">Please choose an option</option><?php endif; ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['field_options']), $this);?>

            </select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_id']); ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_type'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "custom_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_description'] != ""): ?>
            <span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['r']['index']]['fld_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
            <?php endif; ?>
          </td>
        </tr>
			<?php endif; ?>
        <?php endfor; endif; ?>
-->

<!--
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Priority: *</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_priority')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="priority" class="default" tabindex="2">
              <option value="-1">Please choose a priority</option>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['priorities']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
              <option value="<?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id']; ?>
" <?php if ($this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id'] == $_POST['priority']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_title']; ?>
</option>
              <?php endfor; endif; ?>
            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'priority')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>

		<tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Issue Status: *</b> 
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="status" class="default" tabindex="1" onClick="javascript:toggleStatusChoices();">
              <option value="Open" <?php if (( $_POST['status'] == 'Open' )): ?> selected <?php endif; ?>>Open</option>
              <option value="Closed" <?php if (( $_POST['status'] == "" || $_POST['status'] == 'Closed' )): ?> selected <?php endif; ?>>Closed</option>
            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'Status')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> <span class="default"><em>Quick Issues are closed by default unless specified here</em></span>
          </td>
        </tr>
		<?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
		<tr id="statuschoicestitle" style="display:none">
	    <?php else: ?>        
		<tr>
		<?php endif; ?>
		  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Resolution Location: *</b> 
          </td>
		  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"> 
            <table>
              <tr>

				  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
					<select class="default" name="resolution_location">
					  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['resolution_locations']), $this);?>

					</select> &nbsp; <span class="default"><em>(Onsite means at the library branch of the issue location)</em></span>
				  </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Escalate to another team?: <?php if ($this->_tpl_vars['allow_unassigned_issues'] != 'yes'): ?>*<?php endif; ?></b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_assignment')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<br><br>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" valign="top">
            <select name="projects" size="5" class="default" tabindex="3">
			  <option label="Don't Escalate" value="<?php echo $this->_tpl_vars['prj_id']; ?>
" selected>Don't Escalate</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['project_list']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'projects')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
			<input type="button" name="reset_escalation" value="Select None" class="default" onClick="javascript:resetEscalation()">
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Assignment: <?php if ($this->_tpl_vars['allow_unassigned_issues'] != 'yes'): ?>*<?php endif; ?></b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_assignment')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="users[]" id="users" multiple size="10" class="default" tabindex="3">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['user_id']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'users')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<input type="button" name="reset_Assignment" value="Select None" class="default" onClick="javascript:resetAssignment();">
          </td>
        </tr>
-->
<!--
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Scheduled Release:</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_release')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="release" class="default" tabindex="4">
              <option value="0">un-scheduled</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['releases'],'selected' => $_POST['release']), $this);?>

            </select>
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Summary: *</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_summary')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="summary" class="default" size="50" tabindex="5">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'summary')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Description / Initial Requirements: *</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_description')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <textarea name="description" rows="10" tabindex="6" style="width: 97%"></textarea>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'description')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <nobr><b>Estimated Dev. Time:</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'report_estimated_dev_time')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>&nbsp;</nobr>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="estimated_dev_time" class="default" size="10" tabindex="7">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'estimated_dev_time')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> <span class="default">(in hours)</span>
          </td>
        </tr>


        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Attach Files:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <table width="100%" cellpadding="2" cellspacing="0" id="file_table">
              <tr>
                <td>
                  <input type="file" name="file[]" size="40" class="shortcut" tabindex="8" <?php if ($this->_tpl_vars['user_agent'] == 'ie'): ?>onChange="javascript:addFileRow();"<?php endif; ?>>
                </td>
              </tr>
            </table>
            <?php echo '
            <script language="">
            <!--
            if (document.all) {
                var fileTable = document.all[\'file_table\'];
            } else if (!document.all && document.getElementById) {
                var fileTable = document.getElementById(\'file_table\');
            }
            function addFileRow()
            {
                if (!fileTable) {
                    return;
                }
                rows = fileTable.rows.length;
                newRow = fileTable.insertRow(rows);
                cell = newRow.insertCell(0);
                cell.innerHTML = \'<input class="shortcut" size="40" type="file" name="file[]" onChange="javascript:addFileRow();">\';
            }
            //-->
            </script>
            '; ?>

<!--
          </td>
        </tr>
//-->
        <script language="JavaScript">
        <!--
        var prefs = new Array;
        <?php if (count($_from = (array)$this->_tpl_vars['user_prefs'])):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?>
        prefs[prefs.length] = new Option('<?php echo $this->_tpl_vars['key']; ?>
', '<?php echo $this->_tpl_vars['value']; ?>
');
        <?php endforeach; unset($_from); endif; ?>
        <?php echo '
        function toggleNotificationChoices()
        {
            var f = getForm(\'report_form\');
            var element = getPageElement(\'choices\');
            if (f.receive_notifications.checked) {
                element.style.display = \'\';
            } else {
                element.style.display = \'none\';
            }
        }
        function toggleStatusChoices()
        {
            var f = getForm(\'report_form\');
            var elementTitle = getPageElement(\'statuschoicestitle\');
            if (f.status.options[f.status.options.selectedIndex].text == \'Closed\') {
//                element.style.display = \'\';
                elementTitle.style.display = \'\';
            } else {
  //              element.style.display = \'none\';
                elementTitle.style.display = \'none\';
            }
        }
/*        function toggleHardwareChoices()
        {
            var f = getForm(\'report_form\'); 
            var element1 = getPageElement(\'choicecustom_fields[5]\');
            var element2 = getPageElement(\'choicecustom_fields[4]\');
            if (f.category.options[f.category.options.selectedIndex].text == \'Hardware\') {
                element1.style.display = \'\';
                element2.style.display = \'\';
            } else {
                element1.style.display = \'none\';
                element2.style.display = \'none\';
            }
        } */

        function getOptionValue(arr, value)
        {
            for (var i = 0; i < arr.length; i++) {
                if (arr[i].text == value) {
                    return arr[i].value;
                }
            }
        }
        function toggleChoice()
        {
            var f = getForm(\'report_form\');
            if (f.choice[0].checked) {
                for (var i = 0; ; i++) {
                    var field = getFormElement(f, \'actions[]\', i);
                    if (field == false) {
                        break;
                    } else {
                        var value = getOptionValue(prefs, field.value);
                        if (value == \'1\') {
                            field.checked = true;
                        } else {
                            field.checked = false;
                        }
                    }
                }
            } else {
                uncheckBoxes(f, \'actions[]\');
            }
        }
        function uncheckBoxes(f, field_name)
        {
            var i = 0;
            while (1) {
                var field = getFormElement(f, field_name, i);
                if (field != false) {
                    field.checked = false;
                } else {
                    break;
                }
                i++;
            }
        }
		function resetEscalation(){
			//cycle through the options collection, setting the selected attribute of each to false
//			for (i=0;i<document.report_form.projects.options.length;i++){
//				document.report_form.projects.options[i].selected=false;
				document.report_form.projects.selectedIndex=0;
//			}
		}

		function resetAssignment(){
			//cycle through the options collection, setting the selected attribute of each to false
			var element = getPageElement(\'users\');
			for (var i = 0; i < element.options.length; i++) {
				element.options[i].selected = false;
			}
		}

		function resetCustom(x){
			//cycle through the options collection, setting the selected attribute of each to false
			var element = getPageElement(\'custom_fields[\'+x+\']\');
			for (var i = 0; i < element.options.length; i++) {
				element.options[i].selected = false;
			}
		}
        //-->
        </script>
        '; ?>

<!--
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Notification Options:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="checkbox" name="receive_notifications" value="1" onClick="javascript:toggleNotificationChoices();"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'receive_notifications');toggleNotificationChoices();">Subscribe to notifications of changes on this new issue</a>
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            <table id="choices" style="display:none">
            <?php else: ?>
            <table>
            <?php endif; ?>
              <tr>
                <td><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/blank.gif" width="20" height="1" border="0"></td>
                <td class="default">
                  <input type="radio" name="choice" checked value="default" onClick="javascript:toggleChoice();"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('report_form', 'choice', 0);toggleChoice();">Use my default notification preferences</a><br />
                  <input type="radio" name="choice" value="custom" onClick="javascript:toggleChoice();"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('report_form', 'choice', 1);toggleChoice();">Choose a different notification preference</a>
                  <table>
                    <tr>
                      <td><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/blank.gif" width="20" height="1" border="0"></td>
                      <td class="default">
                        <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['user_prefs']['update']): ?>checked<?php endif; ?> value="update"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'actions[]', 0);">Issue is Updated</a><br />
                        <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['user_prefs']['closed']): ?>checked<?php endif; ?> value="closed"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'actions[]', 1);">Issue is Closed</a><br />
                        <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['user_prefs']['emails']): ?>checked<?php endif; ?> value="emails"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'actions[]', 3);">Emails are Associated</a><br />
                        <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['user_prefs']['files']): ?>checked<?php endif; ?> value="files"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'actions[]', 4);">Files are Attached</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
-->
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td width="10" nowrap class="default_white">
                  <nobr>
                  <input type="checkbox" name="report_stays" value="yes" checked tabindex="11"> <b><a id="white_link" class="white_link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'report_stays');">Keep Form Open</a></b>
                  </nobr>
                </td>
                <td width="100%" align="center">
                  <input class="button" type="submit" name="submit" value="Submit &amp; Open" tabindex="12">&nbsp;&nbsp;
                  <input class="button" type="submit" name="submit" value="Submit &amp; Close" tabindex="12">&nbsp;&nbsp;
                  <input class="button" type="reset" value="Reset" tabindex="13">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            <b>* Required fields</b>
          </td>
        </tr>
      </table>
    </td>
	</form>
	<form name="instant_report_form" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post" enctype="multipart/form-data" onSubmit="javascript:return checkFormSubmission(this, 'validateInstantForm');">

		  <td valign="top">
				  <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0" >
					<tr>
					  <td class="default" nowrap align="center">
						<img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/instantissue.jpg" align="absmiddle"> &nbsp;<b>Instant Issue Clicker</b>
					  </td>
					</tr>

			<?php if (( $this->_tpl_vars['prj_id'] == 2 )): ?>
			<tr>
			  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center">
				 <b><span class="default">Logged By: *</span></b><br />  
				<select name="loggedby" class="default" tabindex="1" size="5" onChange="javascript:return selectOption(document.instant_report_form, 'users', document.instant_report_form.loggedby[document.instant_report_form.loggedby.selectedIndex].value)">
				  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $_POST['loggedby']), $this);?>

				</select>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'loggedby')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			  </td>
			</tr>
			<tr>
			  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center">
				 <b><span class="default">Assigned To: *</span></b><br />  
				<select name="users" class="default" tabindex="2" size="5">
				  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $_POST['users']), $this);?>

				</select>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'users')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			  </td>
			</tr>

			<?php endif; ?>

        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['custom_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>

			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 1 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2 || ( $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2 && $this->_tpl_vars['prj_id'] == 3 ) || ( ( $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 7 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3 ) && $this->_tpl_vars['prj_id'] == 2 )): ?>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center">
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_report_form_required']): ?>
            <script language="JavaScript">
            <!--
            instant_custom_fields[instant_custom_fields.length] = new Option('instant_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', '<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
');
            instant_required_custom_fields[instant_required_custom_fields.length] = new Option('instant_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>'multiple'<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'combo'): ?>'combo'<?php else: ?>'whitespace'<?php endif; ?>);
            //-->
            </script>
            <?php endif; ?>
            <b><span class="default"><?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
:<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_report_form_required']): ?> *<?php endif; ?></span></b>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'] != ""): ?>			
            <br /><span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
            <?php endif; ?>&nbsp;
			<br>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'text'): ?>
            <input class="default" type="text" name="instant_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" maxlength="255" size="50">
            <?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'textarea'): ?>
            <textarea name="instant_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" rows="10" cols="60"></textarea>
            <?php else: ?>
            <select <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="instant_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple' && $this->_tpl_vars['prj_id'] != '2'): ?><option value="-1">Please choose an option</option><?php endif; ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 1): ?>
              		<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => '2'), $this);?>

				<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3): ?>
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['user_primary_campus_id']), $this);?>

				<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 7): ?>
              		<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => '34'), $this);?>

				<?php else: ?>
              		<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options']), $this);?>

				<?php endif; ?>
            </select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']); ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "instant_custom_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
		  <?php endif; ?>
        <?php endfor; endif; ?>


					<tr>					
						<td  align="middle" class="default" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><br>&nbsp;
							<input type="hidden" name="cat" value="instantreport">
							<input type="hidden" name="report_stays" value="yes">
					<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['instantlist']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>						
						<input type='submit' name='btnQuick' id="<?php echo $this->_tpl_vars['instantlist'][$this->_sections['i']['index']]['prsc_id']; ?>
"  value="<?php echo $this->_tpl_vars['instantlist'][$this->_sections['i']['index']]['prc_title']; ?>
 -- <?php echo $this->_tpl_vars['instantlist'][$this->_sections['i']['index']]['prsc_title']; ?>
"><br><br>&nbsp;
					<?php endfor; endif; ?>
						</td>
					</tr>
				</table>
			</td>
	  </tr>
	</form>
<?php echo '
<!-- BEGIN THE JTASK SCRIPT -->
<script>
<!--
var groups;
groups=document.report_form.category.options.length;
var group=new Array(groups)
//for (i=0; i<groups; i++) {
for (i=0; i<';  echo $this->_tpl_vars['maxG'];  echo '; i++) {
        group[i]=new Array()
}
group[0][0]=new Option(0,"Please choose a subcategory")

<!-- BEGIN THE JTASK PHP TAG -->
'; ?>

<?php echo $this->_tpl_vars['jtaskData']; ?>

<?php echo '
<!-- END OF THE JTASK PHP TAG -->
var temp=document.report_form.subcategory

function redirect(x) {
	if (x != null) {
		var y = 0
        for (m=temp.options.length-1;m>0;m--)
                temp.options[m]=null
        temp.options[y]=new Option(group[0][0].value,0)
//        temp.options[y]=new Option(group[0][0].value,group[0][0].value)
		y++
        for (var i in group[document.report_form.category.options[x].value]){
//           temp.options[y]=new Option(group[document.report_form.category.options[x].value][i].value,i)
           temp.options[y]=new Option(group[document.report_form.category.options[x].value][i].value,i)
		   y++
        }

        temp.options[0].selected=true
	}
}

//-->
</script>
<!-- END OF THE JTASK SCRIPT -->
'; ?>

<?php echo '
<script language="JavaScript">
<!-- CK - To initialise the list after a reload with the same selected option
redirect(document.report_form.category.options.selectedIndex)
-->
</script>
'; ?>



</table>



<?php echo '
<script language="JavaScript">
<!--
window.onload = setFocus;
//toggleStatusChoices(); // to initialise the resolution location
function setFocus()
{
//    document.report_form.category.focus();
}
//-->
</script>
'; ?>

<?php endif; ?>