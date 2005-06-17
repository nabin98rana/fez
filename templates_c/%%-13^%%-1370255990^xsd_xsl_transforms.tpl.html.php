<?php /* Smarty version 2.6.2, created on 2005-03-11 16:11:55
         compiled from manage/xsd_xsl_transforms.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'manage/xsd_xsl_transforms.tpl.html', 245, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <?php echo '
              <script language="JavaScript">
              <!--
              var editing_option_id = -1;
              function validateForm(f)
              {
                  if (isWhitespace(f.title.value)) {
                      alert(\'Please enter the title of this xsd xsl transform.\');
                      selectField(f, \'title\');
                      return false;
                  }
                  if ((f.field_type[2].checked) || (f.field_type[3].checked)) {
                      // select all of the options in the select box
                      selectAllOptions(f, \'field_options[]\');
                  }
                  return true;
              }
              function addFieldOption(f)
              {
                  var value = f.new_value.value;
                  if (isWhitespace(value)) {
                      alert(\'Please enter the new value for the combo box.\');
                      f.new_value.value = \'\';
                      f.new_value.focus();
                      return false;
                  }
                  var field = getFormElement(f, \'field_options[]\');
                  var current_length = field.options.length;
                  if (current_length == 1) {
                      if (field.options[0].value == -1) {
                          removeFieldOption(f, true);
                      }
                  }
                  // check for an existing option with the same value
                  for (var i = 0; i < field.options.length; i++) {
                      if (field.options[i].text == value) {
                          alert(\'The specified value already exists in the list of options.\');
                          f.new_value.focus();
                          return false;
                      }
                  }
                  current_length = field.options.length;
                  field.options[current_length] = new Option(value, \'new:\' + value);
                  f.new_value.value = \'\';
                  f.new_value.focus();
              }
              function parseParameters(value)
              {
                  value = value.substring(value.indexOf(\':\')+1);
                  var id = value.substring(0, value.indexOf(\':\'));
                  var text = value.substring(value.indexOf(\':\')+1);
                  return new Option(text, id);
              }
              function updateFieldOption(f)
              {
                  if (isWhitespace(f.new_value.value)) {
                      alert(\'Please enter the updated value.\');
                      f.new_value.value = \'\';
                      f.new_value.focus();
                      return false;
                  }
                  var field = getFormElement(f, \'field_options[]\');
                  for (var i = 0; i < field.options.length; i++) {
                      if (field.options[i].value == editing_option_id) {
                          var params = parseParameters(field.options[i].value);
                          field.options[i].value = \'existing:\' + params.value + \':\' + f.new_value.value;
                          field.options[i].text = f.new_value.value;
                          f.new_value.value = \'\';
                          f.update_button.disabled = true;
                      }
                  }
              }
              function editFieldOption(f)
              {
                  var options = getSelectedItems(getFormElement(f, \'field_options[]\'));
                  if (options.length == 0) {
                      alert(\'Please select an option from the list.\');
                      return false;
                  }
                  editing_option_id = options[0].value;
                  f.new_value.value = options[0].text;
                  f.new_value.focus();
                  f.update_button.disabled = false;
              }
              function removeFieldOption(f, delete_first)
              {
                  if (delete_first != null) {
                      var remove = new Array(\'-1\');
                  } else {
                      var options = getSelectedItems(getFormElement(f, \'field_options[]\'));
                      if (options.length == 0) {
                          alert(\'Please select an option from the list.\');
                          return false;
                      }
                      var remove = new Array();
                      for (var i = 0; i < options.length; i++) {
                          remove[remove.length] = options[i].value;
                      }
                  }
                  for (var i = 0; i < remove.length; i++) {
                      removeOptionByValue(f, \'field_options[]\', remove[i]);
                  }
                  var field = getFormElement(f, \'field_options[]\');
                  if ((delete_first == null) && (field.options.length == 0)) {
                      field.options[0] = new Option(\'enter a new option above\', \'-1\');
                  }
              }
              function toggleCustomOptionsField(show_field)
              {
                  var f = getForm(\'custom_field_form\');
                  f.new_value.disabled = show_field;
                  var field = getFormElement(f, \'field_options[]\');
                  field.disabled = show_field;
                  f.add_button.disabled = show_field;
                  f.remove_button.disabled = show_field;
                  if (f.edit_button) {
                      f.edit_button.disabled = show_field;
                  }
                  return true;
              }
              function checkRequiredFields()
              {
                  var f = getForm(\'custom_field_form\');
                  f.report_form_required.disabled = !(f.report_form.checked);
                  if (f.report_form_required.disabled) {
                      f.report_form_required.checked = false;
                  }
                  f.anon_form_required.disabled = !(f.anon_form.checked);
                  if (f.anon_form_required.disabled) {
                      f.anon_form_required.checked = false;
                  }
              }
              //-->
              </script>
              '; ?>

              <form name="custom_field_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
" enctype="multipart/form-data">
              <input type="hidden" name="xsd_id" value="<?php echo $this->_tpl_vars['xsd_id']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td colspan="2" class="default">
                  <b>Manage XSD XSL Transforms</b>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new xsd xsl transform.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the xsd xsl transform was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the xsd xsl transform information.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the xsd xsl transform was updated successfully.
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
                  <input type="text" name="xsl_title" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['xsl_title']; ?>
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
                  <b>Version:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="xsl_version" size="30" class="default" value="<?php echo $this->_tpl_vars['info']['fld_version']; ?>
">
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>XSL Source File:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input size="50" name="xsl_file[]" type="file" class="shortcut" />
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update XSD XSL Transform">
                  <?php else: ?>
                  <input class="button" type="submit" value="Add XSD XSL Transform">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing XSD XSL Transforms:</b>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <script language="JavaScript">
                  <!--
                  <?php echo '
                  function checkDelete(f)
                  {
                      if (!hasOneChecked(f, \'items[]\')) {
                          alert(\'Please select at least one of the xsd xsl transforms.\');
                          return false;
                      }
                      if (!confirm(\'This action will permanently remove the selected xsd xsl transforms.\')) {
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
			        <input type="hidden" name="xsd_id" value="<?php echo $this->_tpl_vars['xsd_id']; ?>
">
                    <tr>
                      <td width="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Version</b></td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>XSL Source</b></td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>XSL HTML Matching Editor&nbsp;</b></td>
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
                        <input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['xsl_id']; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
                      </td>
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['xsl_title']; ?>

                      </td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['xsl_version']; ?>

                      </td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="xsl_source_edit.php?cat=edit&xsl_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['xsl_id']; ?>
" title="edit this XSLs source XML">Edit</a>
                      </td>
                      <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="xsd_xsl_match.php?&xsl_id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['xsl_id']; ?>
" title="match this XSL against the XSD">Edit</a>
                      </td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No xsd xsl transforms could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td width="4" align="center" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
                        <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');">
                      </td>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
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
      window.onload = setCustomOptionsField;
      function setCustomOptionsField()
      {
          var f = getForm(\'custom_field_form\');
          var field1 = getFormElement(f, \'field_type\', 0);
          if (field1.checked) {
              toggleCustomOptionsField(true);
          } else {
              toggleCustomOptionsField(false);
          }
          checkRequiredFields();
      }
      //-->
      </script>
      '; ?>

