<?php /* Smarty version 2.6.2, created on 2004-09-13 14:37:26
         compiled from custom_filter_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'custom_filter_form.tpl.html', 144, false),array('function', 'html_options', 'custom_filter_form.tpl.html', 155, false),array('function', 'html_select_date', 'custom_filter_form.tpl.html', 344, false),)), $this); ?>

<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/dynCalendar.js"></script>
<?php echo '
<script language="JavaScript">
<!--
function saveCustomFilter(f)
{
    if (isWhitespace(f.title.value)) {
        selectField(f, \'title\');
        alert(\'Please enter the title for this saved search.\');
        return false;
    }
    var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var popupWin = window.open(\'\', \'_customFilter\', features);
    popupWin.focus();
    f.target = \'_customFilter\';
    f.method = \'post\';
    f.action = \'popup.php\';
    f.cat.value = \'save_filter\';
    f.submit();
}
function validateRemove(f)
{
    if (!hasOneChecked(f, \'item[]\')) {
        alert(\'Please choose which entries need to be removed.\');
        return false;
    }
    if (!confirm(\'This action will permanently delete the selected entries.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'\', \'_removeFilter\', features);
        popupWin.focus();
        return true;
    }
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
    selectOption(this.document.custom_filter_form, field_name + \'[Day]\', day);
    selectOption(this.document.custom_filter_form, field_name + \'[Month]\', month);
    selectOption(this.document.custom_filter_form, field_name + \'[Year]\', year);
}
function calendarCallback_created(day, month, year) { selectDateField(\'created_date\', day, month, year); }
function calendarCallback_created_end(day, month, year) { selectDateField(\'created_date_end\', day, month, year); }
function calendarCallback_updated(day, month, year) { selectDateField(\'updated_date\', day, month, year); }
function calendarCallback_updated_end(day, month, year) { selectDateField(\'updated_date_end\', day, month, year); }
function calendarCallback_last_response(day, month, year) { selectDateField(\'last_response_date\', day, month, year); }
function calendarCallback_last_response_end(day, month, year) { selectDateField(\'last_response_date_end\', day, month, year); }
function calendarCallback_first_response(day, month, year) { selectDateField(\'first_response_date\', day, month, year); }
function calendarCallback_first_response_end(day, month, year) { selectDateField(\'first_response_date_end\', day, month, year); }
function calendarCallback_closed(day, month, year) { selectDateField(\'closed_date\', day, month, year); }
function calendarCallback_closed_end(day, month, year) { selectDateField(\'closed_date_end\', day, month, year); }
function validateForm(f)
{
    // need to hack this value in the query string so the saved search options don\'t override this one
    if (!f.hide_closed.checked) {
        var field = getFormElement(f, \'hidden1\');
        field.name = \'hide_closed\';
        field.value = \'0\';
    }
    if (!f.show_authorized_issues.checked) {
        var field = getFormElement(f, \'hidden2\');
        field.name = \'show_authorized_issues\';
        field.value = \'\';
    }
    if (!f.show_notification_list_issues.checked) {
        var field = getFormElement(f, \'hidden3\');
        field.name = \'show_notification_list_issues\';
        field.value = \'\';
    }
    return true;
}
//-->
</script>
'; ?>

<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>&nbsp;
      
    </td>
    <td>
      <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="3" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <b>Advanced Search</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="right">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'adv_search')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <form name="custom_filter_form" method="get" action="list.php" onSubmit="javascript:return validateForm(this);">
        <input type="hidden" name="cat" value="search">
        <input type="hidden" name="hidden1" value="">
        <input type="hidden" name="hidden2" value="">
        <input type="hidden" name="hidden3" value="">
        <tr>
          <td>
            <span class="default">Keyword(s):</span><br />
            <input class="default" type="text" name="keywords" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['cst_keywords'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
          </td>
          <td>
            <span class="default">Team(s):</span><br />
            <select name="projects" class="default">
              <option value="all">All</option>
			  <?php if ($this->_tpl_vars['options']['projects'] == ''): ?>
			    <?php $this->assign('selectedTeam', $this->_tpl_vars['prj_id']); ?>
			  <?php else: ?>
                <?php $this->assign('selectedTeam', $this->_tpl_vars['options']['projects']); ?>
			  <?php endif; ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['projects'],'selected' => $this->_tpl_vars['selectedTeam']), $this);?>

            </select>
          </td>

          <td>
            <span class="default">Assigned:</span><br />
            <select name="users" class="default">
              <option value="">any</option>
              <option value="-1" <?php if ($this->_tpl_vars['options']['cst_users'] == '-1'): ?>selected<?php endif; ?>>un-assigned</option>
              <option value="-2" <?php if ($this->_tpl_vars['options']['cst_users'] == '-2'): ?>selected<?php endif; ?>>myself and un-assigned</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['options']['cst_users']), $this);?>

            </select>
          </td>
          <td>
            <span class="default">Priority:</span><br />
            <select name="priority" class="default">
              <option value="">any</option>
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
" <?php if ($this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id'] == $this->_tpl_vars['options']['cst_iss_pri_id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_title']; ?>
</option>
              <?php endfor; endif; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>
            <span class="default">Status:</span><br />
            <select name="status" class="default">
              <option value="">any</option>
              <?php if (count($_from = (array)$this->_tpl_vars['status'])):
    foreach ($_from as $this->_tpl_vars['sta_id'] => $this->_tpl_vars['sta_title']):
?>
              <option value="<?php echo $this->_tpl_vars['sta_id']; ?>
" <?php if ($this->_tpl_vars['sta_id'] == $this->_tpl_vars['options']['cst_iss_sta_id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['sta_title']; ?>
</option>
              <?php endforeach; unset($_from); endif; ?>
            </select>
          </td>


          <td>
            <span class="default">Rows Per Page:</span><br />
            <select name="rows" class="default">
              <option value="5" <?php if ($this->_tpl_vars['options']['cst_rows'] == 5): ?>selected<?php endif; ?>>5</option>
              <option value="10" <?php if ($this->_tpl_vars['options']['cst_rows'] == 10): ?>selected<?php endif; ?>>10</option>
              <option value="25" <?php if ($this->_tpl_vars['options']['cst_rows'] == 25): ?>selected<?php endif; ?>>25</option>
              <option value="50" <?php if ($this->_tpl_vars['options']['cst_rows'] == 50): ?>selected<?php endif; ?>>50</option>
              <option value="100" <?php if ($this->_tpl_vars['options']['cst_rows'] == 100): ?>selected<?php endif; ?>>100</option>
              <option value="ALL" <?php if ($this->_tpl_vars['options']['cst_rows'] == 'ALL'): ?>selected<?php endif; ?>>ALL</option>
            </select>
          </td>
          <td>
            <span class="default">Sort By:</span><br />
            <select name="sort_by" class="default">
              <option value="iss_pri_id" <?php if ($this->_tpl_vars['options']['cst_sort_by'] == 'iss_pri_id'): ?>selected<?php endif; ?>>Priority</option>
              <option value="iss_id" <?php if ($this->_tpl_vars['options']['cst_sort_by'] == 'iss_id'): ?>selected<?php endif; ?>>Issue ID</option>
              <option value="iss_sta_id" <?php if ($this->_tpl_vars['options']['cst_sort_by'] == 'iss_sta_id'): ?>selected<?php endif; ?>>Status</option>
              <option value="iss_summary" <?php if ($this->_tpl_vars['options']['cst_sort_by'] == 'iss_summary'): ?>selected<?php endif; ?>>Summary</option>
            </select>
          </td>
          <td>
            <span class="default">Has Attachments?:</span><br />
            <select name="has_attachments" class="default">
              <option value="">any</option>
              <option value="yes" <?php if ($this->_tpl_vars['options']['has_attachments'] == 'yes'): ?>selected<?php endif; ?>>yes</option>
              <option value="no" <?php if ($this->_tpl_vars['options']['has_attachments'] == 'no'): ?>selected<?php endif; ?>>no</option>
            </select>
          </td>
        </tr>
		<tr>
          <td>
			<span class="default">Time Tracking Category:</span><br />
            <select name="time_tracking_category" class="default">
              <option value="">Please choose a category</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['time_categories'],'selected' => $this->_tpl_vars['options']['time_tracking_category']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'time_tracking_category')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>



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
 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] != 9 && $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] != 10 && $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] != 11 && $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] != 12): ?>
 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 4 || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 8): ?>
		</tr>
		<tr>
 <?php endif; ?>


          <td><span class="default">
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 1): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom1']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom2']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 3): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom3']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 4): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom4']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 5): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom5']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 6): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom6']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 7): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom7']); ?>
			 <?php endif; ?>
			 <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 8): ?>
			  <?php $this->assign('lastcustom', $this->_tpl_vars['options']['custom8']); ?>
			 <?php endif; ?>


            <?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
:
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_report_form_required']): ?>
            <script language="JavaScript">
            <!--
//            custom_fields[custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', '<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
');
//            required_custom_fields[required_custom_fields.length] = new Option('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>', <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>'multiple'<?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'combo'): ?>'combo'<?php else: ?>'whitespace'<?php endif; ?>);
            //-->
            </script>
            <?php endif; ?>
			</span><br />
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'text'): ?>
            <input id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" class="default" type="text" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" maxlength="255" size="50" value="<?php echo $this->_tpl_vars['lastcustom']; ?>
">
            <?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'textarea'): ?>
            <textarea id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" rows="10" cols="60" value="<?php echo $this->_tpl_vars['lastcustom']; ?>
"></textarea>
            <?php else: ?>
            <select id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>[]<?php endif; ?>">
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple'): ?><option value="">any</option><?php endif; ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['lastcustom']), $this);?>

            </select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']); ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "custom_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'] != ""): ?>
            <span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
            <?php endif; ?>
          </td>

		  <?php endif; ?>
        <?php endfor; endif; ?>


        </tr>


        <tr>
          <td colspan="3">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
			  <td nowrap>
				<span class="default">Sort Order:</span>&nbsp;
				<select name="sort_order" class="default">
				  <option value="asc" <?php if ($this->_tpl_vars['options']['cst_sort_order'] == 'asc'): ?>selected<?php endif; ?>>asc</option>
				  <option value="desc" <?php if ($this->_tpl_vars['options']['cst_sort_order'] == 'desc'): ?>selected<?php endif; ?>>desc</option>
				</select>
				&nbsp;&nbsp;
			  </td>

                <td nowrap class="default">
                  Show Issues in Which I Am:&nbsp;
                </td>
                <td width="80%" class="default">
                  <input type="checkbox" name="show_authorized_issues" value="yes" <?php if ($this->_tpl_vars['options']['cst_show_authorized'] == 'yes'): ?>checked<?php endif; ?>>
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('custom_filter_form', 'show_authorized_issues');">Authorized to Send Emails</a>
                  <input type="checkbox" name="show_notification_list_issues" value="yes" <?php if ($this->_tpl_vars['options']['cst_show_notification_list'] == 'yes'): ?>checked<?php endif; ?>>
                  <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('custom_filter_form', 'show_notification_list_issues');">In Notification List</a>
					<input type="checkbox" name="hide_closed" value="1" <?php if ($this->_tpl_vars['options']['cst_hide_closed'] == 1): ?>checked<?php endif; ?>> <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('custom_filter_form', 'hide_closed');">Hide Closed Issues</a>
				</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="5">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['cst_created_date_filter_type'] != ""): ?>checked<?php endif; ?> type="checkbox" name="filter[created_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'created_date');">
                  <span class="default">Created:</span><br />
                  <select name="created_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['cst_created_date_filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_created_date_filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_created_date_filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'created_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar = new dynCalendar('tCalendar', 'calendarCallback_created', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar.setMonthCombo(false);
                  tCalendar.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="created_date1" width="50%" valign="bottom">
                  <span class="default">Created: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'created_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar2 = new dynCalendar('tCalendar2', 'calendarCallback_created_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar2.setMonthCombo(false);
                  tCalendar2.setYearCombo(false);
                  //-->
                  </script>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['cst_updated_date_filter_type'] != ""): ?>checked<?php endif; ?> type="checkbox" name="filter[updated_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'updated_date');">
                  <span class="default">Last Updated:</span><br />
                  <select name="updated_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['cst_updated_date_filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_updated_date_filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_updated_date_filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'updated_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar3 = new dynCalendar('tCalendar3', 'calendarCallback_updated', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar3.setMonthCombo(false);
                  tCalendar3.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="updated_date1" width="50%" valign="bottom">
                  <span class="default">Last Updated: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'updated_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar4 = new dynCalendar('tCalendar4', 'calendarCallback_updated_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar4.setMonthCombo(false);
                  tCalendar4.setYearCombo(false);
                  //-->
                  </script>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['cst_first_response_date_filter_type'] != ""): ?>checked<?php endif; ?> type="checkbox" name="filter[first_response_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'first_response_date');">
                  <span class="default">First Response by Staff:</span><br />
                  <select name="first_response_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['cst_first_response_date_filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_first_response_date_filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_first_response_date_filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'first_response_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar7 = new dynCalendar('tCalendar7', 'calendarCallback_first_response', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar7.setMonthCombo(false);
                  tCalendar7.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="first_response_date1" width="50%" valign="bottom">
                  <span class="default">First Response By Staff: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'first_response_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar8 = new dynCalendar('tCalendar8', 'calendarCallback_first_response_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
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
        <tr>
          <td colspan="4">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['cst_last_response_date_filter_type'] != ""): ?>checked<?php endif; ?> type="checkbox" name="filter[last_response_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'last_response_date');">
                  <span class="default">Last Response by Staff:</span><br />
                  <select name="last_response_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['cst_last_response_date_filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_last_response_date_filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_last_response_date_filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'last_response_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar5 = new dynCalendar('tCalendar5', 'calendarCallback_last_response', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar5.setMonthCombo(false);
                  tCalendar5.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="last_response_date1" width="50%" valign="bottom">
                  <span class="default">Last Response by Staff: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'last_response_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar6 = new dynCalendar('tCalendar6', 'calendarCallback_last_response_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar6.setMonthCombo(false);
                  tCalendar6.setYearCombo(false);
                  //-->
                  </script>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table width="100%" cellspacing="0" border="0" cellpadding="0">
              <tr>
                <td nowrap width="50%">
                  <input <?php if ($this->_tpl_vars['options']['cst_closed_date_filter_type'] != ""): ?>checked<?php endif; ?> type="checkbox" name="filter[closed_date]" value="yes" onClick="javascript:toggleDateFields(this.form, 'closed_date');">
                  <span class="default">Status Closed:</span><br />
                  <select name="closed_date[filter_type]" class="default" onChange="javascript:checkDateFilterType(this.form, this);">
                    <option <?php if ($this->_tpl_vars['options']['cst_closed_date_filter_type'] == 'greater'): ?>selected<?php endif; ?> value="greater">After</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_closed_date_filter_type'] == 'less'): ?>selected<?php endif; ?> value="less">Before</option>
                    <option <?php if ($this->_tpl_vars['options']['cst_closed_date_filter_type'] == 'between'): ?>selected<?php endif; ?> value="between">Between</option>
                  </select>&nbsp;
                  <?php echo smarty_function_html_select_date(array('field_array' => 'closed_date','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar9 = new dynCalendar('tCalendar9', 'calendarCallback_closed', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar9.setMonthCombo(false);
                  tCalendar9.setYearCombo(false);
                  //-->
                  </script>&nbsp;&nbsp;
                </td>
                <td nowrap id="closed_date1" width="50%" valign="bottom">
                  <span class="default">Status Closed: <i>(End date)</i></span><br />
                  <?php echo smarty_function_html_select_date(array('field_array' => 'closed_date_end','prefix' => "",'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar10 = new dynCalendar('tCalendar10', 'calendarCallback_closed_end', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar10.setMonthCombo(false);
                  tCalendar10.setYearCombo(false);
                  //-->
                  </script>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="4" align="center">
            <input class="button" type="submit" value="Run Search">
            <input class="button" type="reset" value="Reset">
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

<script language="JavaScript">
<!--
var f = getForm('custom_filter_form');

var date_fields = new Array();
date_fields[date_fields.length] = new Option('created_date', '<?php echo $this->_tpl_vars['options']['cst_created_date']; ?>
');
date_fields[date_fields.length] = new Option('created_date_end', '<?php echo $this->_tpl_vars['options']['cst_created_date_end']; ?>
');
date_fields[date_fields.length] = new Option('updated_date', '<?php echo $this->_tpl_vars['options']['cst_updated_date']; ?>
');
date_fields[date_fields.length] = new Option('updated_date_end', '<?php echo $this->_tpl_vars['options']['cst_updated_date_end']; ?>
');
date_fields[date_fields.length] = new Option('last_response_date', '<?php echo $this->_tpl_vars['options']['cst_last_response_date']; ?>
');
date_fields[date_fields.length] = new Option('last_response_date_end', '<?php echo $this->_tpl_vars['options']['cst_last_response_date_end']; ?>
');
date_fields[date_fields.length] = new Option('first_response_date', '<?php echo $this->_tpl_vars['options']['cst_first_response_date']; ?>
');
date_fields[date_fields.length] = new Option('first_response_date_end', '<?php echo $this->_tpl_vars['options']['cst_first_response_date_end']; ?>
');
date_fields[date_fields.length] = new Option('closed_date', '<?php echo $this->_tpl_vars['options']['cst_closed_date']; ?>
');
date_fields[date_fields.length] = new Option('closed_date_end', '<?php echo $this->_tpl_vars['options']['cst_closed_date_end']; ?>
');

<?php echo '
var elements_to_hide = new Array(\'created_date\', \'updated_date\', \'last_response_date\', \'first_response_date\', \'closed_date\');
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