<?php /* Smarty version 2.6.2, created on 2004-06-25 09:20:17
         compiled from manage/general.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'manage/general.tpl.html', 264, false),array('function', 'html_options', 'manage/general.tpl.html', 368, false),)), $this); ?>

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
                  var field = getFormElement(f, \'smtp[from]\');
                  if (isWhitespace(field.value)) {
                      alert(\'Please enter the sender address that will be used for all outgoing notification emails.\');
                      selectField(f, \'smtp[from]\');
                      return false;
                  }
                  field = getFormElement(f, \'smtp[host]\');
                  if (isWhitespace(field.value)) {
                      alert(\'Please enter the SMTP server hostname.\');
                      selectField(f, \'smtp[host]\');
                      return false;
                  }
                  field = getFormElement(f, \'smtp[port]\');
                  if ((isWhitespace(field.value)) || (!isNumberOnly(field.value))) {
                      alert(\'Please enter the SMTP server port number.\');
                      selectField(f, \'smtp[port]\');
                      return false;
                  }
                  var field1 = getFormElement(f, \'smtp[auth]\', 0);
                  var field2 = getFormElement(f, \'smtp[auth]\', 1);
                  if ((!field1.checked) && (!field2.checked)) {
                      alert(\'Please indicate whether the SMTP server requires authentication or not.\');
                      return false;
                  }
                  if (field1.checked) {
                      field = getFormElement(f, \'smtp[username]\');
                      if (isWhitespace(field.value)) {
                          alert(\'Please enter the SMTP server username.\');
                          selectField(f, \'smtp[username]\');
                          return false;
                      }
                      field = getFormElement(f, \'smtp[password]\');
                      if (isWhitespace(field.value)) {
                          alert(\'Please enter the SMTP server password.\');
                          selectField(f, \'smtp[password]\');
                          return false;
                      }
                  }
                  var field1 = getFormElement(f, \'smtp[save_outgoing_email]\', 0);
                  var field2 = getFormElement(f, \'smtp[save_address]\');
                  if ((field1.checked) && (!isEmail(field2.value))) {
                      alert(\'Please enter the email address of where copies of outgoing emails should be sent to.\');
                      selectField(f, \'smtp[save_address]\');
                      return false;
                  }
                  if ((!f.open_signup[0].checked) && (!f.open_signup[1].checked))  {
                      alert(\'Please choose whether the system should allow visitors to signup for new accounts or not.\');
                      return false;
                  }
                  if (f.open_signup[0].checked) {
                      field = getFormElement(f, \'accounts_projects[]\');
                      if (!hasOneSelected(f, \'accounts_projects[]\')) {
                          alert(\'Please select the assigned projects for users that create their own accounts.\');
                          selectField(f, \'accounts_projects[]\');
                          return false;
                      }
                  }
                  field1 = getFormElement(f, \'email_routing[status]\', 0);
                  if (field1.checked) {
                      field1 = getFormElement(f, \'email_routing[address_prefix]\');
                      if (isWhitespace(field1.value)) {
                          alert(\'Please enter the email address prefix for the email routing interface.\');
                          selectField(f, \'email_routing[address_prefix]\');
                          return false;
                      }
                      field1 = getFormElement(f, \'email_routing[address_host]\');
                      if (isWhitespace(field1.value)) {
                          alert(\'Please enter the email address hostname for the email routing interface.\');
                          selectField(f, \'email_routing[address_host]\');
                          return false;
                      }
                  }
                  if ((!f.scm_integration[0].checked) && (!f.scm_integration[1].checked))  {
                      alert(\'Please choose whether the SCM integration feature should be enabled or not.\');
                      return false;
                  }
                  if (f.scm_integration[0].checked) {
                      field = getFormElement(f, \'checkout_url\');
                      if (isWhitespace(field.value)) {
                          alert(\'Please enter the checkout page URL for your SCM integration tool.\');
                          selectField(f, \'checkout_url\');
                          return false;
                      }
                      field = getFormElement(f, \'diff_url\');
                      if (isWhitespace(field.value)) {
                          alert(\'Please enter the diff page URL for your SCM integration tool.\');
                          selectField(f, \'diff_url\');
                          return false;
                      }
                  }
                  if ((!f.support_email[0].checked) && (!f.support_email[1].checked))  {
                      alert(\'Please choose whether the email integration feature should be enabled or not.\');
                      return false;
                  }
                  if ((!f.daily_tips[0].checked) && (!f.daily_tips[1].checked))  {
                      alert(\'Please choose whether the daily tips feature should be enabled or not.\');
                      return false;
                  }
                  return true;
              }
              function disableAuthFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'smtp[username]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'smtp[password]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              function checkDebugField(f)
              {
                  var field = getFormElement(f, \'smtp[save_outgoing_email]\');
                  if (field.checked) {
                      var bool = false;
                  } else {
                      var bool = true;
                  }
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  field = getFormElement(f, \'smtp[save_address]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              function disableSCMFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'checkout_url\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'diff_url\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              function disableSignupFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'accounts_projects[]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'accounts_role\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              function disableEmailRoutingFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'email_routing[recipient_type_flag]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'email_routing[flag_location]\', 0);
                  field.disabled = bool;
                  field = getFormElement(f, \'email_routing[flag_location]\', 1);
                  field.disabled = bool;
                  field = getFormElement(f, \'email_routing[address_prefix]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'email_routing[address_host]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'email_routing[host_alias]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'email_routing[warning][status]\', 0);
                  field.disabled = bool;
                  field = getFormElement(f, \'email_routing[warning][status]\', 1);
                  field.disabled = bool;
              }
              function disableNoteRoutingFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'note_routing[recipient_type_flag]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'note_routing[flag_location]\', 0);
                  field.disabled = bool;
                  field = getFormElement(f, \'note_routing[flag_location]\', 1);
                  field.disabled = bool;
                  field = getFormElement(f, \'note_routing[address_prefix]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'note_routing[address_host]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              function disableErrorEmailFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'email_error[addresses]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              //-->
              </script>
              '; ?>

              <form name="general_setup_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <input type="hidden" name="cat" value="update">
              <tr>
                <td colspan="2" class="default">
                  <b>General Setup</b>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($this->_tpl_vars['result'] == -1): ?>
                    ERROR: The system doesn't have the appropriate permissions to 
                    create the configuration file in the setup directory 
                    (<?php echo $this->_tpl_vars['app_setup_path']; ?>
). Please contact your local system 
                    administrator and ask for write privileges on the provided path.
                  <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                    ERROR: The system doesn't have the appropriate permissions to 
                    update the configuration file in the setup directory 
                    (<?php echo $this->_tpl_vars['app_setup_file']; ?>
). Please contact your local system 
                    administrator and ask for write privileges on the provided filename.
                  <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                    Thank you, the setup information was saved successfully.
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Tool Caption:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" class="default" name="tool_caption" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['tool_caption'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>SMTP (Outgoing Email) Settings:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td width="100" class="default" align="right">
                        Sender:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="smtp[from]" size="30" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['smtp']['from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[from]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Hostname:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="smtp[host]" size="30" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['smtp']['host'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[host]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Port:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="smtp[port]" size="10" value="<?php echo $this->_tpl_vars['setup']['smtp']['port']; ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[port]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Requires authentication?&nbsp;
                      </td>
                      <td width="80%" class="default">
                        <input type="radio" name="smtp[auth]" value="1" <?php if ($this->_tpl_vars['setup']['smtp']['auth']): ?>checked<?php endif; ?> onClick="javascript:disableAuthFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'smtp[auth]', 0);disableAuthFields(getForm('general_setup_form'), false);">Yes</a>&nbsp;&nbsp;
                        <input type="radio" name="smtp[auth]" value="0" <?php if (! $this->_tpl_vars['setup']['smtp']['auth']): ?>checked<?php endif; ?> onClick="javascript:disableAuthFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'smtp[auth]', 1);disableAuthFields(getForm('general_setup_form'), true);">No</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Username:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="smtp[username]" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['smtp']['username'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[username]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Password:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="password" class="default" name="smtp[password]" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['smtp']['password'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[password]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" class="default">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="smtp[save_outgoing_email]" value="yes" <?php if ($this->_tpl_vars['setup']['smtp']['save_outgoing_email'] == 'yes'): ?>checked<?php endif; ?> onClick="javascript:checkDebugField(this.form);">
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('general_setup_form', 'smtp[save_outgoing_email]');checkDebugField(getForm('general_setup_form'));">Save a Copy of Every Outgoing Issue Notification Email</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Email Address to Send Saved Messages:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="smtp[save_address]" class="default" size="30" value="<?php echo $this->_tpl_vars['setup']['smtp']['save_address']; ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "smtp[save_address]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Open Account Signup:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td colspan="2" class="default_white">
                        <input type="radio" name="open_signup" value="enabled" <?php if ($this->_tpl_vars['setup']['open_signup'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableSignupFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'open_signup', 0);disableSignupFields(getForm('general_setup_form'), false);">Enabled</a>&nbsp;&nbsp;
                        <input type="radio" name="open_signup" value="disabled" <?php if (! $this->_tpl_vars['setup']['open_signup'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableSignupFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'open_signup', 1);disableSignupFields(getForm('general_setup_form'), true);">Disabled</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Assigned Projects:&nbsp;
                      </td>
                      <td width="80%">
                        <select name="accounts_projects[]" multiple size="3" class="default">
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['project_list'],'selected' => $this->_tpl_vars['setup']['accounts_projects']), $this);?>

                        </select>
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "accounts_projects[]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Assigned Role:&nbsp;
                      </td>
                      <td width="80%">
                        <select name="accounts_role" class="default">
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['user_roles'],'selected' => $this->_tpl_vars['setup']['accounts_role']), $this);?>

                        </select>
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'accounts_role')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Email Routing Interface:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td colspan="2" class="default_white">
                        <input type="radio" name="email_routing[status]" value="enabled" <?php if ($this->_tpl_vars['setup']['email_routing']['status'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableEmailRoutingFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[status]', 0);disableEmailRoutingFields(getForm('general_setup_form'), false);">Enabled</a>&nbsp;&nbsp;
                        <input type="radio" name="email_routing[status]" value="disabled" <?php if ($this->_tpl_vars['setup']['email_routing']['status'] != 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableEmailRoutingFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[status]', 1);disableEmailRoutingFields(getForm('general_setup_form'), true);">Disabled</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Recipient Type Flag:&nbsp;
                      </td>
                      <td width="80%">
                        <input class="default" type="text" name="email_routing[recipient_type_flag]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['email_routing']['recipient_type_flag'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><br />
                        <span class="default">
                        <input type="radio" name="email_routing[flag_location]" value="before" <?php if ($this->_tpl_vars['setup']['email_routing']['flag_location'] == 'before'): ?>checked<?php endif; ?>> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[flag_location]', 0);">Before Sender Name</a>&nbsp;&nbsp;
                        <input type="radio" name="email_routing[flag_location]" value="after" <?php if ($this->_tpl_vars['setup']['email_routing']['flag_location'] != 'before'): ?>checked<?php endif; ?>> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[flag_location]', 1);">After Sender Name</a>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Email Address Prefix:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="email_routing[address_prefix]" value="<?php if ($this->_tpl_vars['setup']['email_routing']['address_prefix']):  echo $this->_tpl_vars['setup']['email_routing']['address_prefix'];  else: ?>eventum_<?php endif; ?>" class="default">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "email_routing[address_prefix]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        <span class="small_default">(i.e. <b>issue_</b>51@example.com)</span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Address Hostname:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="email_routing[address_host]" class="default" value="<?php echo $this->_tpl_vars['setup']['email_routing']['address_host']; ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "email_routing[address_host]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        <span class="small_default">(i.e. issue_51@<b>example.com</b>)</span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Host Alias:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="email_routing[host_alias]" class="default" value="<?php echo $this->_tpl_vars['setup']['email_routing']['host_alias']; ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "email_routing[host_alias]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        <span class="small_default">(Alternate domains that point to 'Address Hostname')</span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Warn Users Whether They Can Send Emails to Issue:&nbsp;
                      </td>
                      <td width="80%" class="default">
                        <input type="radio" name="email_routing[warning][status]" value="enabled" <?php if ($this->_tpl_vars['setup']['email_routing']['warning']['status'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableWarningFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[warning][status]', 0);">Yes</a>&nbsp;&nbsp;
                        <input type="radio" name="email_routing[warning][status]" value="disabled" <?php if ($this->_tpl_vars['setup']['email_routing']['warning']['status'] != 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableWarningFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_routing[warning][status]', 1);">No</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Internal Note Routing Interface:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td colspan="2" class="default_white">
                        <input type="radio" name="note_routing[status]" value="enabled" <?php if ($this->_tpl_vars['setup']['note_routing']['status'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableNoteRoutingFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'note_routing[status]', 0);disableNoteRoutingFields(getForm('general_setup_form'), false);">Enabled</a>&nbsp;&nbsp;
                        <input type="radio" name="note_routing[status]" value="disabled" <?php if ($this->_tpl_vars['setup']['note_routing']['status'] != 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableNoteRoutingFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'note_routing[status]', 1);disableNoteRoutingFields(getForm('general_setup_form'), true);">Disabled</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Recipient Type Flag:&nbsp;
                      </td>
                      <td width="80%">
                        <input class="default" type="text" name="note_routing[recipient_type_flag]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['note_routing']['recipient_type_flag'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><br />
                        <span class="default">
                        <input type="radio" name="note_routing[flag_location]" value="before" <?php if ($this->_tpl_vars['setup']['note_routing']['flag_location'] == 'before'): ?>checked<?php endif; ?>> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'note_routing[flag_location]', 0);">Before Sender Name</a>&nbsp;&nbsp;
                        <input type="radio" name="note_routing[flag_location]" value="after" <?php if ($this->_tpl_vars['setup']['note_routing']['flag_location'] != 'before'): ?>checked<?php endif; ?>> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'note_routing[flag_location]', 1);">After Sender Name</a>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        note Address Prefix:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="note_routing[address_prefix]" value="<?php if ($this->_tpl_vars['setup']['note_routing']['address_prefix']):  echo $this->_tpl_vars['setup']['note_routing']['address_prefix'];  else: ?>eventum_<?php endif; ?>" class="default">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "note_routing[address_prefix]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        <span class="small_default">(i.e. <b>note_</b>51@example.com)</span>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Address Hostname:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" name="note_routing[address_host]" class="default" value="<?php echo $this->_tpl_vars['setup']['note_routing']['address_host']; ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "note_routing[address_host]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        <span class="small_default">(i.e. note_51@<b>example.com</b>)</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>SCM <br />Integration:</b> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'scm_integration')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td colspan="2" class="default_white">
                        <input type="radio" name="scm_integration" value="enabled" <?php if ($this->_tpl_vars['setup']['scm_integration'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableSCMFields(this.form, false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'scm_integration', 0);disableSCMFields(getForm('general_setup_form'), false);">Enabled</a>&nbsp;&nbsp;
                        <input type="radio" name="scm_integration" value="disabled" <?php if (! $this->_tpl_vars['setup']['scm_integration'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableSCMFields(this.form, true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'scm_integration', 1);disableSCMFields(getForm('general_setup_form'), true);">Disabled</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Checkout Page:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="checkout_url" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['checkout_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'checkout_url')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Diff Page:&nbsp;
                      </td>
                      <td width="80%">
                        <input type="text" class="default" name="diff_url" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['diff_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'diff_url')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Email Integration Feature:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="support_email" value="enabled" <?php if ($this->_tpl_vars['setup']['support_email'] == 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'support_email', 0);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="support_email" value="disabled" <?php if ($this->_tpl_vars['setup']['support_email'] != 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'support_email', 1);">Disabled</a>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Daily Tips:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="daily_tips" value="enabled" <?php if ($this->_tpl_vars['setup']['daily_tips'] == 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'daily_tips', 0);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="daily_tips" value="disabled" <?php if ($this->_tpl_vars['setup']['daily_tips'] != 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'daily_tips', 1);">Disabled</a>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Email Spell Checker:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <span class="default">
                  <input type="radio" name="spell_checker" value="enabled" <?php if ($this->_tpl_vars['setup']['spell_checker'] == 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'spell_checker', 0);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="spell_checker" value="disabled" <?php if ($this->_tpl_vars['setup']['spell_checker'] != 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'spell_checker', 1);">Disabled</a></span>
                  &nbsp;&nbsp;<span class="small_default">(requires <a target="_aspell" class="link" href="http://aspell.sourceforge.net/">aspell</a> installed in your server)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>IRC Notifications:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="irc_notification" value="enabled" <?php if ($this->_tpl_vars['setup']['irc_notification'] == 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'irc_notification', 0);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="irc_notification" value="disabled" <?php if ($this->_tpl_vars['setup']['irc_notification'] != 'enabled'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'irc_notification', 1);">Disabled</a>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Allow Un-Assigned Issues?</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="allow_unassigned_issues" value="yes" <?php if ($this->_tpl_vars['setup']['allow_unassigned_issues'] == 'yes'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'allow_unassigned_issues', 0);">Yes</a>&nbsp;&nbsp;
                  <input type="radio" name="allow_unassigned_issues" value="no" <?php if ($this->_tpl_vars['setup']['allow_unassigned_issues'] != 'yes'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'allow_unassigned_issues', 1);">No</a>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Default Options for Notifications:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="checkbox" name="update" <?php if ($this->_tpl_vars['setup']['update']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('general_setup_form', 'update');">Issues are Updated</a><br />
                  <input type="checkbox" name="closed" <?php if ($this->_tpl_vars['setup']['closed']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('general_setup_form', 'closed');">Issues are Closed</a><br />
                  <input type="checkbox" name="emails" <?php if ($this->_tpl_vars['setup']['emails']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('general_setup_form', 'emails');">Emails are Associated</a><br />
                  <input type="checkbox" name="files" <?php if ($this->_tpl_vars['setup']['files']): ?>checked<?php endif; ?> value="1"> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('general_setup_form', 'files');">Files are Attached</a>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Email Error Logging System:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <table>
                    <tr>
                      <td colspan="2" class="default_white">
                        <input type="radio" name="email_error[status]" value="enabled" <?php if ($this->_tpl_vars['setup']['email_error']['status'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableErrorEmailFields(getForm('general_setup_form'), false);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_error[status]', 0);disableErrorEmailFields(getForm('general_setup_form'), false);">Enabled</a>&nbsp;&nbsp;
                        <input type="radio" name="email_error[status]" value="disabled" <?php if ($this->_tpl_vars['setup']['email_error']['status'] != 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableErrorEmailFields(getForm('general_setup_form'), true);"> 
                        <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('general_setup_form', 'email_error[status]', 1);disableErrorEmailFields(getForm('general_setup_form'), true);">Disabled</a>
                      </td>
                    </tr>
                    <tr>
                      <td width="100" class="default" align="right">
                        Email Addresses To Send Errors To:&nbsp;
                      </td>
                      <td width="80%">
                        <input class="default" type="text" name="email_error[addresses]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['setup']['email_error']['addresses'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" size="50">
                        <span class="small_default">(separate multiple addresses with commas)</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <input class="button" type="submit" value="Update Setup">
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
            </table>
          </td>
        </tr>
      </table>
      <?php echo '
      <script language="JavaScript">
      <!--
      window.onload = setDisabledFields;
      function setDisabledFields()
      {
          var f = getForm(\'general_setup_form\');
          var field1 = getFormElement(f, \'smtp[auth]\', 0);
          if (field1.checked) {
              disableAuthFields(f, false);
          } else {
              disableAuthFields(f, true);
          }
          checkDebugField(f);
          if (f.scm_integration[0].checked) {
              disableSCMFields(f, false);
          } else {
              f.scm_integration[1].checked = true;
              disableSCMFields(f, true);
          }
          if (f.open_signup[0].checked) {
              disableSignupFields(f, false);
          } else {
              f.open_signup[1].checked = true;
              disableSignupFields(f, true);
          }
          field1 = getFormElement(f, \'email_routing[status]\', 0);
          var field2 = getFormElement(f, \'email_routing[status]\', 1);
          if (field1.checked) {
              disableEmailRoutingFields(f, false);
          } else {
              field2.checked = true;
              disableEmailRoutingFields(f, true);
          }
          field1 = getFormElement(f, \'note_routing[status]\', 0);
          field2 = getFormElement(f, \'note_routing[status]\', 1);
          if (field1.checked) {
              disableNoteRoutingFields(f, false);
          } else {
              field2.checked = true;
              disableNoteRoutingFields(f, true);
          }
          field1 = getFormElement(f, \'email_error[status]\', 0);
          field2 = getFormElement(f, \'email_error[status]\', 1);
          if (field1.checked) {
              disableErrorEmailFields(f, false);
          } else {
              field2.checked = true;
              disableErrorEmailFields(f, true);
          }
      }
      //-->
      </script>
      '; ?>

