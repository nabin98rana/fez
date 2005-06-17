<?php /* Smarty version 2.6.2, created on 2004-08-31 12:53:53
         compiled from close.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'close.tpl.html', 66, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => $this->_tpl_vars['extra_title'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "navigation.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />
<?php if ($this->_tpl_vars['close_result'] != ""): ?>
<table width="500" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <?php if ($this->_tpl_vars['close_result'] == -1): ?>
            <b>Sorry, an error happened while trying to run your query.</b>
            <?php elseif ($this->_tpl_vars['close_result'] == 1): ?>
            <b>Thank you, the issue was closed successfully. Please choose 
            from one of the options below:</b>
            <ul>
              <li><a href="view.php?id=<?php echo $_POST['issue_id']; ?>
" class="link">Open the Issue Details Page</a></li>
              <li><a href="list.php" class="link">Open the Issue Listing Page</a></li>
              <?php if ($this->_tpl_vars['app_setup']['support_email'] == 'enabled' && $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer']): ?>
              <li><a href="emails.php" class="link">Open the Emails Listing Page</a></li>
              <?php endif; ?>
            </ul>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php else: ?>
<?php echo '
<script language="JavaScript">
<!--
function validateClose(f)
{
// Reason field not required anymore
/*    if (isWhitespace(f.reason.value)) {
        alert(\'Please enter the reason for closing this issue.\');
        selectField(f, \'reason\');
        return false;
    } */
    return true;
}
//-->
</script>
'; ?>

<table width="80%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="close_form" onSubmit="javascript:return validateClose(this);" method="post" action="close.php">
<input type="hidden" name="cat" value="close">
<input type="hidden" name="issue_id" value="<?php echo $_GET['id']; ?>
">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default" nowrap>
            <b>Close Issue</b> (Issue ID: <?php echo $_GET['id']; ?>
)
          </td>
        </tr>
        <tr>
          <td width="160" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Status:</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="status">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['statuses']), $this);?>

            </select>
          </td>
        </tr>
        <tr>
          <td width="160" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Resolution:</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="resolution">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['resolutions']), $this);?>

            </select>
          </td>
        </tr>
        <tr>
          <td width="160" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Resolution Location:</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="resolution_location">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['resolution_locations']), $this);?>

            </select> &nbsp; <span class="default"><em>(Onsite means at the library branch of the issue location)</em></span>
          </td>
        </tr>
        <tr>
          <td width="160" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Send Notification About Issue Being Closed?</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="radio" name="send_notification" checked value="1"> 
            <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('close_form', 'send_notification', 0);">Yes</a>&nbsp;&nbsp;
            <input type="radio" name="send_notification" value="0"> 
            <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('close_form', 'send_notification', 1);">No</a>
          </td>
        </tr>
        <tr>
          <td width="160" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>Internal Note (reason for closing issue): *</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <textarea name="reason" rows="8" style="width: 97%"></textarea>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'reason')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td colspan="2">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td><input class="button" type="button" value="&lt;&lt; Back" onClick="javascript:history.go(-1);"></td>
                <td width="100%" align="center"><input class="button" type="submit" value="Close Issue"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</form>
</table>
<?php endif; ?>
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