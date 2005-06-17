<?php /* Smarty version 2.6.2, created on 2004-07-07 10:55:36
         compiled from post_note.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'post_note.tpl.html', 66, false),array('function', 'html_options', 'post_note.tpl.html', 90, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['post_result'] != ''): ?>
<br />
<center>
  <span class="default">
<?php if ($this->_tpl_vars['post_result'] == -1): ?>
  <b>An error occurred while trying to run your query</b>
<?php elseif ($this->_tpl_vars['post_result'] == 1): ?>
  <b>Thank you, the internal note was posted successfully.</b>
<?php endif; ?>
  </span>
</center>
<script language="JavaScript">
<!--
<?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
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
<?php echo '
<script language="JavaScript">
<!--
function validate(f)
{
    if (isWhitespace(f.title.value)) {
        alert(\'Please enter the title of this note.\');
        selectField(f, \'title\');
        return false;
    }
    if (isWhitespace(f.note.value)) {
        alert(\'Please enter the message body of this note.\');
        selectField(f, \'note\');
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<form onSubmit="javascript:return validate(this);" name="post_note_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<input type="hidden" name="cat" value="post_note">
<input type="hidden" name="parent_id" value="<?php echo $this->_tpl_vars['parent_note_id']; ?>
">
<input type="hidden" name="issue_id" value="<?php echo $this->_tpl_vars['issue_id']; ?>
">
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Post New Internal Note</b>
          </td>
        </tr>
        <tr>
          <td width="140" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>From:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <b><?php echo ((is_array($_tmp=$this->_tpl_vars['from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</b>
          </td>
        </tr>
        <tr>
          <td width="140" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>Title: *</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="title" class="default" size="50" value="<?php if ($this->_tpl_vars['note']['not_title'] != ""): ?>Re: <?php endif;  echo ((is_array($_tmp=$this->_tpl_vars['note']['not_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'title')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" colspan="2">
            <textarea name="note" rows="16" style="width: 97%"><?php echo ((is_array($_tmp=$this->_tpl_vars['note']['not_body'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'note')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="140" class="default_white" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
            <b>Extra Note Recipients:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" size="4" multiple name="note_cc[]" onChange="javascript:showSelections('post_note_form', 'note_cc[]');">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users']), $this);?>

            </select> <span class="small_default"><i>(hold ctrl to select multiple options)</i></span><br />
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => "note_cc[]",'callbacks' => "new Array('showSelections(\'post_note_form\', \'note_cc[]\')')")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <div class="default" id="selection_note_cc[]"></div>
          </td>
        </tr>
        <tr>
          <td width="140" class="default_white" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
            <b>Add Extra Recipients To Notification List?</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="radio" name="add_extra_recipients" value="yes"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('post_note_form', 'add_extra_recipients', 0);">Yes</a>&nbsp;&nbsp;
            <input type="radio" name="add_extra_recipients" value="no" checked> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('post_note_form', 'add_extra_recipients', 1);">No</a>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center">
                  <input class="button" type="submit" value="Post Internal Note">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <input class="button" type="button" value="Cancel" onClick="javascript:window.close();">
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
  </tr>
</table>
</form>
<?php if ($this->_tpl_vars['parent_note_id'] || $_GET['cat'] == 'reply'): ?>
<?php echo '
<script language="JavaScript">
<!--
window.onload = focusMessageBox;
function focusMessageBox()
{
    var f = getForm(\'post_note_form\');
    f.note.focus();
}
//-->
</script>
'; ?>

<?php endif; ?>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>