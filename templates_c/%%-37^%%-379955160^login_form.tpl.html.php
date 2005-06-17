<?php /* Smarty version 2.6.2, created on 2005-06-10 12:30:30
         compiled from login_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'login_form.tpl.html', 30, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
function validateForm(f)
{
    if (isWhitespace(f.username.value)) {
        errors[errors.length] = new Option(\'UQ Username\', \'username\');
    }
    if (isWhitespace(f.passwd.value)) {
        errors[errors.length] = new Option(\'Password\', \'passwd\');
    }
    if (errors.length > 0) {
        return false;
    }
}
//-->
</script>
'; ?>

<form name="login_form" onSubmit="javascript:return checkFormSubmission(this, 'validateForm');" method="post" action="index.php">
<input type="hidden" name="cat" value="login">
<input type="hidden" name="url" value="<?php echo $_GET['url']; ?>
">
<table align="center" width="400" border="0" cellspacing="0" cellpadding="1" bgcolor="#FFFFFF">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2" align="center" nowrap>
            <br /><br />
            <h2><?php echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
</h2>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table align="center" width="400" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2" bgcolor="#006486"><img src="images/blank.gif" width="1" height="5"></td>
        </tr>
        <?php if ($_GET['err'] != 0): ?>
        <tr>
          <td colspan="2" align="center" class="error" bgcolor="#006486">
            <b>
            <?php if ($_GET['err'] == 1): ?>
              Error: Please provide your UQ username.
            <?php elseif ($_GET['err'] == 2): ?>
              Error: Please provide your password.
            <?php elseif ($_GET['err'] == 3 || $_GET['err'] == 4): ?>
              Error: The username / password combination could not be found in the system.
            <?php elseif ($_GET['err'] == 5): ?>
              Your session has expired. Please login again to continue.
            <?php elseif ($_GET['err'] == 6): ?>
              Thank you, you are now logged out of <?php echo $this->_tpl_vars['application_title']; ?>
.
            <?php elseif ($_GET['err'] == 7): ?>
              Error: Your user status is currently set as inactive. Please 
              contact your local system administrator for further information.
            <?php elseif ($_GET['err'] == 8): ?>
              Thank you, your account is now active and ready to be 
              used. Use the form below to login.
            <?php elseif ($_GET['err'] == 9): ?>
              Error: Your user status is currently set as pending. This 
              means that you still need to confirm your account 
              creation request. Please contact your local system 
              administrator for further information.
            <?php elseif ($_GET['err'] == 11): ?>
              Error: Cookies support seem to be disabled in your browser. Please enable this feature and try again.
            <?php elseif ($_GET['err'] == 20): ?>
              Error: Your IP Address appears to have changed during your login session in <?php echo $this->_tpl_vars['application_title']; ?>
. This behaviour is often
			  indicative of an attempt to hijack a session to gain priviledges in web applications, so you have been logged out. If
              this continues to be a problem for you, please contact the <?php echo $this->_tpl_vars['application_title']; ?>
 webmaster as your ISP may be forcing you through a rotating proxy.
            <?php elseif ($_GET['err'] == 21): ?>
			  You must first login to access this resource.
            <?php elseif ($_GET['err'] == 12): ?>
              Error: In order for <?php echo $this->_tpl_vars['application_title']; ?>
 to work properly, you must enable cookie support in your browser. Please login 
              again and accept all cookies coming from it.
            <?php endif; ?>
            </b>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td align="right" width="40%" class="default_white" bgcolor="#006486"><b><u>U</u>Q username:</b></td>
          <td width="60%" bgcolor="#006486">
            <input accessKey="u" class="default" type="text" name="username" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['username'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_GET['username']) : smarty_modifier_default($_tmp, @$_GET['username'])); ?>
" size="30">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'username')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td align="right" width="40%" class="default_white" bgcolor="#006486"><b><u>P</u>assword:</b></td>
          <td width="60%" bgcolor="#006486">
            <input accessKey="p" class="default" type="password" name="passwd" size="20" maxlength="32">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'passwd')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr align="center">
          <td colspan="2" bgcolor="#006486">
            <input type="submit" name="Submit" value="Login" class="button">
          </td>
        </tr>
        <tr align="center">
          <td colspan="2" class="default_white" bgcolor="#006486">
			&nbsp;
            <?php if ($this->_tpl_vars['app_setup']['open_signup'] == 'enabled'): ?><a class="white_link" href="signup.php">Signup for an Account</a><?php endif; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php if ($this->_tpl_vars['anonymous_post']): ?>
<br />
<table width="400" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <b>NOTE: You may report issues without the need to login by using the following URL:</b>
            <br /><br />
            <a href="<?php echo $this->_tpl_vars['app_base_url']; ?>
post.php" class="link"><?php echo $this->_tpl_vars['app_base_url']; ?>
post.php</a>
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
window.onload = setFocus;
function setFocus()
{
    if (!isWhitespace(document.login_form.username.value)) {
        document.login_form.passwd.focus();
    } else {
        document.login_form.username.focus();
    }
}
//-->
</script>
'; ?>

