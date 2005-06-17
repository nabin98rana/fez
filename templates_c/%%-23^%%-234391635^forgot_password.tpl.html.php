<?php /* Smarty version 2.6.2, created on 2004-07-01 11:07:46
         compiled from forgot_password.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'forgot_password.tpl.html', 55, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => 'I Forgot My Password')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br /><br />
<?php echo '
<script language="JavaScript">
<!--
function validateForm(f)
{
    if (isWhitespace(f.email.value)) {
        alert(\'Please enter your account email address.\');
        selectField(f, \'email\');
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<?php if (! $this->_tpl_vars['result']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "yellow_note.tpl.html", 'smarty_include_vars' => array('content' => "<b>Note:</b> Please enter your email address below and a new random password will be created and assigned to your account. For security purposes a confirmation message will be sent to your email address and after confirming it the new password will be then activated and sent to you.")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<form name="email_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<input type="hidden" name="cat" value="reset_password">
<table align="center" width="500" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td>
      <table bgcolor="#006486" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2" align="center">
            <h2 style="color: white;">I Forgot My Password</h2>
            <hr size="1" noshade color="#000000">
          </td>
        </tr>
        <?php if ($this->_tpl_vars['result'] != 0): ?>
        <tr>
          <td colspan="2" align="center" class="error">
            <b>
            <?php if ($this->_tpl_vars['result'] == -1): ?>
              Error: An error occurred while trying to run your query.
            <?php elseif ($this->_tpl_vars['result'] == 1): ?>
              Thank you, a confirmation message was just emailed to you. Please follow the instructions available in this message to confirm your password creation request.
            <?php elseif ($this->_tpl_vars['result'] == 3): ?>
              Error: Your user status is currently set as inactive. Please 
              contact your local system administrator for further information.
            <?php elseif ($this->_tpl_vars['result'] == 4): ?>
              Error: Please provide your email address.
            <?php endif; ?>
            </b>
          </td>
        </tr>
        <?php else: ?>
        <tr>
          <td align="right" width="40%" class="default_white"><b><u>E</u>mail Address:</b></td>
          <td width="60%">
            <input accessKey="e" class="default" type="text" name="email" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['email'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_GET['email']) : smarty_modifier_default($_tmp, @$_GET['email'])); ?>
" size="30" maxlength="100">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'email')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr align="center">
          <td colspan="2">
            <input type="submit" name="Submit" value="Send New Password" class="button">
          </td>
        </tr>
        <?php endif; ?>
        <tr align="center">
          <td colspan="2" class="default_white">
            <a class="white_link" href="index.php">Back to Login Form</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php echo '
<script language="JavaScript">
<!--
window.onload = setFocus;
function setFocus()
{
    if (document.email_form.email) {
        document.email_form.email.focus();
    }
}
//-->
</script>
'; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>