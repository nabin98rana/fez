<?php /* Smarty version 2.6.2, created on 2004-09-14 06:20:51
         compiled from manage/reminders.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'manage/reminders.tpl.html', 123, false),array('function', 'cycle', 'manage/reminders.tpl.html', 246, false),array('modifier', 'escape', 'manage/reminders.tpl.html', 133, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <tr>
                <td colspan="2">
                  <table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                      <td class="default"><b>Manage Issue Alerts</b></td>
                      <td align="right" class="default">
                        <b><?php if ($_GET['cat'] == 'edit'): ?>Updating Alert #<?php echo $_GET['id'];  else: ?>Creating New Alert<?php endif; ?></b>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new alert.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this new alert.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the alert was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the alert information.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this alert.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the alert was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <script language="JavaScript">
              <!--
              var url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
              var rem_id = '<?php echo $_GET['id']; ?>
';
              <?php echo '
              function populateIssueComboBox(f)
              {
                  if (rem_id == \'\') {
                      url += \'?prj_id=\' + getSelectedOption(f, \'project\');
                  } else {
                      url += \'?cat=edit&id=\' + rem_id + \'&prj_id=\' + getSelectedOption(f, \'project\');
                  }
                  window.location.href = url;
              }
              function toggleReminderTypeFields()
              {
                  var f = getForm(\'reminder_form\');
                  var issue_field = getFormElement(f, \'issues[]\');
                  var priority_field = getFormElement(f, \'priorities[]\');

                  var field = getFormElement(f, \'reminder_type\', 0);
                  if (field.checked) {
                      issue_field.disabled = false;
                  } else {
                      issue_field.disabled = true;
                  }
                  field = getFormElement(f, \'check_priority\');
                  if (field.checked) {
                      priority_field.disabled = false;
                  } else {
                      priority_field.disabled = true;
                  }
              }
              function validateForm(f)
              {
                  if (hasSelected(f.project, -1)) {
                      alert(\'Please choose a team that will be associated with this alert.\');
                      return false;
                  }
                  if (isWhitespace(f.title.value)) {
                      selectField(f, \'title\');
                      alert(\'Please enter the title for this alert.\');
                      return false;
                  }
                  if (isWhitespace(f.rank.value)) {
                      selectField(f, \'rank\');
                      alert(\'Please enter the rank for this alert.\');
                      return false;
                  }
                  var field1 = getFormElement(f, \'reminder_type\', 0);
                  var field2 = getFormElement(f, \'reminder_type\', 1);
                  if ((!field1.checked) && (!field2.checked)) {
                      alert(\'Please choose an alert type.\');
                      return false;
                  }
                  if ((field1.checked) && (!hasOneSelected(f, \'issues[]\'))) {
                      alert(\'Please enter the issue IDs that will be associated with this alert.\');
                      return false;
                  }
                  if ((f.check_priority.checked) && (!hasOneSelected(f, \'priorities[]\'))) {
                      alert(\'Please choose the priorities that will be associated with this alert.\');
                      return false;
                  }
                  return true;
              }
              //-->
              </script>
              '; ?>

              <form name="reminder_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Team:</b>
                </td>
                <td width="85%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="project" class="default" onChange="javascript:populateIssueComboBox(this.form);">
                    <option value="-1">Please choose an option</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['project_list'],'selected' => $this->_tpl_vars['info']['rem_prj_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'project')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Title:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" size="50" name="title" class="default" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['rem_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'title')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Rank:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" size="10" name="rank" class="default" value="<?php echo $this->_tpl_vars['info']['rem_rank']; ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'rank')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Alert Type:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <table cellpadding="1">
                    <tr>
                      <td class="default">
                        <input type="radio" name="reminder_type" value="issue" <?php if ($this->_tpl_vars['info']['type'] == 'issue'): ?>checked<?php endif; ?> onClick="javascript:toggleReminderTypeFields();">
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('reminder_form', 'reminder_type', 0);toggleReminderTypeFields();">By Issue ID</a>
                      </td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td class="default">
                        <input type="radio" name="reminder_type" value="all_issues" <?php if ($this->_tpl_vars['info']['type'] == 'ALL'): ?>checked<?php endif; ?> onClick="javascript:toggleReminderTypeFields();">
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('reminder_form', 'reminder_type', 1);toggleReminderTypeFields();">All Issues</a>
                      </td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                      <td>
                        <select name="issues[]" class="default" size="4" multiple>
                          <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['issues'],'selected' => $this->_tpl_vars['associated_issues']), $this);?>

                        </select>
                      </td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="default">
                        <input type="checkbox" name="check_priority" value="yes" <?php if ($this->_tpl_vars['info']['check_priority'] == 'yes'): ?>checked<?php endif; ?> onClick="javascript:toggleReminderTypeFields();">
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('reminder_form', 'check_priority');toggleReminderTypeFields();">Also Filter By Issue Priorities</a>
                      </td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                      <td>
                        <select name="priorities[]" size="4" multiple class="default">
                          <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['priorities'],'selected' => $this->_tpl_vars['info']['rer_pri_id']), $this);?>

                        </select>
                      </td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Alert">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create Alert">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Issue Alerts:</b>
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
                          alert(\'Please select at least one of the alerts.\');
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
                    <tr>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>ID</b>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" align="center">&nbsp;<b>Rank</b>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Team</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Type</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>Issue Priorities</b>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Details</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default" align="center"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default" align="center">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=change_rank&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
&rank=desc"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/desc.gif" border="0"></a> <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_rank']; ?>

                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=change_rank&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
&rank=asc"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/asc.gif" border="0"></a>
                      </td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                      </td>
                      <td width="25%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['prj_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['type'] == 'ALL'): ?>All Issues<?php elseif ($this->_tpl_vars['list'][$this->_sections['i']['index']]['type'] == 'issue'): ?>By Issue ID<?php endif; ?>
                      </td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php if (isset($this->_sections['y'])) unset($this->_sections['y']);
$this->_sections['y']['name'] = 'y';
$this->_sections['y']['loop'] = is_array($_loop=$this->_tpl_vars['list'][$this->_sections['i']['index']]['priorities']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['y']['show'] = true;
$this->_sections['y']['max'] = $this->_sections['y']['loop'];
$this->_sections['y']['step'] = 1;
$this->_sections['y']['start'] = $this->_sections['y']['step'] > 0 ? 0 : $this->_sections['y']['loop']-1;
if ($this->_sections['y']['show']) {
    $this->_sections['y']['total'] = $this->_sections['y']['loop'];
    if ($this->_sections['y']['total'] == 0)
        $this->_sections['y']['show'] = false;
} else
    $this->_sections['y']['total'] = 0;
if ($this->_sections['y']['show']):

            for ($this->_sections['y']['index'] = $this->_sections['y']['start'], $this->_sections['y']['iteration'] = 1;
                 $this->_sections['y']['iteration'] <= $this->_sections['y']['total'];
                 $this->_sections['y']['index'] += $this->_sections['y']['step'], $this->_sections['y']['iteration']++):
$this->_sections['y']['rownum'] = $this->_sections['y']['iteration'];
$this->_sections['y']['index_prev'] = $this->_sections['y']['index'] - $this->_sections['y']['step'];
$this->_sections['y']['index_next'] = $this->_sections['y']['index'] + $this->_sections['y']['step'];
$this->_sections['y']['first']      = ($this->_sections['y']['iteration'] == 1);
$this->_sections['y']['last']       = ($this->_sections['y']['iteration'] == $this->_sections['y']['total']);
 echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['priorities'][$this->_sections['y']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  if (! $this->_sections['y']['last']): ?>, <?php endif;  endfor; endif; ?>
                      </td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a href="reminder_actions.php?rem_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rem_id']; ?>
" class="link"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['total_actions']; ?>
 Action<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['total_actions'] != 1): ?>s<?php endif; ?></a>
                      </td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="8" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No alerts could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="8" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                        <input type="submit" value="Delete" class="button">
                      </td>
                    </tr>
                    </form>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php echo '
      <script language="JavaScript">
      <!--
      window.onload = toggleReminderTypeFields;
      //-->
      </script>
      '; ?>

