<?php /* Smarty version 2.6.2, created on 2004-06-25 09:13:41
         compiled from setup.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'setup.tpl.html', 131, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('application_title' => 'Eventum Installation')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<script language="JavaScript">
<!--
function validateForm(f)
{
    if (isWhitespace(f.hostname.value)) {
        selectField(f, \'hostname\');
        alert(\'Please enter the hostname for the server of this installation of Eventum.\');
        return false;
    }
    if (isWhitespace(f.relative_url.value)) {
        selectField(f, \'relative_url\');
        alert(\'Please enter the relative URL of this installation of Eventum.\');
        return false;
    }
    if (isWhitespace(f.path.value)) {
        selectField(f, \'path\');
        alert(\'Please enter the full path in the server of this installation of Eventum.\');
        return false;
    }
    if (isWhitespace(f.db_hostname.value)) {
        selectField(f, \'db_hostname\');
        alert(\'Please enter the database hostname for this installation of Eventum.\');
        return false;
    }
    if (isWhitespace(f.db_name.value)) {
        selectField(f, \'db_name\');
        alert(\'Please enter the database name for this installation of Eventum.\');
        return false;
    }
    if (isWhitespace(f.db_username.value)) {
        selectField(f, \'db_username\');
        alert(\'Please enter the database username for this installation of Eventum.\');
        return false;
    }
    if (f.alternate_user.checked) {
        if (isWhitespace(f.eventum_user.value)) {
            selectField(f, \'eventum_user\');
            alert(\'Please enter the alternate username for this installation of Eventum.\');
            return false;
        }
    }
    return true;
}
function toggleAlternateUserFields()
{
    var f = getForm(\'install_form\');
    var element = getPageElement(\'alternate_user_row\');
    if (f.alternate_user.checked) {
        element.style.display = \'\';
        f.eventum_user.focus();
    } else {
        element.style.display = \'none\';
        f.alternate_user.focus();
    }
}
//-->
</script>
'; ?>


<?php if ($this->_tpl_vars['result'] != '' && $this->_tpl_vars['result'] != 'success'): ?>
<br />
<table width="400" bgcolor="#003366" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/error.gif" hspace="2" vspace="2" border="0" align="left"></td>
          <td width="100%" class="default"><span style="font-weight: bold; font-size: 160%; color: red;">An Error Was Found</span></td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            <br />
            <b>Details: <?php echo $this->_tpl_vars['result']; ?>
</b>
            <br /><br />
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php endif; ?>

<?php if ($this->_tpl_vars['result'] == 'success'): ?>
<br />
<table width="400" bgcolor="#003366" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td width="100%" class="default"><span style="font-weight: bold; font-size: 160%;">Success!</span></td>
        </tr>
        <tr>
          <td class="default">
            <br />
            <b>Thank You, Eventum is now properly setup and ready to be used. Open the following URL to login on it for the first time:</b>
            <br />
            <a class="link" href="<?php if ($_POST['is_ssl'] == 'yes'): ?>https://<?php else: ?>http://<?php endif;  echo $_POST['hostname'];  echo $_POST['relative_url']; ?>
"><?php if ($_POST['is_ssl'] == 'yes'): ?>https://<?php else: ?>http://<?php endif;  echo $_POST['hostname'];  echo $_POST['relative_url']; ?>
</a>
            <br /><br />
            Email Address: admin@example.com (literally)<br />
            Password: admin<br />
            <br />
            <b>NOTE: For security reasons it is highly recommended that the default password be changed as soon as possible.</b>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php else: ?>
<br />
<table width="600" bgcolor="#000000" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="install_form" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post" onSubmit="javascript:return validateForm(this);">
<input type="hidden" name="cat" value="install">
  <tr>
    <td>
      <table bgcolor="#CCCCCC" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" align="center">
            <h1>Eventum Installation</h1>
            <hr size="1" noshade color="#000000">
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>Server Hostname: *</b>
          </td>
          <td>
            <input type="text" name="hostname" value="<?php echo ((is_array($_tmp=@$_POST['hostname'])) ? $this->_run_mod_handler('default', true, $_tmp, @$_SERVER['HTTP_HOST']) : smarty_modifier_default($_tmp, @$_SERVER['HTTP_HOST'])); ?>
" class="default" size="30" tabindex="1">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'hostname')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <input type="checkbox" name="is_ssl" value="yes" <?php if ($this->_tpl_vars['ssl_mode'] == 'enabled'): ?>checked<?php endif; ?>> <span class="default"><b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('install_form', 'is_ssl');">SSL Server</a></b></span>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>Eventum Relative URL: *</b>
          </td>
          <td>
            <input type="text" name="relative_url" value="<?php echo ((is_array($_tmp=@$_POST['rel_url'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['rel_url']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['rel_url'])); ?>
" class="default" size="30" tabindex="1">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'relative_url')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>Installation Path: *</b>
          </td>
          <td>
            <input type="text" name="path" value="<?php echo ((is_array($_tmp=@$_POST['path'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['installation_path']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['installation_path'])); ?>
" class="default" size="50" tabindex="2">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'path')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <nobr>&nbsp;<b>MySQL Server Hostname: *</b></nobr>
          </td>
          <td>
            <input type="text" name="db_hostname" class="default" size="30" tabindex="3" value="<?php echo $_POST['db_hostname']; ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'db_hostname')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>MySQL Database: *</b>
          </td>
          <td>
            <input type="text" name="db_name" class="default" size="30" tabindex="3" value="<?php echo $_POST['db_name']; ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'db_name')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <input type="checkbox" name="create_db" value="yes" <?php if ($_POST['create_db'] == 'yes'): ?>checked<?php endif; ?>> <span class="default"><b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('install_form', 'create_db');">Create Database</a></b></span>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>MySQL Table Prefix:</b>
          </td>
          <td>
            <input type="text" name="db_table_prefix" value="<?php echo ((is_array($_tmp=@$_POST['db_table_prefix'])) ? $this->_run_mod_handler('default', true, $_tmp, 'eventum_') : smarty_modifier_default($_tmp, 'eventum_')); ?>
" class="default" size="30" tabindex="4">
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default" align="center">
            <input type="checkbox" name="drop_tables" value="yes" <?php if ($_POST['drop_tables'] == 'yes'): ?>checked<?php endif; ?>> <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('install_form', 'drop_tables');">Drop Tables If They Already Exist</a></b>
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>MySQL Username: *</b>
          </td>
          <td>
            <input type="text" name="db_username" class="default" size="20" tabindex="5" value="<?php echo $_POST['db_username']; ?>
">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'db_username')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default" align="center">
            <b>Note:</b> This user requires permission to create and drop tables in the specified database
          </td>
        </tr>
        <tr>
          <td width="180" class="default" align="right">
            <b>MySQL Password:</b>
          </td>
          <td>
            <input type="password" name="db_password" class="default" size="20" tabindex="6" value="<?php echo $_POST['db_password']; ?>
">
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default" align="center">
            <input type="checkbox" name="alternate_user" value="yes" onClick="javascript:toggleAlternateUserFields();" <?php if ($_POST['alternate_user'] == 'yes'): ?>checked<?php endif; ?>> <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('install_form', 'alternate_user');toggleAlternateUserFields();">Use a Separate MySQL User for Normal Eventum Use</a></b>
          </td>
        </tr>
        <tr id="alternate_user_row">
          <td colspan="2" align="center">
            <table>
              <tr>
                <td>
                  <table width="300" cellpadding="1" cellspacing="0" bgcolor="white" border="0">
                    <tr>
                      <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#C0C0C0">
                          <tr>
                            <td colspan="2" class="default">
                              <b>Enter the details below:</b>
                            </td>
                          </tr>
                          <tr>
                            <td class="default" align="right">
                              <nobr>&nbsp;<b>Username: *</b>&nbsp;</nobr>
                            </td>
                            <td>
                              <nobr><input type="text" class="default" name="eventum_user" size="20" value="<?php echo $_POST['eventum_user']; ?>
">&nbsp;</nobr>
                              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'eventum_user')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                            </td>
                          </tr>
                          <tr>
                            <td class="default" align="right">
                              <nobr>&nbsp;<b>Password:</b>&nbsp;</nobr>
                            </td>
                            <td>
                              <nobr><input type="password" class="default" name="eventum_password" size="20" value="<?php echo $_POST['eventum_password']; ?>
">&nbsp;</nobr>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="2" class="default" align="center">
                              <input type="checkbox" name="create_user" value="yes" <?php if ($_POST['create_user'] == 'yes'): ?>checked<?php endif; ?>> <b><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('install_form', 'create_user');">Create User and Permissions</a></b>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default" align="right">
            &nbsp;
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="#666666" align="right">
            <input style="font-family: Verdana, Arial, Helvetica, sans-serif; font-weight: bold; font-size: 90%;" type="submit" value="Start Installation &gt;&gt;" tabindex="7">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" class="default">
      <b>* Required Fields</b>
    </td>
  </tr>
</form>
</table>

<?php echo '
<script language="JavaScript">
<!--
window.onload = setFocus;
function setFocus()
{
    document.install_form.hostname.focus();
    toggleAlternateUserFields();
}
//-->
</script>
'; ?>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>