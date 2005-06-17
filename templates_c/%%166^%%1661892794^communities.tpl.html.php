<?php /* Smarty version 2.6.2, created on 2005-05-09 10:14:19
         compiled from manage/communities.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'manage/communities.tpl.html', 105, false),array('modifier', 'capitalize', 'manage/communities.tpl.html', 112, false),)), $this); ?>

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
                  checkLeadSelection(f);
                  if (isWhitespace(f.title.value)) {
                      alert(\'Please enter the title of this community.\');
                      selectField(f, \'title\');
                      return false;
                  }
                  if (!hasOneSelected(f, \'users[]\')) {
                      alert(\'Please assign the users for this community.\');
                      selectField(f, \'users[]\');
                      return false;
                  }
                  if (!hasOneSelected(f, \'statuses[]\')) {
                      alert(\'Please assign the statuses for this community.\');
                      selectField(f, \'statuses[]\');
                      return false;
                  }
                  // the selected initial status should be one of the selected assigned statuses
                  initial_status = getSelectedOption(f, \'initial_status\');
                  assigned_statuses = getFormElement(f, \'statuses[]\');
                  var found = 0;
                  for (var i = 0; i < assigned_statuses.options.length; i++) {
                      if ((assigned_statuses.options[i].selected) && (initial_status == assigned_statuses.options[i].value)) {
                          found = 1;
                      }
                  }
                  if (!found) {
                      alert(\'Please choose the initial status from one of the assigned statuses of this community.\');
                      selectField(f, \'initial_status\');
                      return false;
                  }
                  if (isWhitespace(f.outgoing_sender_email.value)) {
                      alert(\'Please enter the outgoing sender address for this community.\');
                      selectField(f, \'outgoing_sender_email\');
                      return false;
                  }
                  return true;
              }
              function checkLeadSelection(f)
              {
                  var selection = f.lead_usr_id.options[f.lead_usr_id.selectedIndex].value;
                  selectOption(f, \'users[]\', selection);
              }
              //-->
              </script>
              '; ?>


              <tr>
                <td colspan="2" class="default">
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "report_form.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</td>
			  </tr>		

              <tr>
                <td colspan="2" class="default">
                  <b>Existing Communites:</b>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <?php echo '
                  <script language="JavaScript">
                  <!--
                  function checkDelete(f)
                  {
                      var total_selected = getTotalCheckboxesChecked(f, \'items[]\');
                      var total = getTotalCheckboxes(f, \'items[]\');
                      if (total == total_selected) {
                          alert(\'You cannot remove all of the communitys in the system.\');
                          return false;
                      }
                      if (!hasOneChecked(f, \'items[]\')) {
                          alert(\'Please select at least one of the communitys.\');
                          return false;
                      }
                      if (!confirm(\'WARNING: This action will remove the selected communitys permanently.\\nIt will remove all of its associated entries as well (issues, notes, attachments,\\netc), so please click OK to confirm.\')) {
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
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Primary Community Manager</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Status</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap class="default_white" align="center">&nbsp;<b>Actions</b></td>
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
                      <td width="4" align="center" nowrap bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['prj_id']; ?>
"></td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pid']; ?>
" title="update this entry"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['title']; ?>
</a>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_full_name']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['prj_status'])) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp)); ?>
</td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" nowrap class="default">
                        <ul>
                          <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/categories.php?pid=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pid']; ?>
" class="link">Edit Categories</a></li>
                        </ul>
                      </td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No communitys could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                        <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');">
                      </td>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
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
