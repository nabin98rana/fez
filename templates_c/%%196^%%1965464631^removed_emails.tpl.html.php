<?php /* Smarty version 2.6.2, created on 2004-06-25 14:16:20
         compiled from removed_emails.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'removed_emails.tpl.html', 72, false),array('modifier', 'escape', 'removed_emails.tpl.html', 81, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['result_msg'] != ''): ?>
<br />
<center>
  <span class="default">
<?php if ($this->_tpl_vars['result_msg'] == -1): ?>
  <b>An error occurred while trying to run your query</b>
<?php elseif ($this->_tpl_vars['result_msg'] == 1): ?>
  <b>Thank you, the emails were <?php if ($_POST['cat'] == 'remove'): ?>removed<?php else: ?>restored<?php endif; ?> successfully.</b>
<?php endif; ?>
  </span>
</center>
<script language="JavaScript">
<!--
opener.location.href = opener.location;
setTimeout('window.close()', 2000);
//-->
</script>
<?php else: ?>
<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
        <tr>
          <td class="default">
            <b>Removed Emails</b>
          </td>
        </tr>
        <tr>
          <td>
            <?php echo '
            <script language="">
            <!--
            function validateForm(f)
            {
                if (!hasOneChecked(f, \'item[]\')) {
                    alert(\'Please choose which emails need to be restored.\');
                    return false;
                }
                f.submit();
            }
            function removeEmails(f)
            {
                if (!hasOneChecked(f, \'item[]\')) {
                    alert(\'Please choose which emails need to be permanently removed.\');
                    return false;
                }
                if (!confirm(\'WARNING: This action will permanently remove the selected emails from your email account.\')) {
                    return false;
                } else {
                    f.cat.value = \'remove\';
                    f.submit();
                }
            }
            //-->
            </script>
            '; ?>

            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
            <input type="hidden" name="cat" value="restore">
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
              <tr>
                <td width="1%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                  <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');">
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">Date</td>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" width="20%" class="default_white">From</td>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" width="50%" class="default_white">Subject</td>
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
                <td width="1%" align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <input type="checkbox" name="item[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_id']; ?>
">
                </td>
                <td align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default" nowrap>
                  <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_date']; ?>

                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" width="20%" class="default">
                  <?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" width="50%" class="default">
                  <?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                </td>
              </tr>
              <?php endfor; else: ?>
              <tr>
                <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default" align="center">
                  <i>No emails could be found.</i>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" width="1%" align="center">
                  <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');">
                </td>
                <td colspan="3" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                  <input type="button" class="button" value="Restore Emails" onClick="javascript:validateForm(this.form);">
                  <input type="button" class="button" value="Close" onClick="javascript:window.close();">
                </td>
              </tr>
              <tr>
                <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" align="left">
                  <input type="button" class="button" value="Permanently Remove" onClick="javascript:removeEmails(this.form);">
                </td>
              </tr>
            </table>
            </form>
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
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>