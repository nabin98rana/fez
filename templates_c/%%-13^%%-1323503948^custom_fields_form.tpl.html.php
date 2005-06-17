<?php /* Smarty version 2.6.2, created on 2004-09-09 10:01:42
         compiled from custom_fields_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'custom_fields_form.tpl.html', 59, false),array('function', 'html_options', 'custom_fields_form.tpl.html', 67, false),array('modifier', 'escape', 'custom_fields_form.tpl.html', 75, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['update_result']): ?>
  <br />
  <center>
  <span class="default">
  <?php if ($this->_tpl_vars['update_result'] == -1): ?>
    <b>An error occurred while trying to run your query</b>
  <?php elseif ($this->_tpl_vars['update_result'] == 1): ?>
    <b>Thank you, the custom field values were updated successfully.</b>
  <?php endif; ?>
  </span>
  </center>
  <script language="JavaScript">
  <!--
  <?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows'] == '1'): ?>
  setTimeout('closeAndRefresh()', 2000);
  <?php endif; ?>
  //-->
  </script>
  <br />
  <?php if (! $this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
  <center>
    <span class="default"><a class="link" href="javascript:void(null);" onClick="javascript:closeAndRefresh();">Continue</a></span>
  </center>
  <?php endif; ?>
<?php else: ?>
<?php echo '
<script language="JavaScript">
<!--
var required_custom_fields = new Array();
function validateForm(f)
{
    checkRequiredCustomFields(f, required_custom_fields);
}
function resetCustom(x){
	//cycle through the options collection, setting the selected attribute of each to false
	var element = getPageElement(\'custom_fields[\'+x+\']\');
	for (var i = 0; i < element.options.length; i++) {
		element.options[i].selected = false;
	}
}
//-->
</script>
'; ?>

<form name="custom_field_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
" onSubmit="javascript:return checkFormSubmission(this, 'validateForm');">
<input type="hidden" name="cat" value="update_values">
<input type="hidden" name="issue_id" value="<?php echo $_GET['issue_id']; ?>
">
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Update Issue Custom Details</b>
          </td>
        </tr>
        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['custom_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <nobr><b><?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
:</b>&nbsp;</nobr>
          </td>
          <td width="100%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
			<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 6 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 8): ?>
				<select size="10" class="default" multiple id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" onChange="javascript:showSelectionsFill('custom_field_form', 'custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]', <?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
);">
				  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['library_staff']), $this);?>

				</select>
			<input type="button" name="reset_nameOfUser" value="Select None" class="default" onClick="javascript:resetCustom(<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
);custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
.value='';">
			<br />
			<div class=default>Quick Lookup:</div><?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 6):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => "custom_fields[6]",'callbacks' => "new Array('showSelectionsFill(\'custom_field_form\', \'custom_fields[6]\', 6)')")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  else:  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_field.tpl.html", 'smarty_include_vars' => array('lookup_field_name' => 'search','lookup_field_target' => "custom_fields[8]",'callbacks' => "new Array('showSelectionsFill(\'custom_field_form\', \'custom_fields[8]\', 8)')")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
					<img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" align="absmiddle" onClick="javascript:lookupHistory(<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
)">
					<input type="button" value="Clear" class="default" name="btnClearCustom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
" onClick="javascript:custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
.value='';">
			<div class="default" id="selection_custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]"></div>
			<input type="text" name="custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
" id="custom<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['icf_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" size="80" class="default"><?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 6):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'custom6')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  else:  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'custom8')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
			<?php else: ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'text'): ?>
				<input class="default" type="text" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" maxlength="255" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['icf_value'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
				<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'textarea'): ?>
				<textarea name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" rows="10" cols="60"></textarea>
				<?php else: ?>
				<select class="default" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>" <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>multiple size="3"<?php endif; ?>>
				  <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple'): ?><option value="-1">Please choose an option</option><?php endif; ?>
				  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['selected_cfo_id']), $this);?>

				</select>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'] != ""): ?>
				<span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
				<?php endif; ?>
			<?php endif; ?>
          </td>
        </tr>
        <?php endfor; else: ?>
        <tr>
          <td align="center" class="default" colspan="2" bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">
            <b>No custom field could be found.</b>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
            <input class="button" type="submit" value="Update Values">&nbsp;&nbsp;
            <input class="button" type="button" value="Close" onClick="javascript:window.close();">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>