<?php /* Smarty version 2.6.2, created on 2004-07-02 10:53:33
         compiled from manage/email_accounts.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'manage/email_accounts.tpl.html', 114, false),array('function', 'cycle', 'manage/email_accounts.tpl.html', 248, false),array('modifier', 'escape', 'manage/email_accounts.tpl.html', 146, false),array('modifier', 'ucfirst', 'manage/email_accounts.tpl.html', 258, false),)), $this); ?>

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
                  if (f.project.selectedIndex == 0) {
                      alert(\'Please choose the team to be associated with this email account.\');
                      selectField(f, \'project\');
                      return false;
                  }
                  if (f.type.selectedIndex == 0) {
                      alert(\'Please choose the type of email server to be associated with this email account.\');
                      selectField(f, \'type\');
                      return false;
                  }
                  if (isWhitespace(f.hostname.value)) {
                      alert(\'Please enter the hostname for this email account.\');
                      selectField(f, \'hostname\');
                      return false;
                  }
                  if (!isNumberOnly(f.port.value)) {
                      alert(\'Please enter the port number for this email account.\');
                      selectField(f, \'port\');
                      return false;
                  }
                  var server_type = getSelectedOption(f, \'type\');
                  if ((server_type.indexOf(\'imap\') != -1) && (isWhitespace(f.folder.value))) {
                      alert(\'Please enter the IMAP folder for this email account.\');
                      selectField(f, \'folder\');
                      return false;
                  }
                  if (isWhitespace(f.username.value)) {
                      alert(\'Please enter the username for this email account.\');
                      selectField(f, \'username\');
                      return false;
                  }
                  if (isWhitespace(f.password.value)) {
                      alert(\'Please enter the password for this email account.\');
                      selectField(f, \'password\');
                      return false;
                  }
                  return true;
              }
              function toggleFolderField(f)
              {
                  var element = getPageElement(\'imap_folder\');
                  var option = getSelectedOption(f, \'type\');
                  if (option.indexOf(\'imap\') != -1) {
                      element.style.display = getDisplayStyle();
                      f.folder.disabled = false;
                  } else {
                      element.style.display = \'none\';
                      f.folder.disabled = true;
                  }
              }
              function testSettings(f)
              {
                  var features = \'width=320,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
                  var popupWin = window.open(\'\', \'_testEmailSettings\', features);
                  popupWin.focus();
                  var old_action = f.action;
                  f.action = \'check_email_settings.php\';
                  f.target = \'_testEmailSettings\';
                  f.submit();
                  f.action = old_action;
                  f.target = \'\';
              }
              //-->
              </script>
              '; ?>

              <form name="email_account_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
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
                  <b>Manage Email Accounts</b>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new account.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the email account was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the account information.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the account was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <nobr><b>Associated Team:</b></nobr>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <select name="project" class="default">
                    <option value="-1"></option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['all_projects'],'selected' => $this->_tpl_vars['info']['ema_prj_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'project')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Type:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <select name="type" class="default" onChange="javascript:toggleFolderField(this.form);">
                    <option value="-1"></option>
                    <option value="imap" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap'): ?>selected<?php endif; ?>>IMAP</option>
                    <option value="imap/ssl" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap/ssl'): ?>selected<?php endif; ?>>IMAP over SSL</option>
                    <option value="imap/ssl/novalidate-cert" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap/ssl/novalidate-cert'): ?>selected<?php endif; ?>>IMAP over SSL (self-signed)</option>
                    <option value="imap/notls" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap/notls'): ?>selected<?php endif; ?>>IMAP, no TLS</option>
                    <option value="imap/tls" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap/tls'): ?>selected<?php endif; ?>>IMAP, with TLS</option>
                    <option value="imap/tls/novalidate-cert" <?php if ($this->_tpl_vars['info']['ema_type'] == 'imap/tls/novalidate-cert'): ?>selected<?php endif; ?>>IMAP, with TLS (self-signed)</option>
                    <option value="pop3" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3'): ?>selected<?php endif; ?>>POP3</option>
                    <option value="pop3/ssl" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3/ssl'): ?>selected<?php endif; ?>>POP3 over SSL</option>
                    <option value="pop3/ssl/novalidate-cert" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3/ssl/novalidate-cert'): ?>selected<?php endif; ?>>POP3 over SSL (self-signed)</option>
                    <option value="pop3/notls" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3/notls'): ?>selected<?php endif; ?>>POP3, no TLS</option>
                    <option value="pop3/tls" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3/tls'): ?>selected<?php endif; ?>>POP3, with TLS</option>
                    <option value="pop3/tls/novalidate-cert" <?php if ($this->_tpl_vars['info']['ema_type'] == 'pop3/tls/novalidate-cert'): ?>selected<?php endif; ?>>POP3, with TLS (self-signed)</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Hostname:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <input type="text" class="default" name="hostname" size="30" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['ema_hostname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'hostname')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Port:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <input type="text" class="default" name="port" size="10" value="<?php echo $this->_tpl_vars['info']['ema_port']; ?>
"> <span class="default">(defaults are 110 for POP3 servers and 143 for IMAP ones)</span>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'port')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr id="imap_folder">
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>IMAP Folder:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <input type="text" class="default" name="folder" size="20" value="<?php if ($this->_tpl_vars['info']['ema_folder'] == ""): ?>INBOX<?php else:  echo $this->_tpl_vars['info']['ema_folder'];  endif; ?>"> <span class="default">(default folder is INBOX)</span>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'folder')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Username:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <input type="text" class="default" name="username" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['ema_username'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'username')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Password:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <input type="password" class="default" name="password" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['ema_password'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'password')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="100" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Advanced Options:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%" class="default">
                  <input type="checkbox" name="get_only_new" value="1" <?php if ($this->_tpl_vars['info']['ema_get_only_new']): ?>checked<?php endif; ?>>
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('email_account_form', 'get_only_new', 0);">Only Download Unread Messages</a><br />
                  <input type="checkbox" name="leave_copy" value="1" <?php if ($_GET['cat'] == 'edit'):  if ($this->_tpl_vars['info']['ema_leave_copy']): ?>checked<?php endif;  else: ?>checked<?php endif; ?>>
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('email_account_form', 'leave_copy', 0);">Leave Copy of Messages On Server</a>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <input class="button" type="button" value="Test Settings" onClick="javascript:testSettings(this.form);">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Account">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create Account">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Accounts:</b>
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
                          alert(\'Please select at least one of the accounts.\');
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
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Associated Team</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Hostname</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Type</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Port</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Username</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Mailbox</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Auto-Creation of Issues</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_id']; ?>
"></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['prj_title']; ?>
</td>
                      <td width="30%"bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_hostname'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_type']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_port']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_username'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_folder']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<a href="issue_auto_creation.php?ema_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_id']; ?>
" class="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['ema_issue_auto_creation'])) ? $this->_run_mod_handler('ucfirst', true, $_tmp) : ucfirst($_tmp)); ?>
</a></td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="8" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No email accounts could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                      <td width="4" align="center">
                        <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');">
                      </td>
                      <td colspan="7" align="center">
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
      window.onload = setFolderField;
      function setFolderField()
      {
          var f = getForm(\'email_account_form\');
          toggleFolderField(f);
      }
      //-->
      </script>
      '; ?>

