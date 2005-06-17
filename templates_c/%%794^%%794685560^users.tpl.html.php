<?php /* Smarty version 2.6.2, created on 2005-06-06 15:05:24
         compiled from manage/users.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'manage/users.tpl.html', 160, false),array('modifier', 'capitalize', 'manage/users.tpl.html', 228, false),array('function', 'cycle', 'manage/users.tpl.html', 207, false),)), $this); ?>

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
                  if (isWhitespace(f.username.value)) {
                      alert(\'Please enter the username of this user.\');
                      selectField(f, \'username\');
                      return false;
                  }
                  if (isWhitespace(f.email.value)) {
                      alert(\'Please enter the email of this user.\');
                      selectField(f, \'email\');
                      return false;
                  }
                  if (!isEmail(f.email.value)) {
                      alert(\'Please enter a valid email address.\');
                      selectField(f, \'email\');
                      return false;
                  }
                  if (isWhitespace(f.full_name.value)) {
                      alert(\'Please enter the full name of this user.\');
                      selectField(f, \'full_name\');
                      return false;
                  }

                  if (!(f.ldap_authentication.checked) && (isWhitespace(f.password.value))) {
                      alert(\'If you are not using LDAP for authentication you must enter a password for the user.\');
                      selectField(f, \'password\');
                      return false;
                  }

                  return true;
              }
              function togglePasswordText()
              {
                  var f = getForm(\'user_form\');
//				  var elementTitle = getPageElement(\'password\');
				  if (f.ldap_authentication.checked) {
					  f.password.disabled = true;
				  } else {
					  f.password.disabled = false;
				  }
                  return true;
              }
              //-->
              </script>
              '; ?>

              <form name="user_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
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
                  <b>Manage Users</b>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new user.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the user was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the user information.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the user was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Username:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="username" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['usr_username']; ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'username')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>E-mail:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="email" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['usr_email']; ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'email')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Full Name:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="full_name" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['usr_full_name']; ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'full_name')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Administrator?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				  <input type="checkbox" name="administrator" <?php if ($this->_tpl_vars['info']['usr_administrator'] == 1): ?>checked<?php endif; ?>>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Use LDAP Authentication?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				  <input type="checkbox" name="ldap_authentication" <?php if ($this->_tpl_vars['info']['usr_ldap_authentication'] == 1): ?>checked<?php endif; ?> onClick="javascript:togglePasswordText();">
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Password:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="password" name="password" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['usr_password']; ?>
"> <i>(Only for Non-LDAP authentication)</i>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'full_name')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update User">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create User">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Users:</b>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <script language="JavaScript">
                  <!--
                  var active_users = new Array();
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
                  <?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_status'] == 'active'): ?>
                  active_users[<?php echo $this->_sections['i']['index']; ?>
] = '<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
';
                  <?php endif; ?>
                  <?php endfor; endif; ?>
                  var page_url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
                  <?php echo '
                  function checkDelete(f)
                  {
                      var total_selected = getTotalCheckboxesChecked(f, \'items[]\');
                      var total = getTotalCheckboxes(f, \'items[]\');
                      if (getSelectedOption(f, \'status\') == \'inactive\') {
                          if (active_users.length < 2) {
                              alert(\'You cannot change the status of the only active user left in the system.\');
                              return false;
                          }
                          if (total == total_selected) {
                              alert(\'You cannot inactivate all of the users in the system.\');
                              return false;
                          }
                      }
                      if (!hasOneChecked(f, \'items[]\')) {
                          alert(\'Please select at least one of the users.\');
                          return false;
                      }
                      if (!confirm(\'This action will change the status of the selected users.\')) {
                          return false;
                      } else {
                          return true;
                      }
                  }
                  //-->
                  </script>
                  '; ?>

                  <table border="0" width="100%" cellpadding="1" cellspacing="1">
                    <form name="change_status_form" onSubmit="javascript:return checkDelete(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
                    <input type="hidden" name="cat" value="change_status">
                    <tr>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Name</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Username</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Administrator?</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>LDAP Authentication?</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>E-mail</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Status</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>Login Count</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Last Login Date</b></td>
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
" align="center">
                        <input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_id']; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_id']; ?>
" title="update this entry"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_full_name']; ?>
</a>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_username']; ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_administrator'] == 1): ?>Yes<?php else: ?>No<?php endif; ?>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_ldap_authentication'] == 1): ?>Yes<?php else: ?>No<?php endif; ?>
                      </td>
                      <td width="40%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a href="mailto:<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_email']; ?>
" class="link" title="send email to <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_email']; ?>
"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_email']; ?>
</a>
                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_status'])) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp)); ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_login_count']; ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['usr_last_login_date']; ?>

                      </td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="10" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No users could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="10" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                          <tr>
                            <td>
                              <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');">
                              <input type="submit" value="Update Status &gt;&gt;" class="button">
                              <select name="status" class="default">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                              </select>
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
              <?php echo '
              <script language="JavaScript">
              <!--
				togglePasswordText();
			  -->
			  </script>
			  '; ?>

