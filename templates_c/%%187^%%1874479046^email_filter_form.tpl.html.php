<?php /* Smarty version 2.6.2, created on 2004-09-10 11:11:41
         compiled from email_filter_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'get_display_style', 'email_filter_form.tpl.html', 84, false),array('function', 'html_options', 'email_filter_form.tpl.html', 110, false),array('function', 'html_select_date', 'email_filter_form.tpl.html', 130, false),array('modifier', 'escape', 'email_filter_form.tpl.html', 96, false),)), $this); ?>

<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/dynCalendar.js"></script>
<?php echo '
<script language="JavaScript">
<!--
function clearFilters(f)
{
    f.keywords.value = \'\';
    f.sender.value = \'\';
    f.to.value = \'\';
    f.ema_id.selectedIndex = 0;
    var field = getFormElement(f, \'filter[arrival_date]\');
    field.checked = false;
    toggleDateFields(f, \'filter[arrival_date]\');
    f.submit();
}
function toggleDateFields(f, field_name)
{
    var checkbox = getFormElement(f, \'filter[\' + field_name + \']\');
    var filter_type = getFormElement(f, field_name + \'[filter_type]\');
    var month_field = getFormElement(f, field_name + \'[Month]\');
    var day_field = getFormElement(f, field_name + \'[Day]\');
    var year_field = getFormElement(f, field_name + \'[Year]\');
    var month_end_field = getFormElement(f, field_name + \'_end[Month]\');
    var day_end_field = getFormElement(f, field_name + \'_end[Day]\');
    var year_end_field = getFormElement(f, field_name + \'_end[Year]\');
    if (checkbox.checked) {
        var bool = false;
    } else {
        var bool = true;
    }
    filter_type.disabled = bool;
    month_field.disabled = bool;
    day_field.disabled = bool;
    year_field.disabled = bool;
    month_end_field.disabled = bool;
    day_end_field.disabled = bool;
    year_end_field.disabled = bool;
}
function selectDateOptions(field_prefix, date_str)
{
    if (date_str.length != 10) {
        return false;
    } else {
        var year = date_str.substring(0, date_str.indexOf(\'-\'));
        var month = date_str.substring(date_str.indexOf(\'-\')+1, date_str.lastIndexOf(\'-\'));
        var day = date_str.substring(date_str.lastIndexOf(\'-\')+1);
        selectDateField(field_prefix, day, month, year);
    }
}
function selectDateField(field_name, day, month, year)
{
    selectOption(getForm(\'email_filter_form\'), field_name + \'[Day]\', day);
    selectOption(getForm(\'email_filter_form\'), field_name + \'[Month]\', month);
    selectOption(getForm(\'email_filter_form\'), field_name + \'[Year]\', year);
}
function checkDateFilterType(f, type_field)
{
    var option = getSelectedOption(f, type_field.name);
    var element_name = type_field.name.substring(0, type_field.name.indexOf(\'[\'));
    var element = getPageElement(element_name + 1);
    if ((option == \'between\') && (!isElementVisible(element))) {
        toggleVisibility(element_name, false);
    } else if ((option != \'between\') && (isElementVisible(element))) {
        toggleVisibility(element_name, false);
    }
}
function calendarCallback_arrival(day, month, year) { selectDateField(\'arrival_date\', day, month, year); }
function calendarCallback_arrival_end(day, month, year) { selectDateField(\'arrival_date_end\', day, month, year); }
function validateForm(f)
{
    var checkbox = getFormElement(f, \'filter[arrival_date]\');
    // need to hack this value in the query string so the saved search options don\'t override this one
    if (!checkbox.checked) {
        var field = getFormElement(f, \'hidden1\');
        field.name = \'filter[arrival_date]\';
        field.value = \'no\';
    }
}
//-->
</script>
'; ?>

<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr id="filter_form1" <?php echo smarty_function_get_display_style(array('element_name' => 'filter_form'), $this);?>
>
    <td>&nbsp;
      
    </td>
    <td>
      <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
        <form action="emails.php" method="get" name="email_filter_form" onSubmit="javascript:return validateForm(this);">
        <input type="hidden" name="cat" value="search">
        <input type="hidden" name="hidden1" value="">
        <tr>
          <td>
            <span class="default">Subject/Body:</span><br />
            <input class="default" type="text" name="keywords" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['keywords'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
          </td>
          <td>
            <span class="default">Sender:</span><br />
            <input class="default" type="text" name="sender" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['sender'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
          </td>
          <td>
            <span class="default">To:</span><br />
            <input class="default" type="text" name="to" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
          </td>
          <td>
            <span class="default">Email Account:</span><br />
            <select name="ema_id" class="default">
              <option value="">any</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['accounts'],'selected' => $this->_tpl_vars['options']['ema_id']), $this);?>

            </select>
          </td>
          <td>
            <input class="button" type="submit" value="Search">
            <input class="button" type="button" value="Clear" onClick="javascript:clearFilters(this.form);">
          </td>
        </tr>
        <tr>
          <td colspan="5">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['filter']['arrival_date'] == 'yes'): ?>checked<?php endif; ?> type="checkbox" name="filter[arrival_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'arrival_date');">
                  <span class="default"><a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('email_filter_form', 'filter[arrival_date]');toggleDateFields(getForm('email_filter_form'), 'arrival_date');">Filter by Arrival Date:</a></span><br />
                  <select name="arrival_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['arrival_date']['filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['arrival_date']['filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['arrival_date']['filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'arrival_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar7 = new dynCalendar('tCalendar7', 'calendarCallback_arrival', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar7.setMonthCombo(false);
                  tCalendar7.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="arrival_date1" width="50%" valign="bottom">
                  <span class="default">Arrival Date: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'arrival_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar8 = new dynCalendar('tCalendar8', 'calendarCallback_arrival_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar8.setMonthCombo(false);
                  tCalendar8.setYearCombo(false);
                  //-->
                  </script>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        </form>
      </table>
    </td>
    <td>&nbsp;
      
    </td>
  </tr>
</table>

<br />

<?php echo '
<script language="JavaScript">
<!--
var f = getForm(\'email_filter_form\');

'; ?>

var date_fields = new Array();
date_fields[date_fields.length] = new Option('arrival_date', '<?php echo $this->_tpl_vars['options']['arrival_date']['start']; ?>
');
date_fields[date_fields.length] = new Option('arrival_date_end', '<?php echo $this->_tpl_vars['options']['arrival_date']['end']; ?>
');
<?php echo '

var elements_to_hide = new Array(\'arrival_date\');
for (var i = 0; i < elements_to_hide.length; i++) {
    toggleVisibility(elements_to_hide[i]);
    toggleDateFields(f, elements_to_hide[i]);
    var filter_type = getFormElement(f, elements_to_hide[i] + \'[filter_type]\');
    checkDateFilterType(f, filter_type);
}

for (var i = 0; i < date_fields.length; i++) {
    if (!isWhitespace(date_fields[i].value)) {
        selectDateOptions(date_fields[i].text, date_fields[i].value);
    }
}
//-->
</script>
'; ?>