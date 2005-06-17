<?php /* Smarty version 2.6.2, created on 2004-10-20 19:26:16
         compiled from associate.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'associate.tpl.html', 44, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => $this->_tpl_vars['extra_title'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['associate_result']): ?>
  <br />
  <center>
  <span class="default">
  <?php if ($this->_tpl_vars['associate_result'] == -1): ?>
    <b>An error occurred while trying to associate the selected email message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?></b>
  <?php elseif ($this->_tpl_vars['associate_result'] == 1): ?>
    <b>Thank you, the selected email message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> <?php if ($this->_tpl_vars['total_emails'] > 1): ?>were<?php else: ?>was<?php endif; ?> associated successfully.</b>
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
<?php if ($this->_tpl_vars['unknown_contacts'] != ''): ?>
<br />
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/error.gif" hspace="2" vspace="2" border="0" align="left"></td>
          <td width="100%" class="default"><span style="font-weight: bold; font-size: 160%; color: red;">Warning: Unknown Contacts Found</span></td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            The following addresses could not be matched against the system user records:
            <br /><br />
            <ul>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['unknown_contacts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
              <li><?php echo ((is_array($_tmp=$this->_tpl_vars['unknown_contacts'][$this->_sections['i']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</li>
              <?php endfor; endif; ?>
            </ul>
            Please make sure you have selected the correct email messages to associate.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br />
<?php endif; ?>
<script language="JavaScript">
<!--
var found_unknown = <?php if ($this->_tpl_vars['unknown_contacts'] != ''): ?>1<?php else: ?>0<?php endif; ?>;
<?php echo '
function validateForm(f)
{
    if ((found_unknown) && (!confirm(\'Warning: Unknown contacts were found in the selected email messages. Please\\nmake sure you have selected the correct email messages to associate.\'))) {
        return false;
    } else {
        return true;
    }
}
//-->
</script>
'; ?>

<form name="associate_email_form" method="post" action="associate.php" onSubmit="javascript:return validateForm(this);">
<input type="hidden" name="cat" value="associate">
<input type="hidden" name="issue" value="<?php echo $_GET['issue']; ?>
">
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
<input type="hidden" name="item[]" value="<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]; ?>
">
<?php endfor; endif; ?>
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Associate Email Message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> to Issue #<?php echo $_GET['issue']; ?>
</b>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="default">
            <img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/error.gif"> <b>Please choose one of the following actions to take in regards to the selected email message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?>:</b>
            <br /><br />
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" align="right" valign="top">
            <input type="radio" name="target" value="email" checked>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="default">
            <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('associate_email_form', 'target', 0);">Save Message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> as <?php if ($this->_tpl_vars['total_emails'] == 1): ?>an <?php endif; ?>Email<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?></a></b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">&nbsp;</td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="small_default">
            <b>NOTE:</b> Email<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> will be broadcasted to the full notification list, including any unknown contacts, if this option is chosen.
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" align="right">
            <input type="radio" name="target" value="note">
          </td>
          <td class="default" bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">
            <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('associate_email_form', 'target', 1);">Save Message<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> as <?php if ($this->_tpl_vars['total_emails'] == 1): ?>an <?php endif; ?>Internal Note<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?></a></b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">&nbsp;</td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="small_default">
            <b>NOTE:</b> Email<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> will be saved as <?php if ($this->_tpl_vars['total_emails'] == 1): ?>a<?php endif; ?> note<?php if ($this->_tpl_vars['total_emails'] > 1): ?>s<?php endif; ?> and broadcasted only to staff users.
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