<?php /* Smarty version 2.6.2, created on 2004-09-14 14:16:17
         compiled from reports/custom_fields.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_select_date', 'reports/custom_fields.tpl.html', 19, false),array('function', 'html_options', 'reports/custom_fields.tpl.html', 43, false),array('modifier', 'count', 'reports/custom_fields.tpl.html', 96, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>
" name="custom_fields_report" method="get">
<input type="hidden" name="cat" value="generate">
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center" width="400">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" colspan="3" class="default_white">
            <b>Custom Fields Report</b>
          </td>
        </tr>
        <tr id="start_row">
          <td width="120" class="default">
            <b>Start:</b>
          </td>
          <td width="200">
            <?php echo smarty_function_html_select_date(array('time' => $this->_tpl_vars['start_date'],'prefix' => "",'field_array' => 'start','start_year' => "-2",'end_year' => "+1",'field_order' => 'YMD','month_format' => "%b",'all_extra' => "class='default'"), $this);?>

          </td>
        </tr>
        <tr id="end_row">
          <td width="120" class="default">
            <b>End:</b>
          </td>
          <td width="200"><?php echo $this->_tpl_vars['end_date']; ?>

            <?php echo smarty_function_html_select_date(array('time' => $this->_tpl_vars['end_date'],'prefix' => "",'field_array' => 'end','start_year' => "-2",'end_year' => "+1",'field_order' => 'YMD','month_format' => "%b",'all_extra' => "class='default'"), $this);?>

          </td>
        </tr>

        <tr>
          <td width="30%" class="default" align="center">
            <b>Field to Graph</b>
          </td>
          <td width="30%" class="default" align="center">
            <b>Options to Graph</b>
          </td>
        </tr>
        <tr>
          <td align="center" valign="top">
            <select name="custom_field" class="default" onChange="setOptions(this.options[this.selectedIndex].value, true)">
                <option value=""></option>
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'],'selected' => $this->_tpl_vars['custom_field']), $this);?>

            </select>
          </td>
          <td align="center" valign="top">
            <select name="custom_options[]" size="8" multiple class="default">
            </select>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="cat" value="Generate" class="shortcut">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<div class="default" align="center">Based on all support cases recorded in CSC since start, Oct 1, 2003 to present.</div><br />
<script language="JavaScript">
var options = new Array();
var option_to_fld_id = new Array();
<?php if (isset($this->_foreach['option_list'])) unset($this->_foreach['option_list']);
$this->_foreach['option_list']['name'] = 'option_list';
$this->_foreach['option_list']['total'] = count($_from = (array)$this->_tpl_vars['options']);
$this->_foreach['option_list']['show'] = $this->_foreach['option_list']['total'] > 0;
if ($this->_foreach['option_list']['show']):
$this->_foreach['option_list']['iteration'] = 0;
    foreach ($_from as $this->_tpl_vars['fld_id'] => $this->_tpl_vars['option_list']):
        $this->_foreach['option_list']['iteration']++;
        $this->_foreach['option_list']['first'] = ($this->_foreach['option_list']['iteration'] == 1);
        $this->_foreach['option_list']['last']  = ($this->_foreach['option_list']['iteration'] == $this->_foreach['option_list']['total']);
?>
    option_to_fld_id[<?php echo $this->_foreach['option_list']['iteration']-1; ?>
] = <?php echo $this->_tpl_vars['fld_id']; ?>
;
    options[<?php echo $this->_foreach['option_list']['iteration']-1; ?>
] = new Array();
    <?php if (isset($this->_foreach['option'])) unset($this->_foreach['option']);
$this->_foreach['option']['name'] = 'option';
$this->_foreach['option']['total'] = count($_from = (array)$this->_tpl_vars['option_list']);
$this->_foreach['option']['show'] = $this->_foreach['option']['total'] > 0;
if ($this->_foreach['option']['show']):
$this->_foreach['option']['iteration'] = 0;
    foreach ($_from as $this->_tpl_vars['cfo_id'] => $this->_tpl_vars['cfo_value']):
        $this->_foreach['option']['iteration']++;
        $this->_foreach['option']['first'] = ($this->_foreach['option']['iteration'] == 1);
        $this->_foreach['option']['last']  = ($this->_foreach['option']['iteration'] == $this->_foreach['option']['total']);
?>
        options[<?php echo $this->_foreach['option_list']['iteration']-1; ?>
][<?php echo $this->_foreach['option']['iteration']-1; ?>
] = new Option('<?php echo $this->_tpl_vars['cfo_value']; ?>
', '<?php echo $this->_tpl_vars['cfo_id']; ?>
');
    <?php endforeach; unset($_from); endif; ?>
<?php endforeach; unset($_from); endif; ?>

<?php echo '
var options_field = document.forms[\'custom_fields_report\'].elements[\'custom_options[]\'];
function setOptions(fld_id, auto_select)
{
    fld_id_index = \'\';
    for (i = 0; i < option_to_fld_id.length; i++) {
        if (option_to_fld_id[i] == fld_id) {
            fld_id_index = i;
        }
    }
    
    options_field.length = 0;
    if (options[fld_id_index]) {
        options_field.length = options[fld_id_index].length;
        for (i = 0; i < options[fld_id_index].length; i++) {
            options_field.options[i] = options[fld_id_index][i];
            options_field.options[i].selected = auto_select;
        }
    }
}
'; ?>


setOptions('<?php echo $this->_tpl_vars['custom_field']; ?>
', false);
<?php if (count($this->_tpl_vars['custom_options']) > 0): ?>
    <?php if (count($_from = (array)$this->_tpl_vars['custom_options'])):
    foreach ($_from as $this->_tpl_vars['option_index'] => $this->_tpl_vars['option']):
?>
        options_field.options[<?php echo $this->_tpl_vars['option_index']; ?>
].selected = true;
    <?php endforeach; unset($_from); endif; ?>
<?php endif; ?>
</script>

<?php if (count($this->_tpl_vars['custom_options']) > 0): ?>
<div align="center" class="default">
    <img src="custom_fields_graph.php?<?php echo $_SERVER['QUERY_STRING']; ?>
"><br /><br />
    <img src="custom_fields_graph.php?<?php echo $_SERVER['QUERY_STRING']; ?>
&type=pie"><br />
    Percentages may not add up to exactly 100% due to rounding.<br /><br />
</div>
<?php endif; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>