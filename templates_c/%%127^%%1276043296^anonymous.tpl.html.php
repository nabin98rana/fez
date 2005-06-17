<?php /* Smarty version 2.6.2, created on 2004-07-02 11:00:52
         compiled from manage/anonymous.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'manage/anonymous.tpl.html', 117, false),)), $this); ?>

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
                  var field1 = getFormElement(f, \'anonymous_post\', 0);
                  var field2 = getFormElement(f, \'anonymous_post\', 1);
                  if ((!field1.checked) && (!field2.checked)) {
                      alert(\'Please choose whether the anonymous posting feature should be allowed or not for this team\');
                      return false;
                  }
                  if (field1.checked) {
                      var field = getFormElement(f, \'options[show_custom_fields]\');
                      if (field.selectedIndex == 0) {
                          alert(\'Please choose whether to show custom fields for remote invocations or not.\');
                          selectField(f, \'options[show_custom_fields]\');
                          return false;
                      }
                      field = getFormElement(f, \'options[reporter]\');
                      if (field.selectedIndex == 0) {
                          alert(\'Please choose the reporter for remote invocations.\');
                          selectField(f, \'options[reporter]\');
                          return false;
                      }
                      field = getFormElement(f, \'options[category]\');
                      if (field.selectedIndex == 0) {
                          alert(\'Please choose the default category for remote invocations.\');
                          selectField(f, \'options[category]\');
                          return false;
                      }
                      field = getFormElement(f, \'options[priority]\');
                      if (field.selectedIndex == 0) {
                          alert(\'Please choose the default priority for remote invocations.\');
                          selectField(f, \'options[priority]\');
                          return false;
                      }
                      if (!hasOneSelected(f, \'options[users][]\')) {
                          alert(\'Please choose at least one person to assign the new issues created remotely.\');
                          selectField(f, \'options[users][]\');
                          return false;
                      }
                  }
                  return true;
              }
              function disableFields(f, bool)
              {
                  if (bool) {
                      var bgcolor = \'#CCCCCC\';
                  } else {
                      var bgcolor = \'#FFFFFF\';
                  }
                  var field = getFormElement(f, \'options[show_custom_fields]\');
                  field.disabled = bool;
                  field = getFormElement(f, \'options[category]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'options[reporter]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'options[priority]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
                  field = getFormElement(f, \'options[users][]\');
                  field.disabled = bool;
                  field.style.backgroundColor = bgcolor;
              }
              //-->
              </script>
              '; ?>

              <form name="anon_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="prj_id" value="<?php echo $this->_tpl_vars['prj_id']; ?>
">
              <tr>
                <td colspan="2" class="default">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td class="default"><b>Anonymous Reporting of New Issues</b></td>
                      <td align="right" class="default">(Current Team: <?php echo $this->_tpl_vars['project']['prj_title']; ?>
)</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td width="130" nowrap bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Anonymous Reporting: *</b>
                </td>
                <td width="80%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="anonymous_post" value="enabled" <?php if ($this->_tpl_vars['project']['prj_anonymous_post'] == 'enabled'): ?>checked<?php endif; ?> onClick="javascript:disableFields(getForm('anon_form'), false);"> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('anon_form', 'anonymous_post', 0);disableFields(getForm('anon_form'), false);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="anonymous_post" value="disabled" <?php if ($this->_tpl_vars['project']['prj_anonymous_post'] == 'disabled'): ?>checked<?php endif; ?> onClick="javascript:disableFields(getForm('anon_form'), true);"> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('anon_form', 'anonymous_post', 1);disableFields(getForm('anon_form'), true);">Disabled</a>
                </td>
              </tr>
              <tr>
                <td width="130" nowrap bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Show Custom Fields ? *</b>
                </td>
                <td width="80%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <input type="radio" name="options[show_custom_fields]" value="yes" <?php if ($this->_tpl_vars['options']['show_custom_fields'] == 'yes'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('anon_form', 'options[show_custom_fields]', 0);">Enabled</a>&nbsp;&nbsp;
                  <input type="radio" name="options[show_custom_fields]" value="no" <?php if ($this->_tpl_vars['options']['show_custom_fields'] == 'no'): ?>checked<?php endif; ?>> 
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:checkRadio('anon_form', 'options[show_custom_fields]', 1);">Disabled</a>
                </td>
              </tr>
              <tr>
                <td width="130" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Reporter: *</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="options[reporter]" class="default" tabindex="1">
                    <option value="-1">Please choose an user</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['options']['reporter']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "options[reporter]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="130" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Default Category: *</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="options[category]" class="default" tabindex="2">
                    <option value="-1">Please choose a category</option>
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['cats'],'selected' => $this->_tpl_vars['options']['category']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "options[category]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="130" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Default Priority: *</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="options[priority]" class="default" tabindex="3">
                    <option value="-1">Please choose a priority</option>
                    <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['priorities']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
                    <option value="<?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id']; ?>
" <?php if ($this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id'] == $this->_tpl_vars['options']['priority']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_title']; ?>
</option>
                    <?php endfor; endif; ?>
                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "options[priority]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Assignment:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="options[users][]" multiple size="3" class="default" tabindex="4">
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['options']['users']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "options[users][]")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
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
          var f = getForm(\'anon_form\');
          var field1 = getFormElement(f, \'anonymous_post\', 0);
          if (field1.checked) {
              disableFields(f, false);
          } else {
              field1 = getFormElement(f, \'anonymous_post\', 1);
              field1.checked = true;
              disableFields(f, true);
          }
      }
      //-->
      </script>
      '; ?>

