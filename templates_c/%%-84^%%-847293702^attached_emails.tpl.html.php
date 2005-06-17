<?php /* Smarty version 2.6.2, created on 2004-07-06 10:25:39
         compiled from attached_emails.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'attached_emails.tpl.html', 43, false),)), $this); ?>

<br />
<script language="JavaScript">
<!--
var url = '<?php echo $_SERVER['PHP_SELF']; ?>
?cat=associate&';
<?php echo '
function removeEmails(f)
{
    if (!hasOneChecked(f, \'emails[]\')) {
        alert(\'Please choose which entries need to be removed.\');
        return false;
    }
    // loop through all of the form elements and build a dynamic url
    for (var i = 0; i < f.elements.length; i++) {
        if ((f.elements[i].name == \'emails[]\') && (!f.elements[i].checked)) {
            url += \'item[]=\' + f.elements[i].value + \'&\';
        }
    }
    window.location.href = url;
}
//-->
</script>
'; ?>

<table width="600" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <form action="" method="get">
  <tr>
    <td>
      &nbsp;
    </td>
    <td width="100%">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" colspan="3" class="default">
            <b>Attached Emails</b>
          </td>
        </tr>
        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['emails']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        <tr>
          <td>
            <input type="checkbox" name="emails[]" value="<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_id']; ?>
">
          </td>
          <td class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
          <td class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <?php endfor; endif; ?>
        <tr>
          <td colspan="3">
            <input type="button" class="button" value="Remove Selected" onClick="javascript:removeEmails(this.form);">
          </td>
        </tr>
      </table>
    </td>
    <td>
      &nbsp;
    </td>
  </tr>
  </form>
</table>
