<?php /* Smarty version 2.6.2, created on 2004-11-03 14:49:08
         compiled from notification.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'notification.tpl.html', 147, false),array('modifier', 'escape', 'notification.tpl.html', 151, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />

<?php echo '
<script language="JavaScript">
<!--
function validate(f)
{
    if (isWhitespace(f.email.value)) {
        alert(\'Please enter a valid email address.\');
        selectField(f, \'email\');
        return false;
	} else {
		var emailCheck = true;
		var tempStr = f.email.value;
		var sArray = tempStr.split(",");
		for (var x=0; x < sArray.length; x++) {
			emailCheck = isValidEmail(sArray[x]);
			if (!emailCheck)  {					
				alert(\'Email Address: \'+sArray[x]+\' is not a valid email address!\');
				return false;
			}
		}
	}
    return true;
}
function addSelection(f, from, to)
{
    var selected = new Array();
    var field = getFormElement(f, from);
    selected = getSelectedItems(field);
    addOptions(f, to, selected);
}
function removeOption(f, field_name)
{
    var field = getFormElement(f, field_name);
    for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].selected) {
            field.options[i] = null;
            removeOption(f, field_name);
        }
    }
}
//-->
</script>
'; ?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="js/overlib_mini.js"></script>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
        <form name="notification_form" onSubmit="javascript:return validate(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
        <?php if ($_GET['cat'] == 'edit'): ?>
        <input type="hidden" name="cat" value="update">
        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
        <?php else: ?>
        <input type="hidden" name="cat" value="insert">
        <?php endif; ?>
        <input type="hidden" name="issue_id" value="<?php echo $this->_tpl_vars['issue_id']; ?>
">
        <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['reporter']): ?>
        <tr>
          <td colspan="2" class="default">
            <b>Notification Options</b>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['update_result'] != ""): ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
            <?php if ($this->_tpl_vars['update_result'] == -1): ?>
              An error occurred while trying to update the notification entry.
            <?php elseif ($this->_tpl_vars['update_result'] == 1): ?>
              Thank you, the notification entry was updated successfully.
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Email:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="email" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['sub_email']; ?>
">
            <?php if (! ( $this->_tpl_vars['os']['mac'] && $this->_tpl_vars['browser']['ie'] )): ?><a href="javascript:void(null);" onClick="return overlib(getFillInput('<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_layer.tpl.html", 'smarty_include_vars' => array('list' => $this->_tpl_vars['assoc_users'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>', 'notification_form', 'email'), STICKY, HEIGHT, 50, WIDTH, 160, BELOW, LEFT, CLOSECOLOR, '#FFFFFF', FGCOLOR, '#FFFFFF', BGCOLOR, '#333333', CAPTION, 'Lookup Details', CLOSECLICK);" onMouseOut="javascript:nd();"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" border="0"></a><?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'email')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Get a Notification When:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['info']['emails']): ?>checked<?php endif; ?> value="emails"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('notification_form', 'actions[]', 0);">Emails are Received or Sent</a><br />
            <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['info']['updated']): ?>checked<?php endif; ?> value="updated"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('notification_form', 'actions[]', 1);">Overview or Details are Changed</a><br />
            <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['info']['closed']): ?>checked<?php endif; ?> value="closed"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('notification_form', 'actions[]', 2);">Issue is Closed</a><br />
            <input type="checkbox" name="actions[]" <?php if ($this->_tpl_vars['info']['files']): ?>checked<?php endif; ?> value="files"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('notification_form', 'actions[]', 3);">Files are Attached</a>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <?php if ($_GET['cat'] == 'edit'): ?>
            <input class="button" type="submit" value="Update Subscription">
            <?php else: ?>
            <input class="button" type="submit" value="Add Subscription">
            <?php endif; ?>
            <input class="button" type="reset" value="Reset">
          </td>
        </tr>
        </form>
        <?php endif; ?>
        <tr>
          <td colspan="2" class="default">
            <b>Existing Subscribers for this Issue:</b>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo '
            <script language="JavaScript">
            <!--
            function checkDelete(f)
            {
                if (!hasOneChecked(f, \'items[]\')) {
                    alert(\'Please select at least one of the subscribers.\');
                    return false;
                }
                if (!confirm(\'This action will remove the selected entries.\')) {
                    return false;
                } else {
                    return true;
                }
            }
            //-->
            </script>
            '; ?>

            <table border="0" width="100%" cellpadding="1" cellspacing="1">
              <form onSubmit="javascript:return checkDelete(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <input type="hidden" name="cat" value="delete">
              <input type="hidden" name="issue_id" value="<?php echo $this->_tpl_vars['issue_id']; ?>
">
              <tr>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white"><b>Email</b> (click to edit)</td>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white"><b>Actions</b></td>
              </tr>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
              <?php echo smarty_function_cycle(array('values' => $this->_tpl_vars['cycle'],'assign' => 'row_color'), $this);?>

              <tr>
                <td width="4" nowrap bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sub_id']; ?>
"></td>
                <td width="60%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                  <a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&iss_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sub_iss_id']; ?>
&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sub_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sub_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                </td>
                <td width="40%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                  <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['actions']; ?>

                </td>
              </tr>
              <?php endfor; else: ?>
              <tr>
                <td colspan="3" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                  <b>No subscribers could be found.</b>
                </td>
              </tr>
              <?php endif; ?>
              <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['reporter']): ?>
              <tr>
                <td colspan="3" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <input type="submit" value="Remove Selected" class="button">
                  <input type="button" value="Close" class="button" onClick="javascript:closeAndRefresh();">
                </td>
              </tr>
              <?php endif; ?>
              </form>
            </table>
          </td>
        </tr>
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