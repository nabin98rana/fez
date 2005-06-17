<?php /* Smarty version 2.6.2, created on 2004-07-26 12:26:10
         compiled from convert_note.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => $this->_tpl_vars['extra_title'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['convert_result']): ?>
  <br />
  <center>
  <span class="default">
  <?php if ($this->_tpl_vars['convert_result'] == -1): ?>
    <b>An error occurred while trying to convert the selected note.</b>
  <?php elseif ($this->_tpl_vars['convert_result'] == 1): ?>
    <b>Thank you, the note was converted successfully.</b>
  <?php endif; ?>
  </span>
  </center>
  <script language="JavaScript">
  <!--
  <?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows'] == '1'): ?>
  setTimeout('closeAndRefresh()', 2000);
  <?php endif; ?>
  //-->
  </script>
  <br />
  <?php if (! $this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
  <center>
    <span class="default"><a class="link" href="javascript:void(null);" onClick="javascript:closeAndRefresh();">Continue</a></span>
  </center>
  <?php endif; ?>
<?php else: ?>
<script language="JavaScript">
<!--
<?php echo '
function validateForm(f)
{
    var field = getFormElement(f, \'target\', 1);
    if ((field.checked) && (!confirm(\'WARNING: Converting this note to an email will send the email to any unknown contacts that may \\nbe listed in this issue\\\'s notification list.\'))) {
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<form name="convert_note_form" method="post" action="convert_note.php" onSubmit="javascript:return validateForm(this);">
<input type="hidden" name="cat" value="convert">
<input type="hidden" name="note_id" value="<?php echo $this->_tpl_vars['note_id']; ?>
">
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Convert Note To Email</b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" align="right">
            <input type="radio" name="target" value="draft" checked>
          </td>
          <td class="default" bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">
            <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('convert_note_form', 'target', 0);">Convert to Draft and Save For Later Editing</a></b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">&nbsp;</td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="small_default">
            <b>ALERT:</b> Email will be re-sent from your name, NOT original sender's, and without any attachments.
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" align="right" valign="top">
            <input type="radio" name="target" value="email">
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="default">
            <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('convert_note_form', 'target', 1);">Convert to Email and Send Now</a></b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">&nbsp;</td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="small_default">
            <b>ALERT:</b> Email will be re-sent from original sender, including any attachments.
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="right">
            <input type="submit" value="Continue &gt;&gt;" class="button">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>