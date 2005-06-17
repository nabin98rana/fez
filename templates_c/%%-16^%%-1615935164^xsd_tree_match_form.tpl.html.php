<?php /* Smarty version 2.6.2, created on 2005-06-08 11:57:22
         compiled from manage/xsd_tree_match_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'manage/xsd_tree_match_form.tpl.html', 31, false),array('function', 'html_options', 'manage/xsd_tree_match_form.tpl.html', 625, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['show_subelement_parents'] == true): ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <form name="xsdsel_match_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
		      <input type="hidden" name="form_name" value="xsdsel_match_form">
              <?php if ($this->_tpl_vars['form_cat'] == 'edit'): ?>
              <input type="hidden" name="form_cat" value="update">
              <?php else: ?>
              <input type="hidden" name="form_cat" value="new">
              <?php endif; ?>
              <input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
              <input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
              <tr>
                <td colspan="2" class="default">
                  <b>Choose the Looping Subelement Instance:</b>
                </td>
              </tr>
				<tr>
				  <td width="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap align="center"><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
				  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Type</b></td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Order</b></td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Link to XSD Match Field for this Subelement Instance</b></td>
				</tr>
				<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['xsdsel_loop_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					<input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['xsdsel_loop_list'][$this->_sections['i']['index']]['xsdsel_id']; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
				  </td>
				  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
					&nbsp;<?php echo $this->_tpl_vars['xsdsel_loop_list'][$this->_sections['i']['index']]['xsdsel_title']; ?>

				  </td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
					&nbsp;<?php echo $this->_tpl_vars['xsdsel_loop_list'][$this->_sections['i']['index']]['xsdsel_type']; ?>

				  </td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
					&nbsp;<?php echo $this->_tpl_vars['xsdsel_loop_list'][$this->_sections['i']['index']]['xsdsel_order']; ?>

				  </td>
				  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="xsd_tree_match_form.php?&xdis_id=<?php echo $this->_tpl_vars['xdis_id']; ?>
&xml_element=<?php echo $this->_tpl_vars['xml_element']; ?>
&xsdsel_id=<?php echo $this->_tpl_vars['xsdsel_loop_list'][$this->_sections['i']['index']]['xsdsel_id']; ?>
" title="match this XSD Display against HTML elements">Link</a>
				  </td>
				</tr>
				<?php endfor; endif; ?>
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
<?php else: ?>
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
                      alert(\'Please enter the title of this xsd element to html element match.\');
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
              function toggleXSDMatchField(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
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
              function toggleStaticText(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'static_text_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }
              function toggleDynamicText(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'dynamic_text_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }
              function toggleEspaceVarText(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'espace_variable_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }
              function toggleSmartyVarText(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'smarty_variable_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }

              function toggleXSDRef(show_field)
              {
//                  var f = getForm(\'xsd_tree_match_form\');
//				  if (
				  var elementCheck = getPageElement(\'no_xsd_refs\');
				  if (!(elementCheck) && (show_field == true)) {
					  var f = getForm(\'xsd_tree_match_form\');
					  for (var x=0; x < 7; x++) {
						if (x != 5) {
						  var field = getFormElement(f, \'field_type\', x);
						  field.disabled = true;
						}
					  }
				  }
                  //f.field_type.disabled = true;


				  var elementTitle = getPageElement(\'xsd_display_ref_tr\');

				  
				  if ((elementCheck) && (show_field == false)) {
					  if (show_field == true) {
						elementTitle.style.display = \'\';
					  } else {
						elementTitle.style.display = \'none\';
					  }
					  return true;
				  } else {
					  if (show_field == true) {
						  elementTitle.style.display = \'\';
						  return true;
					  } else {
						  alert(\'There are still XSD Display references against this XSD Display Matching Field!\\nYou must delete the references before you can change this XSDMF to another type.\');
						  return false;
					  }
				  }
              }
              function toggleXSDLoopSubelement(show_field)
              {
//                  var f = getForm(\'xsd_tree_match_form\');
//				  if (
				  var elementCheck = getPageElement(\'no_xsd_looping_subelements\');
				  if (!(elementCheck) && (show_field == true)) {
					  var f = getForm(\'xsd_tree_match_form\');
					  for (var x=0; x < 7; x++) {
						if (x != 6) {
						  var field = getFormElement(f, \'field_type\', x);
						  field.disabled = true;
						}
					  }
				  }
                  //f.field_type.disabled = true;


				  var elementTitle = getPageElement(\'xsd_loop_subelement_tr\');
//				  var elementTitle = getPageElement(\'xsd_display_ref_tr\');
//				  elementTitle.style.display = \'\';

				  if ((elementCheck) && (show_field == false)) {
					  if (show_field == true) {
						elementTitle.style.display = \'\';

					  } else {
						elementTitle.style.display = \'none\';
					  }
					  return true;
				  } else {

					  if (show_field == true) {
						  elementTitle.style.display = \'\';
						  return true;
					  } else {
						  alert(\'There are still XSD Looping Subelements against this XSD Display Matching Field!\\nYou must delete the references before you can change this XSDMF to another type.\');
						  return false;
					  }
				  }
              }


              function toggleValidationType(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'validation_type_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }
              function toggleXSDMF_ID_Refs(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'xsdmf_id_ref_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }
              function toggleFieldOptions(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'field_options_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
				  toggleDynamicSelectedOption(show_field);
                  return true;
              }
              function toggleDynamicSelectedOption(show_field)
              {
                  var f = getForm(\'xsd_tree_match_form\');
				  var elementTitle = getPageElement(\'dynamic_selected_option_tr\');
				  if (show_field == true) {
					elementTitle.style.display = \'\';
				  } else {
					elementTitle.style.display = \'none\';
				  }
                  return true;
              }

              function checkRequiredFields()
              {
                  var f = getForm(\'xsd_tree_match_form\');
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

              <form name="xsd_tree_match_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
		      <input type="hidden" name="form_name" value="xsdmf">
			  <input type="hidden" name="xsdmf_id" value="<?php echo $this->_tpl_vars['xsdmf_id']; ?>
">
              <?php if ($this->_tpl_vars['form_cat'] == 'edit'): ?>
              <input type="hidden" name="form_cat" value="update">
              <?php else: ?>
              <input type="hidden" name="form_cat" value="new">
              <?php endif; ?>
              <input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
              <input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
              <input type="hidden" name="xsdsel_id" value="<?php echo $this->_tpl_vars['xsdsel_id']; ?>
">
              <tr>
                <td colspan="2" class="default">
                  <b>XSD Tree Match to HTML Elements Form</b>
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['form_cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new xsd element to html element match.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the xsd element to html element match was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['form_cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the xsd element to html element match information.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the xsd element to html element match was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>	
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>XSD Sublooping Element Title:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
					<?php echo $this->_tpl_vars['xsdsel_title']; ?>

                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>XML Element:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <?php echo $this->_tpl_vars['xml_element_clean']; ?>

                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Title:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="title" size="80" class="default" value="<?php if ($this->_tpl_vars['info']['xsdmf_title']):  echo $this->_tpl_vars['info']['xsdmf_title'];  else:  if ($this->_tpl_vars['xsdsel_title'] != "N/A"):  echo $this->_tpl_vars['xsdsel_title']; ?>
 - <?php endif;  echo $this->_tpl_vars['xml_element_clean'];  endif; ?>">
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
                  <b>Short Description:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="description" size="30" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_description']; ?>
">
                  <span class="small_default">(it will show up by the side of the field)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Image Location:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="image_location" size="30" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_image_location']; ?>
">
                  <span class="small_default">(It will be shown to the left of the title of the element if it exists in the images directory of eSpace.)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Enforced Namespace Prefix:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="enforced_prefix" size="30" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_enforced_prefix']; ?>
">
                  <span class="small_default">(it will be used as the namespace for this field and override the main object namespace. You must include the ":".)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Value Prefix:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="value_prefix" size="30" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_value_prefix']; ?>
">
                  <span class="small_default">(it will be appended to the front of any form entered variable for this xsdmf match eg for Pids to have info:fedora/ at the front.)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Display Order Priority:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="order" size="30" class="default" value="<?php if ($this->_tpl_vars['info']['xsdmf_order']):  echo $this->_tpl_vars['info']['xsdmf_order'];  else: ?>0<?php endif; ?>"><br />
                  <span class="small_default">(The order the field will display on HTML forms, 0 is highest priority, 1, 2 next etc.)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>XML Instance Order:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="xml_order" size="30" class="default" value="<?php if ($this->_tpl_vars['info']['xsdmf_xml_order']):  echo $this->_tpl_vars['info']['xsdmf_xml_order'];  else: ?>0<?php endif; ?>"><br />
                  <span class="small_default">(The order the field will feed into the xml object, 0 is highest priority, 1, 2 next etc.)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Enabled?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="enabled" class="default" <?php if (( ( $this->_tpl_vars['info']['xsdmf_enabled'] != 0 ) || ( $this->_tpl_vars['info']['xsdmf_enabled'] == '' ) )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this enabled tickbox is not ticked this element will not be active)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Required?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="required" class="default" <?php if (( $this->_tpl_vars['info']['xsdmf_required'] != 0 )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this enabled tickbox is not ticked you the input form will not require this field)</span>
                </td>
              </tr>
              <tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Multiple?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="multiple" class="default" <?php if (( $this->_tpl_vars['info']['xsdmf_multiple'] == 1 )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this enabled tickbox is ticked this element will be able to be entered multiple times in forms)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Multiple Limit:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="multiple_limit" class="default" size="30" value="<?php echo $this->_tpl_vars['info']['xsdmf_multiple_limit']; ?>
"><br />
                  <span class="small_default">(The maximum amount of html input elements that will show for this element if it is a multiple)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Show in View Details?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="show_in_view" class="default" <?php if (( $this->_tpl_vars['info']['xsdmf_show_in_view'] == 1 || $this->_tpl_vars['info']['xsdmf_show_in_view'] == "" )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this enabled tickbox is ticked this element will show its heading and value in the object view form)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Value In Tag?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="valueintag" class="default" <?php if (( $this->_tpl_vars['info']['xsdmf_valueintag'] == 1 || $this->_tpl_vars['info']['xsdmf_valueintag'] == "" )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this enabled tickbox is ticked this element will have a start and end tag, rather than one which does both)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Is a SEL Unique Key?:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="checkbox" name="is_key" class="default" <?php if (( $this->_tpl_vars['info']['xsdmf_is_key'] == 1 )): ?>checked<?php endif; ?>><br />
                  <span class="small_default">(If this tickbox is ticked the element key match value will match against)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Key Match Text:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				  <input type="text" name="key_match" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_key_match']; ?>
">
                  <span class="small_default">(If the above tickbox is ticked xml elements will be matched against this text value to get the corresponding xsdmf_id)</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Parent Key Match Text:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				  <input type="text" name="parent_key_match" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_parent_key_match']; ?>
">
                  <span class="small_default">(If the above tickbox is ticked xml elements will be matched against this text value to get the corresponding xsdmf_id from the parent element passed key match (if one exists))</span>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Field Type:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
				  <?php echo '
                  <input type="radio" name="field_type" value="text" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'text'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(true);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 0);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(true);toggleXSDMF_ID_Refs(false);}">Text Input</a>&nbsp;
                  <input type="radio" name="field_type" value="textarea" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'textarea'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(true);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 1);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(true);toggleXSDMF_ID_Refs(false);}">Textarea</a>&nbsp;
                  <input type="radio" name="field_type" value="combo" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'combo'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(true);toggleSmartyVarText(true);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(true);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 2);  toggleEspaceVarText(true);toggleSmartyVarText(true);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(true);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">Combo Box</a>&nbsp;
                  <input type="radio" name="field_type" value="multiple" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'multiple'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(true);toggleSmartyVarText(true);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(true);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
	              <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 3);  toggleEspaceVarText(true);toggleSmartyVarText(true);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(true);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">Multiple Combo Box</a>
<br />            <input type="radio" name="field_type" value="checkbox" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'checkbox'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 4);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">Checkbox</a>&nbsp;
		         <input type="radio" name="field_type" value="static" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'static'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) {  toggleEspaceVarText(true);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(true);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) {checkRadio(\'xsd_tree_match_form\', \'field_type\', 5);  toggleEspaceVarText(true);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(true);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">Hidden Static Text</a>
       			  <input type="radio" name="field_type" value="dynamic" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'dynamic'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(true);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) {checkRadio(\'xsd_tree_match_form\', \'field_type\', 6);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(true);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">Hidden Dynamic Text</a>
<br />            <input type="radio" name="field_type" value="xsd_ref" ';  if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref'): ?>checked<?php endif; ?> <?php if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(true) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="if (toggleXSDRef(true) && toggleXSDLoopSubelement(false)) { javascript:checkRadio(\'xsd_tree_match_form\', \'field_type\', 7); toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">XSD Reference(s)</a>
		          <input type="radio" name="field_type" value="xsd_loop_subelement" ';  if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement'): ?>checked<?php endif; ?> <?php if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 )): ?>disabled<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(true)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="if (toggleXSDRef(false) && toggleXSDLoopSubelement(true)) { javascript:checkRadio(\'xsd_tree_match_form\', \'field_type\', 8); toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(false);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">XSD Loop Subelement(s)</a>
                  <input type="radio" name="field_type" value="xsdmf_id_ref" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'xsdmf_id_ref'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(true);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 9);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(true);}">XSDMF ID Reference</a>&nbsp;
<br />            <input type="radio" name="field_type" value="file_input" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'file_input'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 10);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">File Upload Input</a>&nbsp;
                  <input type="radio" name="field_type" value="file_selector" ';  if (( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_ref' && $this->_tpl_vars['xsd_display_count'] > 0 ) || ( $this->_tpl_vars['info']['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_subelement_count'] > 0 )): ?>disabled<?php endif; ?> <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] == 'file_selector'): ?>checked<?php endif;  echo ' onClick="javascript: if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:if (toggleXSDRef(false) && toggleXSDLoopSubelement(false)) { checkRadio(\'xsd_tree_match_form\', \'field_type\', 11);  toggleEspaceVarText(false);toggleSmartyVarText(false);toggleXSDMatchField(true);toggleStaticText(false);toggleDynamicText(false);toggleFieldOptions(false);toggleValidationType(false);toggleXSDMF_ID_Refs(false);}">File Selector</a>&nbsp;
				  '; ?>

                </td>
              </tr>
              <tr id="field_options_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'combo' && $this->_tpl_vars['info']['xsdmf_html_input'] != 'multiple'): ?>style="display:none"<?php endif; ?>>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Field Options:</b><i>(If a dynamic variable is set it will be used instead of the entered values)</i>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <table bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                      <td rowspan="2"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/blank.gif" width="30" height="1"></td>
                      <td>
                        <span class="default"><b>Set available options:</b></span><br />
                        <input class="default" type="text" name="new_value" size="26"><input class="shortcut" name="add_button" type="button" value="Add" onClick="javascript:addFieldOption(this.form);"><?php if ($_GET['form_cat'] == 'edit'): ?><input class="shortcut" name="update_button" type="button" value="Update Value" disabled onClick="javascript:updateFieldOption(this.form);"><?php endif; ?><br />
                      </td>
                      <td rowspan="3"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/blank.gif" width="30" height="1"></td>
                    </tr>
                    <tr>
                      <td>
                        <table border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td>
                              <select name="field_options[]" multiple size="3" class="default">
                              <?php if ($this->_tpl_vars['info']['field_options'] == ""): ?>
                                <option value="-1">enter a new option above</option>
                              <?php else: ?>
                                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['info']['field_options']), $this);?>

                              <?php endif; ?>
                              </select>
                            </td>
                            <td valign="top">
                              <?php if ($this->_tpl_vars['form_cat'] == 'edit'): ?>
                              <input class="shortcut" type="button" name="edit_button" value="Edit Option" onClick="javascript:editFieldOption(this.form);"><br />
                              <?php endif; ?>
                              <input class="shortcut" type="button" name="remove_button" value="Remove" onClick="javascript:removeFieldOption(this.form);">
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr id="xsdmf_id_ref_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'xsdmf_id_ref'): ?>style="display:none"<?php endif; ?>>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>XSD ID Reference:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
					<select name="xsdmf_id_ref" class="default">
						<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsdmf_id_ref_list'],'selected' => $this->_tpl_vars['info']['xsdmf_id_ref']), $this);?>

					</select>
                </td>
              </tr>
              <tr id="static_text_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'static'): ?>style="display:none"<?php endif; ?>>
				<td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
				  <b>Static Text:</b>
				</td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <table bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" cellspacing="0" cellpadding="0" border="0">
                    <tr>			
						<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						  <textarea name="static_text" rows="20" cols="60" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_static_text']; ?>
"><?php echo $this->_tpl_vars['info']['xsdmf_static_text']; ?>
</textarea>
						  <br /><span class="small_default">(it will be added when XML for an instance of this XSD is created)</span>
						</td>
					  </tr>
					</table>
				 </td>
			  </tr>

              <tr id="dynamic_text_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'dynamic'): ?>style="display:none"<?php endif; ?>>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Dynamic Text: <br /><i>(The xml creation process will look for a post variable with the name you put in the textarea and use it as the dynamic variable so make sure it exists, usually as a hidden form html input)</i></b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <textarea name="dynamic_text" rows="20" cols="60" class="default" value="<?php echo $this->_tpl_vars['info']['xsdmf_dynamic_text']; ?>
"><?php echo $this->_tpl_vars['info']['xsdmf_dynamic_text']; ?>
</textarea>
                  <br /><span class="small_default">(it will be added when XML for an instance of this XSD is created)</span>
                </td>
              </tr>
              <tr id="validation_type_tr" style="display:none">
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Validation Type:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				  <select name="validation_types" multiple size="4" class="default">
				  	<option value="none" <?php if (( $this->_tpl_vars['info']['xsdmf_validation_type'] == 'none' ) || ( $this->_tpl_vars['info']['xsdmf_validation_type'] == '' )): ?>selected<?php endif; ?>>None</option>
				  	<option value="numeric" <?php if (( $this->_tpl_vars['info']['xsdmf_validation_type'] == 'numeric' )): ?>selected<?php endif; ?>>Numeric</option>
				  	<option value="date" <?php if (( $this->_tpl_vars['info']['xsdmf_validation_type'] == 'date' )): ?>selected<?php endif; ?>>Date</option>
				  	<option value="email" <?php if (( $this->_tpl_vars['info']['xsdmf_validation_type'] == 'email' )): ?>selected<?php endif; ?>>Email Address</option>
				  </select>
                  <br /><span class="small_default">(The datatype the javascript will validate input in this field against)</span>
                </td>
              </tr>
		      <tr id="smarty_variable_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'multiple' && $this->_tpl_vars['info']['xsdmf_html_input'] != 'combo'): ?>style="display:none"<?php endif; ?>>
                 <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white"><b>Dynamic Variable:</b><i>(If a dynamic variable is set it will be used instead of the entered values, or if this is a combo or multiple html input then the dynamic variable will be used as the field options)</i></td>
 				<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><span class="default"><br /><b>OR use this dynamic variable:</b></span> <br />
					<input class="default" type="text" name="smarty_variable" size="26" value="<?php echo $this->_tpl_vars['info']['xsdmf_smarty_variable']; ?>
">
			  	</td>
		     </tr>
		      <tr id="dynamic_selected_option_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'multiple' && $this->_tpl_vars['info']['xsdmf_html_input'] != 'combo'): ?>style="display:none"<?php endif; ?>>
                 <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white"><b>Dynamic Selected Option:</b><i>(If a dynamic variable is set it will be used to preselect the select options for combo boxes and multiple select boxes)</i></td>
 				<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
					<input class="default" type="text" name="dynamic_selected_option" size="26" value="<?php echo $this->_tpl_vars['info']['xsdmf_dynamic_selected_option']; ?>
">
			  	</td>
		     </tr>
			 <tr id="espace_variable_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'static' && $this->_tpl_vars['info']['xsdmf_html_input'] != 'multiple' && $this->_tpl_vars['info']['xsdmf_html_input'] != 'combo'): ?>style="display:none"<?php endif; ?>>
                 <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white"><b>eSpace Variable:</b><i>(If an eSpace variable is set it will be used instead of any other options including smarty template variables)</i></td>
				<td class="default" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><br /><b>OR use one of these eSpace variables: </b> <br />
					<INPUT TYPE=RADIO NAME="espace_variable" VALUE="none" <?php if (( $this->_tpl_vars['info']['xsdmf_espace_variable'] == "" || $this->_tpl_vars['info']['xsdmf_espace_variable'] == 'none' )): ?>checked<?php endif; ?>/>None<BR>
					<INPUT TYPE=RADIO NAME="espace_variable" VALUE="pid" <?php if ($this->_tpl_vars['info']['xsdmf_espace_variable'] == 'pid'): ?>checked<?php endif; ?>/>Pid (for new records it will get the new Pid and then create this parent datastream)<BR>
					<INPUT TYPE=RADIO NAME="espace_variable" VALUE="xdis_id" <?php if ($this->_tpl_vars['info']['xsdmf_espace_variable'] == 'xdis_id'): ?>checked<?php endif; ?>/>XSD Display ID<BR>
				</td>
 			  </tr> 
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($this->_tpl_vars['form_cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update XSD Element to HTML Element Match">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create XSD Element to HTML Element Match">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                  <?php if ($this->_tpl_vars['form_cat'] == 'edit'): ?>
                  <input class="button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete the selected XSD Element to HTML input form matching?')">
                  <?php endif; ?>
                </td>
              </tr>
              </form>
            </table>
          </td>
        </tr>

		<tr id="xsd_display_ref_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'xsd_ref'): ?>style="display:none"<?php endif; ?>>
			<td>
			  <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
				<tr>
				  <td>
					<table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
					  <form name="xsd_display_ref_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
		              <input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
 		              <input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
		              <input type="hidden" name="xsdsel_id" value="<?php echo $this->_tpl_vars['xsdsel_id']; ?>
">
 					  <input type="hidden" name="form_name" value="xsdrel_main">
 					  <input type="hidden" name="xsdrel_xsdmf_id" value="<?php echo $this->_tpl_vars['xsdmf_id']; ?>
">
 					  <input type="hidden" name="xsdrel_xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
					  <?php if ($_GET['form_cat'] == 'edit'): ?>
					  <input type="hidden" name="form_cat" value="update">
					  <?php else: ?>
					  <input type="hidden" name="form_cat" value="new">
					  <?php endif; ?>
					  <tr>
						<td colspan="2" class="default">
						  <b>Manage XSD Display References</b>
						</td>
					  </tr>
					  <?php if ($this->_tpl_vars['result'] != ""): ?>
					  <tr>
						<td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
						  <?php if ($_POST['form_cat'] == 'new'): ?>
							<?php if ($this->_tpl_vars['result'] == -1): ?>
							  An error occurred while trying to add the new xsd display.
							<?php elseif ($this->_tpl_vars['result'] == 1): ?>
							  Thank you, the xsd display was added successfully.
							<?php endif; ?>
						  <?php elseif ($_POST['form_cat'] == 'update'): ?>
							<?php if ($this->_tpl_vars['result'] == -1): ?>
							  An error occurred while trying to update the xsd display information.
							<?php elseif ($this->_tpl_vars['result'] == 1): ?>
							  Thank you, the xsd display was updated successfully.
							<?php endif; ?>
						  <?php endif; ?>
						</td>
					  </tr>
					  <?php endif; ?>
					  <tr>
						<td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
						  <b>XSD Display:</b>
						</td>
						<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<select class="default" name="xsd_display_id">
						  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsd_displays']), $this);?>

						</select>

						 <!-- <input type="text" name="xdis_title" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['xdis_title']; ?>
"> -->
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
						  <b>Order:</b>
						</td>
						<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						  <input type="text" name="xsdrel_order" size="20" class="default">
						  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'xsdrel_order')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						</td>
					  </tr>
					  <tr>
						<td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
						  <?php if ($_GET['form_cat'] == 'edit'): ?>
						  <input class="button" type="submit" value="Update XSD Display Reference">
						  <?php else: ?>
						  <input class="button" type="submit" value="Add XSD Display Reference">
						  <?php endif; ?>
						  <input class="button" type="reset" value="Reset">
						</td>
					  </tr>
					  </form>
					  <tr>
						<td colspan="2" class="default">
						  <b>Existing XSD Display References:</b>
						</td>
					  </tr>
					  <tr>
						<td colspan="2">
						  <table border="0" width="100%" cellpadding="1" cellspacing="1">
							<form onSubmit="javascript:return checkDelete(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
				            <input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
				            <input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
				            <input type="hidden" name="xsdsel_id" value="<?php echo $this->_tpl_vars['xsdsel_id']; ?>
">
						    <input type="hidden" name="form_name" value="xsdrel_delete">
							<input type="hidden" name="form_cat" value="delete">
							<input type="hidden" name="xsdrel_xsdmf_id" value="<?php echo $this->_tpl_vars['xsdrel_xsdmf_id']; ?>
">
							<tr>
							  <td width="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap align="center"><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
							  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Version</b></td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Order</b></td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>XSD HTML Matching Editor&nbsp;</b></td>
							</tr>
							<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_ref_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['xsd_display_ref_list'][$this->_sections['i']['index']]['xsdrel_id']; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
							  </td>
							  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
								&nbsp;<?php echo $this->_tpl_vars['xsd_display_ref_list'][$this->_sections['i']['index']]['xdis_title']; ?>

							  </td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
								&nbsp;<?php echo $this->_tpl_vars['xsd_display_ref_list'][$this->_sections['i']['index']]['xdis_version']; ?>

							  </td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
								&nbsp;<?php echo $this->_tpl_vars['xsd_display_ref_list'][$this->_sections['i']['index']]['xsdrel_order']; ?>

							  </td>
							  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
								&nbsp;<a class="link" href="xsd_tree_match.php?&xdis_id=<?php echo $this->_tpl_vars['xsd_display_ref_list'][$this->_sections['i']['index']]['xdis_id']; ?>
" title="match this XSD Display against HTML elements">Edit</a>
							  </td>
							</tr>
							<?php endfor; else: ?>
							<tr>
							  <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
								No xsd display references could be found.
								<input type="hidden" id="no_xsd_refs" name="no_xsd_refs" value="true">
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
				  </td>
				</tr>

				<tr id="xsd_loop_subelement_tr" <?php if ($this->_tpl_vars['info']['xsdmf_html_input'] != 'xsd_loop_subelement'): ?>style="display:none"<?php endif; ?>>
					<td>
					  <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
						<tr>
						  <td>
							<table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
							  <form name="xsd_loop_subelement_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
							  <input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
							  <input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
				              <input type="hidden" name="xsdsel_id" value="<?php echo $this->_tpl_vars['xsdsel_id']; ?>
">
							  <input type="hidden" name="form_name" value="xsdsel_main">
							  <input type="hidden" name="xsdsel_xsdmf_id" value="<?php echo $this->_tpl_vars['xsdmf_id']; ?>
">
							  <input type="hidden" name="xsdsel_xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
							  <?php if ($_GET['form_cat'] == 'edit'): ?>
							  <input type="hidden" name="form_cat" value="update">
							  <?php else: ?>
							  <input type="hidden" name="form_cat" value="new">
							  <?php endif; ?>
							  <tr>
								<td colspan="2" class="default">
								  <b>Manage XSD Looping Subelements</b>
								</td>
							  </tr>
							  <?php if ($this->_tpl_vars['result'] != ""): ?>
							  <tr>
								<td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
								  <?php if ($_POST['form_cat'] == 'new'): ?>
									<?php if ($this->_tpl_vars['result'] == -1): ?>
									  An error occurred while trying to add the new xsd looping subelement.
									<?php elseif ($this->_tpl_vars['result'] == 1): ?>
									  Thank you, the xsd looping subelement was added successfully.
									<?php endif; ?>
								  <?php elseif ($_POST['form_cat'] == 'update'): ?>
									<?php if ($this->_tpl_vars['result'] == -1): ?>
									  An error occurred while trying to update the xsd looping subelement.
									<?php elseif ($this->_tpl_vars['result'] == 1): ?>
									  Thank you, the xsd looping subelement was updated successfully.
									<?php endif; ?>
								  <?php endif; ?>
								</td>
							  </tr>
							  <?php endif; ?>
							  <tr>
								<td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
								  <b>XSD Subelement Loop Title:</b>
								</td>
								<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
								 <input type="text" name="xsdsel_title" size="20" class="default">
		
								 <!-- <input type="text" name="xdis_title" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['xdis_title']; ?>
"> -->
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
								  <b>Subelement Loop Type:</b>
								</td>
								<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
								<select class="default" name="xsdsel_type">
								  <option value="hardset">Hardset</option>
								  <option value="hardsetplus">Hardset Plus Unlimited</option>
								  <option value="hardsetplusrestricted">Hardset Plus Unlimited Restricted</option>
								  <option value="unlimited">Unlimited</option>
								  <option value="restrictedunlimited">Restricted Unlimited</option>
								  <option value="attributeloop">Loop on Element Attribute</option>
								</select>
								</td>
							  </tr>
							  <tr>
								<td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
								  <b>Loop on Element Attribute XSDMF_ID:</b>
								</td>
								<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
									<select class="default" name="xsdsel_attribute_loop_xsdmf_id">
									  <option value=0>none</option>
									  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsdmf_id_ref_list']), $this);?>

									</select>
								</td>
							  </tr>
							  <tr>
								<td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
								  <b>Order:</b>
								</td>
								<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
								  <input type="text" name="xsdsel_order" size="20" class="default">
								  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'xsdsel_order')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
								</td>
							  </tr>
							  <tr>
								<td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
								  <?php if ($_GET['form_cat'] == 'edit'): ?>
								  <input class="button" type="submit" value="Update XSD Looping Subelement">
								  <?php else: ?>
								  <input class="button" type="submit" value="Add XSD Looping Subelement">
								  <?php endif; ?>
								  <input class="button" type="reset" value="Reset">
								</td>
							  </tr>
							  </form>
							  <tr>
								<td colspan="2" class="default">
								  <b>Existing XSD Looping Subelements:</b>
								</td>
							  </tr>
							  <tr>
								<td colspan="2">
								  <table border="0" width="100%" cellpadding="1" cellspacing="1">
									<form onSubmit="javascript:return checkDelete(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
									<input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
									<input type="hidden" name="xml_element" value="<?php echo $this->_tpl_vars['xml_element']; ?>
">
						            <input type="hidden" name="xsdsel_id" value="<?php echo $this->_tpl_vars['xsdsel_id']; ?>
">
									<input type="hidden" name="form_name" value="xsdsel_delete">
									<input type="hidden" name="form_cat" value="delete">
									<input type="hidden" name="xsdsel_xsdmf_id" value="<?php echo $this->_tpl_vars['xsdsel_xsdmf_id']; ?>
">
									<tr>
									  <td width="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap align="center"><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'items[]');"></td>
									  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Type</b></td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Order</b></td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Attribute Loop Candidate</b></td>
									</tr>
									<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_loop_subelement_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['xsd_loop_subelement_list'][$this->_sections['i']['index']]['xsdsel_id']; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
									  </td>
									  <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
										&nbsp;<?php echo $this->_tpl_vars['xsd_loop_subelement_list'][$this->_sections['i']['index']]['xsdsel_title']; ?>

									  </td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
										&nbsp;<?php echo $this->_tpl_vars['xsd_loop_subelement_list'][$this->_sections['i']['index']]['xsdsel_type']; ?>

									  </td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
										&nbsp;<?php echo $this->_tpl_vars['xsd_loop_subelement_list'][$this->_sections['i']['index']]['xsdsel_order']; ?>

									  </td>
									  <td width="15%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
										&nbsp;<?php echo $this->_tpl_vars['xsd_loop_subelement_list'][$this->_sections['i']['index']]['xsdsel_attribute_loop_xsdmf_id']; ?>

									  </td>

									</tr>
									<?php endfor; else: ?>
									<tr>
									  <td colspan="6" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
										No xsd looping subelements could be found.
										<input type="hidden" id="no_xsd_looping_subelements" name="no_xsd_looping_subelements" value="true">
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
				    </td>
				  </tr>

			    </table>
      <?php echo '
      <script language="JavaScript">
      <!--
      window.onload = setXSDMatchField;
      function setXSDMatchField()
      {
          var f = getForm(\'xsd_tree_match_form\');
          var field1 = getFormElement(f, \'field_type\', 0);
          if (field1.checked) {
              toggleXSDMatchField(true);
          } else {
              toggleXSDMatchField(false);
          }
//          checkRequiredFields();
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