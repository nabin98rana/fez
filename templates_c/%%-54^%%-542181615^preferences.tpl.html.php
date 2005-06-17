<?php /* Smarty version 2.6.2, created on 2004-07-01 15:23:41
         compiled from preferences.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'preferences.tpl.html', 138, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => 'Preferences')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "navigation.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<script language="JavaScript">
<!--
function validateName(f)
{
    if (isWhitespace(f.full_name.value)) {
        alert(\'Please enter your full name.\');
        selectField(f, \'full_name\');
        return false;
    }
    return true;
}
function validateEmail(f)
{
    if (!isEmail(f.email.value)) {
        alert(\'Please enter a valid email address.\');
        selectField(f, \'email\');
        return false;
    }
    return true;
}
function validateAccount(f)
{
    return true;
}
//-->
</script>
'; ?>

<table width="80%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <b>User Details</b>
          </td>
          <td align="right">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'preferences')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <form name="update_name_form" onSubmit="javascript:return validateName(this);" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post">
        <input type="hidden" name="cat" value="update_name">
        <?php if ($this->_tpl_vars['update_name_result']): ?>
        <tr>
          <td colspan="2" class="error" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <?php if ($this->_tpl_vars['update_name_result'] == -1): ?>
            <b>An error occurred while trying to run your query.</b>
            <?php elseif ($this->_tpl_vars['update_name_result'] == 1): ?>
            <b>Thank you, your full name was updated successfully.</b>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Full Name:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="full_name" size="40" class="default" value="<?php echo $this->_tpl_vars['current_full_name']; ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'full_name')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <input class="button" type="submit" value="Update Full Name">
            <input class="button" type="reset" value="Reset">
          </td>
        </tr>
        </form>
        <form name="update_email_form" onSubmit="javascript:return validateEmail(this);" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post">
        <input type="hidden" name="cat" value="update_email">
        <?php if ($this->_tpl_vars['update_email_result']): ?>
        <tr>
          <td colspan="2" class="error" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <?php if ($this->_tpl_vars['update_email_result'] == -1): ?>
            <b>An error occurred while trying to run your query.</b>
            <?php elseif ($this->_tpl_vars['update_email_result'] == 1): ?>
            <b>Thank you, your email address was updated successfully.</b>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Email Address:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="email" size="40" class="default" value="<?php echo $this->_tpl_vars['current_email']; ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'email')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <input class="button" type="submit" value="Update Email Address">
            <input class="button" type="reset" value="Reset">
          </td>
        </tr>
        </form>
      </table>
    </td>
  </tr>
</table>
<br />
<table width="80%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <form name="account_prefs_form" onSubmit="javascript:return validateAccount(this);" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post" enctype="multipart/form-data">
        <input type="hidden" name="cat" value="update_account">
        <tr>
          <td class="default">
            <b>Account Preferences</b>
          </td>
          <td align="right">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'preferences')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['update_account_result']): ?>
        <tr>
          <td colspan="2" class="error" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <?php if ($this->_tpl_vars['update_account_result'] == -1): ?>
            <b>An error occurred while trying to run your query.</b>
            <?php elseif ($this->_tpl_vars['update_account_result'] == 1): ?>
            <b>Thank you, your account preferences were updated successfully.</b>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Timezone:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="timezone">
              <?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['zones'],'output' => $this->_tpl_vars['zones'],'selected' => $this->_tpl_vars['user_prefs']['timezone']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => 'timezone')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Automatically close confirmation popup windows ?</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="radio" name="close_popup_windows" <?php if ($this->_tpl_vars['user_prefs']['close_popup_windows'] == '1'): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'close_popup_windows', 0);">Yes</a>&nbsp;&nbsp;
            <input type="radio" name="close_popup_windows" <?php if ($this->_tpl_vars['user_prefs']['close_popup_windows'] != '1'): ?>checked<?php endif; ?> value="0"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'close_popup_windows', 1);">No</a>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Receive emails when all issues are created ?</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="radio" name="receive_new_emails" <?php if ($this->_tpl_vars['user_prefs']['receive_new_emails'] != '0'): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'receive_new_emails', 0);">Yes</a>&nbsp;&nbsp;
            <input type="radio" name="receive_new_emails" <?php if ($this->_tpl_vars['user_prefs']['receive_new_emails'] == '0'): ?>checked<?php endif; ?> value="0"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'receive_new_emails', 1);">No</a>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Receive emails when new issues are assigned to you ?</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="radio" name="receive_assigned_emails" <?php if ($this->_tpl_vars['user_prefs']['receive_assigned_emails']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'receive_assigned_emails', 0);">Yes</a>&nbsp;&nbsp;
            <input type="radio" name="receive_assigned_emails" <?php if (! $this->_tpl_vars['user_prefs']['receive_assigned_emails']): ?>checked<?php endif; ?> value="0"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('account_prefs_form', 'receive_assigned_emails', 1);">No</a>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Default Options for Notifications:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="checkbox" name="updated" <?php if ($this->_tpl_vars['user_prefs']['updated']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('account_prefs_form', 'updated');">Issues are Updated</a><br />
            <input type="checkbox" name="closed" <?php if ($this->_tpl_vars['user_prefs']['closed']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('account_prefs_form', 'closed');">Issues are Closed</a><br />
            <input type="checkbox" name="emails" <?php if ($this->_tpl_vars['user_prefs']['emails']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('account_prefs_form', 'emails');">Emails are Associated</a><br />
            <input type="checkbox" name="files" <?php if ($this->_tpl_vars['user_prefs']['files']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('account_prefs_form', 'files');">Files are Attached</a>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Refresh Rate for Issue Listing Page:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" size="10" class="default" name="list_refresh_rate" value="<?php echo $this->_tpl_vars['user_prefs']['list_refresh_rate']; ?>
">
            <span class="small_default">(in minutes)</span>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Refresh Rate for Email Listing Page:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" size="10" class="default" name="emails_refresh_rate" value="<?php echo $this->_tpl_vars['user_prefs']['emails_refresh_rate']; ?>
">
            <span class="small_default">(in minutes)</span>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Email Signature:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <table border="0" width="100%">
              <tr>
                <td class="default" colspan="2">
                  Edit Signature:<br />
                  <textarea name="signature" style="width: 97%" rows="10"><?php echo $this->_tpl_vars['user_prefs']['email_signature']; ?>
</textarea>
                </td>
              </tr>
              <tr>
                <td class="default" width="140" nowrap>Upload New Signature:</td>
                <td><input size="40" type="file" name="file_signature" class="default"></td>
              </tr>
              <tr>
                <td class="default" colspan="2">
                  <input type="checkbox" name="auto_append_sig" value="yes" <?php if ($this->_tpl_vars['user_prefs']['auto_append_sig'] == 'yes'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('account_prefs_form', 'auto_append_sig');">Automatically append email signature when composing web based emails</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="190" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>SMS Email Address:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" size="40" class="default" name="sms_email" value="<?php echo $this->_tpl_vars['user_prefs']['sms_email']; ?>
">
            <span class="small_default"><i>(only used for automatic issue reminders)</i></span>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <input class="button" type="submit" value="Update Preferences">
            <input class="button" type="reset" value="Reset">
          </td>
        </tr>
        </form>
      </table>
    </td>
  </tr>
</table>

<br />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "app_info.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>