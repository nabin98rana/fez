<?php /* Smarty version 2.6.2, created on 2004-09-14 14:15:25
         compiled from reports/weekly.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'reports/weekly.tpl.html', 37, false),array('function', 'html_select_date', 'reports/weekly.tpl.html', 46, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
" name="weekly_report">
<input type="hidden" name="cat" value="generate">
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" colspan="3" class="default_white">
            <b>Weekly Report</b>
          </td>
        </tr>
        <tr>
          <td width="120" class="default">
            <b>Report Type:</b>
          </td>
          <td width="200" class="default" NOWRAP>
            <input type="radio" name="report_type" value="weekly" class="default" <?php if ($this->_tpl_vars['report_type'] != 'range'): ?>checked<?php endif; ?> onClick="changeType('weekly');">
                <a id="link" class="link" href="javascript:void(null)" 
                            onClick="javascript:checkRadio('weekly_report', 'report_type', 0);changeType('weekly');">Weekly</a>&nbsp;
            <input type="radio" name="report_type" value="range" <?php if ($this->_tpl_vars['report_type'] == 'range'): ?>CHECKED<?php endif; ?> onClick="changeType('range');">
                <a id="link" class="link" href="javascript:void(null)" 
                            onClick="javascript:checkRadio('weekly_report', 'report_type', 1);changeType('range');">Date Range</a>&nbsp;
          </td>
          <td rowspan="5">
            <input type="submit" value="Generate" class="shortcut">
          </td>
        </tr>
        <tr id="week_row">
          <td width="120" class="default">
            <b>Week:</b>
          </td>
          <td width="200">
            <select name="week" class="default">
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['weeks'],'selected' => $this->_tpl_vars['week']), $this);?>

            </select>
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
          <td width="200">
            <?php echo smarty_function_html_select_date(array('time' => $this->_tpl_vars['end_date'],'prefix' => "",'field_array' => 'end','start_year' => "-2",'end_year' => "+1",'field_order' => 'YMD','month_format' => "%b",'all_extra' => "class='default'"), $this);?>

          </td>
        </tr>
        <tr>
          <td width="120" class="default">
            <b>Developer:</b>
          </td>
          <td width="200">
            <select name="developer" class="default">
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['developer']), $this);?>

            </select>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<script language="JavaScript">
<?php echo '
function changeType(type) {
    if (type == \'range\') {
        document.getElementById(\'week_row\').style.display = \'none\';
        document.getElementById(\'start_row\').style.display = getDisplayStyle();
        document.getElementById(\'end_row\').style.display = getDisplayStyle();
    } else {
        document.getElementById(\'week_row\').style.display = getDisplayStyle();
        document.getElementById(\'start_row\').style.display = \'none\';
        document.getElementById(\'end_row\').style.display = \'none\';
    }
}
'; ?>


changeType('<?php echo $this->_tpl_vars['report_type']; ?>
');
</script>

<?php if ($this->_tpl_vars['data'] != ''): ?>
<pre>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "reports/weekly_data.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</pre>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>