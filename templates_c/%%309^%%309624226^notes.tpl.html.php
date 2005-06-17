<?php /* Smarty version 2.6.2, created on 2004-09-08 07:05:28
         compiled from notes.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'notes.tpl.html', 54, false),array('modifier', 'escape', 'notes.tpl.html', 81, false),array('modifier', 'default', 'notes.tpl.html', 85, false),array('function', 'get_innerhtml', 'notes.tpl.html', 58, false),array('function', 'get_display_style', 'notes.tpl.html', 62, false),array('function', 'cycle', 'notes.tpl.html', 73, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
function deleteNote(note_id)
{
    if (!confirm(\'This action will permanently delete the specified note.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'popup.php?cat=delete_note&id=\' + note_id, \'_popup\', features);
        popupWin.focus();
    }
}
function convertNote(note_id)
{
    if (!confirm(\'This note will be deleted & converted to an email, one either sent immediately or saved as a draft.\')) {
        return false;
    } else {
        var features = \'width=420,height=160,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'convert_note.php?id=\' + note_id, \'_convertNote\', features);
        popupWin.focus();
    }
}
function viewNote(note_id)
{
    var features = \'width=560,height=500,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var noteWin = window.open(\'view_note.php?id=\' + note_id, \'_note\' + note_id, features);
    noteWin.focus();
}
function postInternalNote(issue_id)
{
    var features = \'width=560,height=500,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var noteWin = window.open(\'post_note.php?issue_id=\' + issue_id, \'_postNote\', features);
    noteWin.focus();
}
function replyNote(note_id, issue_id)
{
    var features = \'width=560,height=500,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var noteWin = window.open(\'post_note.php?cat=reply&id=\' + note_id + \'&issue_id=\' + issue_id, \'_postNote\', features);
    noteWin.focus();
}
//-->
</script>
'; ?>

<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="add_note_form" target="_notes" method="post">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
        <tr>
          <td class="default">
            <b>Internal Notes (<?php echo count($this->_tpl_vars['notes']); ?>
)</b>
          </td>
          <td align="right" class="default">
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            [ <a id="notes_link" class="link" href="javascript:void(null);" onClick="javascript:toggleVisibility('notes');"><?php echo smarty_function_get_innerhtml(array('element_name' => 'notes'), $this);?>
</a> ]
            <?php endif; ?>
          </td>
        </tr>
        <tr id="notes1" <?php echo smarty_function_get_display_style(array('element_name' => 'notes'), $this);?>
>
          <td colspan="2">
            <table width="100%" cellpadding="2" cellspacing="1">
              <tr bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
                <td class="default_white" NOWRAP><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('remote_func' => 'getNote','ec_id' => 'note')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td class="default_white">Reply</td>
                <td width="20%" class="default_white">Posted Date</td>
                <td width="20%" class="default_white">User</td>
                <td width="60%" class="default_white">Title</td>
              </tr>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['notes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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

              <tr bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                <td class="default" NOWRAP><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('ec_id' => 'note','list_id' => $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td align="center">
                  <a title="reply to this note" href="javascript:void(null);" onClick="javascript:replyNote(<?php echo $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id']; ?>
, <?php echo $_GET['id']; ?>
);" class="link"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/reply.gif" border="0"></a>
                </td>
                <td class="default"><?php echo $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_created_date']; ?>
</td>
                <td class="default">
                  <?php echo ((is_array($_tmp=$this->_tpl_vars['notes'][$this->_sections['i']['index']]['usr_full_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                  <?php if ($this->_tpl_vars['current_user_id'] == $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_usr_id']): ?>[ <a class="link" href="javascript:void(null);" onClick="javascript:deleteNote(<?php echo $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id']; ?>
);">delete</a> ]<?php endif; ?>
                </td>
                <td class="default">
                  <a title="view note details" href="javascript:void(null);" onClick="javascript:viewNote(<?php echo $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id']; ?>
);" class="link"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_title'])) ? $this->_run_mod_handler('default', true, $_tmp, "<Empty Title>") : smarty_modifier_default($_tmp, "<Empty Title>")))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                  <?php if ($this->_tpl_vars['notes'][$this->_sections['i']['index']]['has_blocked_message']): ?> (<a href="javascript:void(null);" onClick="javascript:convertNote(<?php echo $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id']; ?>
);" class="link">convert note</a>)<?php endif; ?>
                </td>
              </tr>
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/body.tpl.html", 'smarty_include_vars' => array('ec_id' => 'note','list_id' => $this->_tpl_vars['notes'][$this->_sections['i']['index']]['not_id'],'colspan' => '5')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
              <?php endfor; else: ?>
              <tr>
                <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                  <i>No internal notes could be found.</i>
                </td>
              </tr>
              <?php endif; ?>
              <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['reporter'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
              <tr>
                <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center">
                  <input type="button" value="Post Internal Note" class="button" onClick="javascript:postInternalNote(<?php echo $_GET['id']; ?>
);">
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
