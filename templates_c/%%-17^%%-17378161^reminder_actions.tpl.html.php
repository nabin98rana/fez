<?php /* Smarty version 2.6.2, created on 2004-09-14 16:21:43
         compiled from manage/reminder_actions.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'manage/reminder_actions.tpl.html', 74, false),array('function', 'html_options', 'manage/reminder_actions.tpl.html', 125, false),array('function', 'cycle', 'manage/reminder_actions.tpl.html', 214, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <?php echo '
              <script language="JavaScript">
              <!--
              function validateForm(f)
              {
                  if (hasSelected(f.type, -1)) {
                      errors[errors.length] = new Option(\'Action Type\', \'type\');
                  }
                  if (isWhitespace(f.rank.value)) {
                      errors[errors.length] = new Option(\'Rank\', \'rank\');
                  }
                  // hack to make the multiple select box actually submit something
                  selectAllOptions(f, \'user_list[]\');
                  return true;
              }
              //-->
              </script>
              '; ?>

              <form name="reminder_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
" onSubmit="javascript:return checkFormSubmission(this, 'validateForm');">
              <input type="hidden" name="rem_id" value="<?php echo $this->_tpl_vars['rem_id']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td colspan="2">
                  <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="left" class="default">
                        <b>Manage Alert Actions</b>
                      </td>
                      <td align="right" class="default">
                        (<a href="reminders.php?cat=edit&id=<?php echo $this->_tpl_vars['rem_id']; ?>
" class="link" title="view alert details">Alert #<?php echo $this->_tpl_vars['rem_id']; ?>
: <?php echo $this->_tpl_vars['rem_title']; ?>
</a>)
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
                      An error occurred while trying to add the new action.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this new action.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the action was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the action information.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this action.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the action was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Title:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" size="50" name="title" class="default" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['rma_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'title')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <?php echo '
              <script language="JavaScript">
              <!--
              function checkActionType(f)
              {
                  var option = getSelectedOptionObject(f, \'type\');
                  if (option.text.indexOf(\'To...\') != -1) {
                      var block = \'block\';
                  } else {
                      var block = \'none\';
                  }
                  var user_list = getPageElement(\'action_user_list\');
                  user_list.style.display = block;
              }
              function addUserList(f)
              {
                  var field = getFormElement(f, \'available_users\');
                  var options = getSelectedItems(field);
                  if (isEmail(f.email_address.value)) {
                      options[options.length] = new Option(f.email_address.value);
                  }
                  addOptions(f, \'user_list[]\', options);
              }
              function removeUserList(f)
              {
                  var field = getFormElement(f, \'user_list[]\');
                  var options = new Array();
                  if (field.options.length > 0) {
                      for (var i = 0; i < field.options.length; i++) {
                          if (!field.options[i].selected) {
                              options[options.length] = field.options[i];
                          }
                      }
                  }
                  removeAllOptions(f, \'user_list[]\');
                  addOptions(f, \'user_list[]\', options);
              }
              //-->
              </script>
              '; ?>

              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Action Type:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="type" class="default" onChange="javascript:checkActionType(this.form);">
                    <option value="-1">Please choose an option</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['action_types'],'selected' => $this->_tpl_vars['info']['rma_rmt_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'type')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                  <div id="action_user_list" style="display: none;">
                  <table bgcolor="#666666" style="margin-left: 30px; margin-top: 5px;">
                    <tr>
                      <td><input class="default" type="text" name="email_address"></td>
                      <td>&nbsp;</td>
                      <td class="default_white" valign="bottom"><b>Email List:</b></td>
                    </tr>
                    <tr>
                      <td>
                        <select class="default" name="available_users" multiple size="4">
                          <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['user_options']), $this);?>

                        </select>
                      </td>
                      <td>
                        <input class="shortcut" type="button" value="Add &gt;&gt;" onClick="javascript:addUserList(this.form);">
                        <br /><br />
                        <input class="shortcut" type="button" value="Remove" onClick="javascript:removeUserList(this.form);">
                      </td>
                      <td>
                        <select class="default" name="user_list[]" multiple size="4">
                          <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['info']['user_list']), $this);?>

                        </select>
                      </td>
                    </tr>
                  </table>
                  </div>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Rank:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" size="5" class="default" name="rank" value="<?php echo $this->_tpl_vars['info']['rma_rank']; ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'rank')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                  <span class="small_default"><i>(this will determine the order in which actions are triggered)</i></span>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Action">
                  <?php else: ?>
                  <input class="button" type="submit" value="Add Action">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Actions:</b> (<a href="reminders.php?cat=edit&id=<?php echo $this->_tpl_vars['rem_id']; ?>
" class="link">Back to Alert List</a>)
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
                          alert(\'Please select at least one of the actions.\');
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
                    <input type="hidden" name="rem_id" value="<?php echo $this->_tpl_vars['rem_id']; ?>
">
                    <input type="hidden" name="cat" value="delete">
                    <tr>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" align="center">&nbsp;<b>Rank</b>&nbsp;</td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Type</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_id']; ?>
"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default" align="center">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=change_rank&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_id']; ?>
&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&rank=desc"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/desc.gif" border="0"></a> <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_rank']; ?>

                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=change_rank&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_id']; ?>
&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&rank=asc"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/asc.gif" border="0"></a>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                      </td>
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rmt_title']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<a href="reminder_conditions.php?rem_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_rem_id']; ?>
&rma_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rma_id']; ?>
" class="link"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['total_conditions']; ?>
 Condition<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['total_conditions'] != 1): ?>s<?php endif; ?></a></td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No actions could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
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
      window.onload = setActionTypeField;
      function setActionTypeField()
      {
          var f = getForm(\'reminder_form\');
          checkActionType(f);
      }
      //-->
      </script>
      '; ?>

      