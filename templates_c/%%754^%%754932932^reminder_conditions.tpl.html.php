<?php /* Smarty version 2.6.2, created on 2004-09-14 16:21:54
         compiled from manage/reminder_conditions.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'manage/reminder_conditions.tpl.html', 91, false),array('function', 'cycle', 'manage/reminder_conditions.tpl.html', 185, false),array('modifier', 'escape', 'manage/reminder_conditions.tpl.html', 188, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <script language="JavaScript">
              <!--
              var url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
              var rem_id = '<?php echo $this->_tpl_vars['rem_id']; ?>
';
              var rma_id = '<?php echo $this->_tpl_vars['rma_id']; ?>
';
              <?php echo '
              function validateForm(f)
              {
                  if (hasSelected(f.field, -1)) {
                      errors[errors.length] = new Option(\'Field\', \'field\');
                  }
                  if (hasSelected(f.operator, -1)) {
                      errors[errors.length] = new Option(\'Operator\', \'operator\');
                  }
                  if (f.value.type == \'select-one\') {
                      if (hasSelected(f.value, -1)) {
                          errors[errors.length] = new Option(\'Value\', \'value\');
                      }
                  } else if (f.value.type == \'text\') {
                      if (isWhitespace(f.value.value)) {
                          errors[errors.length] = new Option(\'Value\', \'value\');
                      }
                  }
                  return true;
              }
              function setValueField(f)
              {
                  window.location.href = url + \'?rem_id=\' + rem_id + \'&rma_id=\' + rma_id + \'&field=\' + getSelectedOption(f, \'field\');
              }
              //-->
              </script>
              '; ?>

              <form name="reminder_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
" onSubmit="javascript:return checkFormSubmission(this, 'validateForm');">
              <input type="hidden" name="rem_id" value="<?php echo $this->_tpl_vars['rem_id']; ?>
">
              <input type="hidden" name="rma_id" value="<?php echo $this->_tpl_vars['rma_id']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td colspan="2" class="default">
                  <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="left" class="default">
                        <b>Manage Alert Conditions</b>
                      </td>
                      <td align="right" class="default">
                        (<a href="reminders.php?cat=edit&id=<?php echo $this->_tpl_vars['rem_id']; ?>
" class="link" title="view alert details">Alert #<?php echo $this->_tpl_vars['rem_id']; ?>
: <?php echo $this->_tpl_vars['rem_title']; ?>
</a> -> <a href="reminder_actions.php?cat=edit&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&id=<?php echo $this->_tpl_vars['rma_id']; ?>
" class="link" title="view alert action details">Action #<?php echo $this->_tpl_vars['rma_id']; ?>
: <?php echo $this->_tpl_vars['rma_title']; ?>
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
                      An error occurred while trying to add the new condition.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this new condition.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the condition was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the condition information.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this condition.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the condition was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Field:</b>
                </td>
                <td width="90%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="field" class="default" onChange="javascript:setValueField(this.form);">
                    <option value="-1">Please choose an option</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['fields'],'selected' => $this->_tpl_vars['info']['rlc_rmf_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'field')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Operator:</b>
                </td>
                <td width="90%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="operator" class="default">
                    <option value="-1">Please choose an option</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['operators'],'selected' => $this->_tpl_vars['info']['rlc_rmo_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'operator')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Value:</b>
                </td>
                <td width="90%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <?php if ($this->_tpl_vars['show_status_options'] == 'yes' || $this->_tpl_vars['show_category_options'] == 'yes'): ?>
                  <select name="value" class="default">
                    <option value="-1">Please choose an option</option>
                    <?php if ($this->_tpl_vars['show_status_options'] == 'yes'): ?>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['statuses'],'selected' => $this->_tpl_vars['info']['rlc_value']), $this);?>

                    <?php elseif ($this->_tpl_vars['show_category_options'] == 'yes'): ?>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['categories'],'selected' => $this->_tpl_vars['info']['rlc_value']), $this);?>

                    <?php endif; ?>
                  </select>
                  <?php else: ?>
                  <input type="text" size="5" name="value" class="default" value="<?php echo $this->_tpl_vars['info']['rlc_value']; ?>
">
                  <span class="small_default"><i>(in hours please)</i></span>
                  <?php endif; ?>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'value')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Condition">
                  <?php else: ?>
                  <input class="button" type="submit" value="Add Condition">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Conditions:</b> (<a href="reminder_actions.php?cat=edit&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&id=<?php echo $this->_tpl_vars['rma_id']; ?>
" class="link">Back to Alert Action List</a>)
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <script language="JavaScript">
                  <!--
                  var rem_id = '<?php echo $this->_tpl_vars['rem_id']; ?>
';
                  var rma_id = '<?php echo $this->_tpl_vars['rma_id']; ?>
';
                  <?php echo '
                  function reviewSQL()
                  {
                      var features = \'width=420,height=300,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
                      var popupWin = window.open(\'reminder_review.php?rem_id=\' + rem_id + \'&rma_id=\' + rma_id, \'_reviewSQL\', features);
                      popupWin.focus();
                  }
                  function checkDelete(f)
                  {
                      if (!hasOneChecked(f, \'items[]\')) {
                          alert(\'Please select at least one of the conditions.\');
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
                    <input type="hidden" name="rma_id" value="<?php echo $this->_tpl_vars['rma_id']; ?>
">
                    <input type="hidden" name="rem_id" value="<?php echo $this->_tpl_vars['rem_id']; ?>
">
                    <input type="hidden" name="cat" value="delete">
                    <tr>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Field</b></td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Operator</b></td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Value</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rlc_id']; ?>
"></td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&rem_id=<?php echo $this->_tpl_vars['rem_id']; ?>
&rma_id=<?php echo $this->_tpl_vars['rma_id']; ?>
&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['rlc_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['rmf_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a></td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['rmo_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                      <td width="33%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['rlc_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No conditions could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                        <table width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="left">
                              <input type="submit" value="Delete" class="button">
                            </td>
                            <td width="100%" align="center">
                              <input type="button" value="Review SQL Query" class="button" onClick="javascript:reviewSQL();">
                            </td>
                          </tr>
                        </table>
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
