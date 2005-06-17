<?php /* Smarty version 2.6.2, created on 2004-10-22 02:30:16
         compiled from support_emails.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'is_array', 'support_emails.tpl.html', 53, false),array('modifier', 'count', 'support_emails.tpl.html', 53, false),array('modifier', 'escape', 'support_emails.tpl.html', 93, false),array('modifier', 'default', 'support_emails.tpl.html', 103, false),array('function', 'get_innerhtml', 'support_emails.tpl.html', 57, false),array('function', 'get_display_style', 'support_emails.tpl.html', 61, false),array('function', 'cycle', 'support_emails.tpl.html', 80, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
function removeEmails(f)
{
    if (!hasOneChecked(f, \'item[]\')) {
        alert(\'Please choose which entries need to be disassociated with the current issue.\');
        return false;
    }
    if (!confirm(\'This action will remove the association of the selected entries to the current issue.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'\', \'_removeEmails\', features);
        popupWin.focus();
        return true;
    }
}
function viewEmail(account_id, email_id)
{
    var features = \'width=740,height=580,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var emailWin = window.open(\'view_email.php?ema_id=\' + account_id + \'&id=\' + email_id, \'_email\' + email_id, features);
    emailWin.focus();
}
function reply(account_id, email_id)
{
'; ?>

    var features = 'width=740,height=580,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var emailWin = window.open('send.php?issue_id=<?php echo $_GET['id']; ?>
&ema_id=' + account_id + '&id=' + email_id, '_emailReply' + email_id, features);
    emailWin.focus();
<?php echo '
}
function sendEmail(account_id, issue_id)
{
    var features = \'width=740,height=580,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var emailWin = window.open(\'send.php?issue_id=\' + issue_id + \'&ema_id=\' + account_id, \'_email\', features);
    emailWin.focus();
}

//-->
</script>
'; ?>

<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form onSubmit="javascript:return removeEmails(this);" target="_removeEmails" action="popup.php" method="post">
<input type="hidden" name="cat" value="remove_support_email">
  <tr>
    <td width="100%">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default" nowrap>
            <b>Associated Emails (<?php if (is_array($this->_tpl_vars['emails'])):  echo count($this->_tpl_vars['emails']);  else: ?>0<?php endif; ?>)</b>
          </td>
          <td align="right" class="default">
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            [ <a id="support_emails_link" class="link" href="javascript:void(null);" onClick="javascript:toggleVisibility('support_emails');"><?php echo smarty_function_get_innerhtml(array('element_name' => 'support_emails'), $this);?>
</a> ]
            <?php endif; ?>
          </td>
        </tr>
        <tr id="support_emails1" <?php echo smarty_function_get_display_style(array('element_name' => 'support_emails'), $this);?>
>
          <td colspan="2">
            <table width="100%" cellpadding="2" cellspacing="1">
              <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                <?php if ($this->_tpl_vars['emails'] != "" && ( $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true' )): ?>
                <td width="5">
                  <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');">
                </td>
                <?php endif; ?>
                <td align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" NOWRAP align="center">
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('ec_id' => 'email','remote_func' => 'getEmail')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
                <td width="5" class="default_white">Reply</td>
                <td class="default_white">From</td>
                <td class="default_white">To</td>
                <td class="default_white" nowrap>Date</td>
                <td width="55%" class="default_white">Subject</td>
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
              <?php echo smarty_function_cycle(array('values' => $this->_tpl_vars['cycle'],'assign' => 'row_color'), $this);?>

              <tr>
                <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
                <td align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <input type="checkbox" name="item[]" value="<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_id']; ?>
">
                </td>
                <?php endif; ?>
                <td align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" NOWRAP align="center">
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('ec_id' => 'email','list_id' => $this->_tpl_vars['emails'][$this->_sections['i']['index']]['composite_id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
                <td align="center" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <a title="reply to this email" href="javascript:void(null);" onClick="javascript:reply(<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_ema_id']; ?>
, <?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_id']; ?>
);" class="link"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/reply.gif" border="0"></a>
                </td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <?php if ($this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_to'] == ""): ?>
                  <i>sent to notification list</i>
                  <?php else: ?>
                  <?php echo ((is_array($_tmp=$this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                  <?php endif; ?>
                </td>
                <td class="default" nowrap bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_date']; ?>
</td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <a title="view email details" href="javascript:void(null);" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_ema_id']; ?>
, <?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_id']; ?>
);" class="link"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_subject'])) ? $this->_run_mod_handler('default', true, $_tmp, "<Empty Subject Header>") : smarty_modifier_default($_tmp, "<Empty Subject Header>")))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                  <?php if ($this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_has_attachment']): ?>
                  <a title="view email details" href="javascript:void(null);" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_ema_id']; ?>
, <?php echo $this->_tpl_vars['emails'][$this->_sections['i']['index']]['sup_id']; ?>
);" class="link"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/attachment.gif" border="0"></a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/body.tpl.html", 'smarty_include_vars' => array('ec_id' => 'email','list_id' => $this->_tpl_vars['emails'][$this->_sections['i']['index']]['composite_id'],'colspan' => 7,'row_color' => $this->_tpl_vars['row_color'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
              <?php endfor; else: ?>
              <tr>
                <td colspan="<?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>6<?php else: ?>5<?php endif; ?>" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default" align="center">
                  <i>No associated emails could be found.</i>
                </td>
              </tr>
              <?php endif; ?>
              <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
              <tr>
                <td colspan="7" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td nowrap align="left">
                        <nobr>
                        <?php if ($this->_tpl_vars['emails'] != "" && ( $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true' )): ?>
                        <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');">
                        <input type="submit" class="button" value="Remove Selected">
                        <?php endif; ?>
                        </nobr>
                      </td>
                      <td>
                        <?php if ($this->_tpl_vars['ema_id'] != ""): ?>
                        <input type="button" class="button" value="Send Email" onClick="javascript:sendEmail(<?php echo $this->_tpl_vars['ema_id']; ?>
, <?php echo $_GET['id']; ?>
);">
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php endif; ?>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</form>
</table>
