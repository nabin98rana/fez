<?php /* Smarty version 2.6.2, created on 2004-06-25 03:51:53
         compiled from email_drafts.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'email_drafts.tpl.html', 21, false),array('modifier', 'escape', 'email_drafts.tpl.html', 43, false),array('modifier', 'default', 'email_drafts.tpl.html', 47, false),array('function', 'get_innerhtml', 'email_drafts.tpl.html', 25, false),array('function', 'get_display_style', 'email_drafts.tpl.html', 29, false),array('function', 'cycle', 'email_drafts.tpl.html', 40, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
function viewDraft(draft_id, issue_id)
{
    var features = \'width=740,height=580,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var draftWin = window.open(\'send.php?cat=view_draft&issue_id=\' + issue_id + \'&id=\' + draft_id, \'_draft\' + draft_id, features);
    draftWin.focus();
}
//-->
</script>
'; ?>

<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td width="100%">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default" nowrap>
            <b>Drafts (<?php echo count($this->_tpl_vars['drafts']); ?>
)</b>
          </td>
          <td align="right" class="default">
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            [ <a id="drafts_link" class="link" href="javascript:void(null);" onClick="javascript:toggleVisibility('drafts');"><?php echo smarty_function_get_innerhtml(array('element_name' => 'drafts'), $this);?>
</a> ]
            <?php endif; ?>
          </td>
        </tr>
        <tr id="drafts1" <?php echo smarty_function_get_display_style(array('element_name' => 'drafts'), $this);?>
>
          <td colspan="2">
            <table width="100%" cellpadding="2" cellspacing="1">
              <tr bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
                <td class="default_white" NOWRAP><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('remote_func' => 'getDraft','ec_id' => 'draft')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td class="default_white">From</td>
                <td class="default_white">To</td>
                <td class="default_white" nowrap>Last Updated Date</td>
                <td width="45%" class="default_white">Subject</td>
              </tr>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['drafts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
                <td class="default" NOWRAP bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('ec_id' => 'draft','list_id' => $this->_tpl_vars['drafts'][$this->_sections['i']['index']]['emd_id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['drafts'][$this->_sections['i']['index']]['from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['drafts'][$this->_sections['i']['index']]['to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                <td class="default" nowrap bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><?php echo $this->_tpl_vars['drafts'][$this->_sections['i']['index']]['emd_updated_date']; ?>
</td>
                <td class="default" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                  <a title="view email details" href="javascript:void(null);" onClick="javascript:viewDraft(<?php echo $this->_tpl_vars['drafts'][$this->_sections['i']['index']]['emd_id']; ?>
, <?php echo $_GET['id']; ?>
);" class="link"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['drafts'][$this->_sections['i']['index']]['emd_subject'])) ? $this->_run_mod_handler('default', true, $_tmp, "<Empty Subject Header>") : smarty_modifier_default($_tmp, "<Empty Subject Header>")))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                </td>
              </tr>
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/body.tpl.html", 'smarty_include_vars' => array('ec_id' => 'draft','list_id' => $this->_tpl_vars['drafts'][$this->_sections['i']['index']]['emd_id'],'colspan' => '5')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
              <?php endfor; else: ?>
              <tr>
                <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default" align="center">
                  <i>No email drafts could be found.</i>
                </td>
              </tr>
              <?php endif; ?>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
