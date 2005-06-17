<?php /* Smarty version 2.6.2, created on 2004-11-05 12:09:40
         compiled from quick_filter_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'quick_filter_form.tpl.html', 111, false),array('function', 'get_display_style', 'quick_filter_form.tpl.html', 130, false),array('function', 'html_options', 'quick_filter_form.tpl.html', 175, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
var anyBranchArray = new Array(1);
anyBranchArray[0] = "";
var onCampusArray = new Array(15);
var offCampusArray = new Array(9);
onCampusArray[0] = 36;
onCampusArray[1] = 17;
onCampusArray[2] = 16;
onCampusArray[3] = 23;
onCampusArray[4] = 7;
onCampusArray[5] = 24;
onCampusArray[6] = 14;
onCampusArray[7] = 13;
onCampusArray[8] = 11;
onCampusArray[9] = 33; 
onCampusArray[10] = 9; 
onCampusArray[11] = 38; 
onCampusArray[12] = 26; 
onCampusArray[13] = 22; 
onCampusArray[14] = 6; 

offCampusArray[0] = 15; 
offCampusArray[1] = 12; 
offCampusArray[2] = 25; 
offCampusArray[3] = 10; 
offCampusArray[4] = 8; 
offCampusArray[5] = 37;
offCampusArray[6] = 40; 
offCampusArray[7] = 39; 
offCampusArray[8] = 41; 
function clearFilters(f)
{
    f.keywords.value = \'\';
    f.has_attachments.value = \'\';
	// @@@ CK - added so the team/projects select box can be reselt can also be cleared
    f.projects.selectedIndex = 0;
    f.users.selectedIndex = 0;
    f.category.selectedIndex = 0;
    f.status.selectedIndex = 0;
    f.priority.selectedIndex = 0;
    f.time_tracking_category.selectedIndex = 0;
	// @@@ CK - added so the custom fields can also be cleared
'; ?>

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
//	f.custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
].selectedIndex = 0;
//	f.custom_fields[1].selectedIndex = 0;
//	var element<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
 = getPageElement('custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]');
//	var element<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
.selectedIndex = 0;
<?php endfor; endif; ?>
<?php echo '
	var element1 = getPageElement(\'custom_fields[1]\');
	element1.selectedIndex = 0;
	var element2 = getPageElement(\'custom_fields[2]\');
	if (element2 != null) {
		element2.selectedIndex = 0;
	}
	var element3 = getPageElement(\'custom_fields[3]\');
	if (element3 != null) {
		element3.selectedIndex = 0;
	}
	var element4 = getPageElement(\'custom_fields[4]\');
	element4.value = \'\';
	var element5 = getPageElement(\'custom_fields[5]\');
	element5.selectedIndex = 0;
	var element6 = getPageElement(\'custom_fields[6]\');
	if (element6 != null) {
		element6.value = \'\';
	}
	var element7 = getPageElement(\'custom_fields[7]\');
	if (element7 != null) {
//		element7.selectedIndex = 0;
	}
	var element8 = getPageElement(\'custom_fields[8]\');
	if (element8 != null) {
		element8.value = \'\';
	}



    // now for the fields that are only available through the advanced search page
    setHiddenFieldValue(f, \'created_date[Year]\', \'\');
    setHiddenFieldValue(f, \'created_date[Month]\', \'\');
    setHiddenFieldValue(f, \'created_date[Day]\', \'\');
    setHiddenFieldValue(f, \'created_date[filter_type]\', \'\');
    setHiddenFieldValue(f, \'updated_date[Year]\', \'\');
    setHiddenFieldValue(f, \'updated_date[Month]\', \'\');
    setHiddenFieldValue(f, \'updated_date[Day]\', \'\');
    setHiddenFieldValue(f, \'updated_date[filter_type]\', \'\');
    setHiddenFieldValue(f, \'last_response_date[Year]\', \'\');
    setHiddenFieldValue(f, \'last_response_date[Month]\', \'\');
    setHiddenFieldValue(f, \'last_response_date[Day]\', \'\');
    setHiddenFieldValue(f, \'last_response_date[filter_type]\', \'\');
    setHiddenFieldValue(f, \'first_response_date[Year]\', \'\');
    setHiddenFieldValue(f, \'first_response_date[Month]\', \'\');
    setHiddenFieldValue(f, \'first_response_date[Day]\', \'\');
    setHiddenFieldValue(f, \'first_response_date[filter_type]\', \'\');
    setHiddenFieldValue(f, \'closed_date[Year]\', \'\');
    setHiddenFieldValue(f, \'closed_date[Month]\', \'\');
    setHiddenFieldValue(f, \'closed_date[Day]\', \'\');
    setHiddenFieldValue(f, \'closed_date[filter_type]\', \'\');
    setHiddenFieldValue(f, \'show_authorized_issues\', \'\');
    setHiddenFieldValue(f, \'show_notification_list_issues\', \'\');
    f.submit();
}
'; ?>

var get_urls = new Array();
<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['csts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
get_urls[<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_id']; ?>
] = 'keywords=<?php echo ((is_array($_tmp=$this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_keywords'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&users=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_users']; ?>
&category=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_iss_prc_id']; ?>
&status=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_iss_sta_id']; ?>
&priority=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_iss_pri_id']; ?>
&release=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_iss_pre_id']; ?>
&rows=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_rows']; ?>
&sort_by=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_sort_by']; ?>
&sort_order=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_sort_order']; ?>
&hide_closed=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_hide_closed']; ?>
&show_authorized_issues=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_show_authorized']; ?>
&show_notification_list_issues=<?php echo $this->_tpl_vars['csts'][$this->_sections['i']['index']]['cst_show_notification_list']; ?>
';
<?php endfor; endif; ?>
<?php echo '
function runCustomFilter(f)
{
    var cst_id = getSelectedOption(f, \'custom_filter\');
    if (isWhitespace(cst_id)) {
        alert(\'Please select the custom filter to search against.\');
        f.custom_filter.focus();
        return false;
    }
    f.action = \'list.php?cat=search&\' + get_urls[cst_id];
    location.href = f.action;
    return false;
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
        <form action="list.php" method="get">
        <input type="hidden" name="cat" value="search">
        <input type="hidden" name="pagerRow" value="0">
        <input type="hidden" name="created_date[Year]" value="<?php echo $this->_tpl_vars['options']['created_date']['Year']; ?>
">
        <input type="hidden" name="created_date[Month]" value="<?php echo $this->_tpl_vars['options']['created_date']['Month']; ?>
">
        <input type="hidden" name="created_date[Day]" value="<?php echo $this->_tpl_vars['options']['created_date']['Day']; ?>
">
        <input type="hidden" name="created_date[filter_type]" value="<?php echo $this->_tpl_vars['options']['created_date']['filter_type']; ?>
">
        <input type="hidden" name="updated_date[Year]" value="<?php echo $this->_tpl_vars['options']['updated_date']['Year']; ?>
">
        <input type="hidden" name="updated_date[Month]" value="<?php echo $this->_tpl_vars['options']['updated_date']['Month']; ?>
">
        <input type="hidden" name="updated_date[Day]" value="<?php echo $this->_tpl_vars['options']['updated_date']['Day']; ?>
">
        <input type="hidden" name="updated_date[filter_type]" value="<?php echo $this->_tpl_vars['options']['updated_date']['filter_type']; ?>
">
        <input type="hidden" name="last_response_date[Year]" value="<?php echo $this->_tpl_vars['options']['last_response_date']['Year']; ?>
">
        <input type="hidden" name="last_response_date[Month]" value="<?php echo $this->_tpl_vars['options']['last_response_date']['Month']; ?>
">
        <input type="hidden" name="last_response_date[Day]" value="<?php echo $this->_tpl_vars['options']['last_response_date']['Day']; ?>
">
        <input type="hidden" name="last_response_date[filter_type]" value="<?php echo $this->_tpl_vars['options']['last_response_date']['filter_type']; ?>
">
        <input type="hidden" name="first_response_date[Year]" value="<?php echo $this->_tpl_vars['options']['first_response_date']['Year']; ?>
">
        <input type="hidden" name="first_response_date[Month]" value="<?php echo $this->_tpl_vars['options']['first_response_date']['Month']; ?>
">
        <input type="hidden" name="first_response_date[Day]" value="<?php echo $this->_tpl_vars['options']['first_response_date']['Day']; ?>
">
        <input type="hidden" name="first_response_date[filter_type]" value="<?php echo $this->_tpl_vars['options']['first_response_date']['filter_type']; ?>
">
        <input type="hidden" name="closed_date[Year]" value="<?php echo $this->_tpl_vars['options']['closed_date']['Year']; ?>
">
        <input type="hidden" name="closed_date[Month]" value="<?php echo $this->_tpl_vars['options']['closed_date']['Month']; ?>
">
        <input type="hidden" name="closed_date[Day]" value="<?php echo $this->_tpl_vars['options']['closed_date']['Day']; ?>
">
        <input type="hidden" name="closed_date[filter_type]" value="<?php echo $this->_tpl_vars['options']['closed_date']['filter_type']; ?>
">
        <input type="hidden" name="show_authorized_issues" value="<?php echo $this->_tpl_vars['options']['show_authorized_issues']; ?>
">
        <input type="hidden" name="show_notification_list_issues" value="<?php echo $this->_tpl_vars['options']['show_notification_list_issues']; ?>
">
        <tr>
          <td>
            <span class="<?php if ($this->_tpl_vars['options']['keywords']): ?>default-search-used<?php else: ?>default<?php endif; ?>">Keyword(s):</span><br />
            <input class="default" type="text" name="keywords" size="15" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['keywords'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
          </td>
          <td>
            <span class="<?php if ($this->_tpl_vars['options']['projects'] != 'all' && $this->_tpl_vars['options']['projects'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Team(s):</span><br />
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
            <span class="<?php if ($this->_tpl_vars['options']['users'] != 'any' && $this->_tpl_vars['options']['users'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Assigned:</span><br />
            <select name="users" class="default">
              <option value="">any</option>
              <option value="-1" <?php if ($this->_tpl_vars['options']['users'] == '-1'): ?>selected<?php endif; ?>>un-assigned</option>
              <option value="-2" <?php if ($this->_tpl_vars['options']['users'] == '-2'): ?>selected<?php endif; ?>>myself and un-assigned</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['options']['users']), $this);?>

            </select>
          </td>
          <td>
            <span class="<?php if ($this->_tpl_vars['options']['status'] != 'any' && $this->_tpl_vars['options']['status'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Status:</span><br />
            <select name="status" class="default">
              <option value="">any</option>
              <?php if (count($_from = (array)$this->_tpl_vars['status'])):
    foreach ($_from as $this->_tpl_vars['sta_id'] => $this->_tpl_vars['sta_title']):
?>
              <option value="<?php echo $this->_tpl_vars['sta_id']; ?>
" <?php if ($this->_tpl_vars['sta_id'] == $this->_tpl_vars['options']['status']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['sta_title']; ?>
</option>
              <?php endforeach; unset($_from); endif; ?>
            </select>
          </td>
          <td nowrap valign="middle">
            <input class="button" type="submit" value="Search">
            <input class="button" type="button" value="Clear" onClick="javascript:clearFilters(this.form);">
          </td>

		</tr>
		<tr>

          <td>
            <span class="<?php if ($this->_tpl_vars['options']['category'] != 'any' && $this->_tpl_vars['options']['category'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Category:</span><br />
            <select name="category" class="default">
              <option value="">any</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['categories'],'selected' => $this->_tpl_vars['options']['category']), $this);?>

            </select>
          </td>

          <td>
            <span class="<?php if ($this->_tpl_vars['options']['priority'] != 'all' && $this->_tpl_vars['options']['priority'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Priority:</span><br />
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
" <?php if ($this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id'] == $this->_tpl_vars['options']['priority']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_title']; ?>
</option>
              <?php endfor; endif; ?>
            </select>
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

          <td <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>rowspan='3' colspan="2"<?php endif; ?>><span class="<?php if ($this->_tpl_vars['lastcustom'] != 'all' && $this->_tpl_vars['lastcustom'] != 'any' && $this->_tpl_vars['lastcustom'] != '' && $this->_tpl_vars['lastcustom'][0] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">


            <?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_title']; ?>
:

				<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'text' && $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'textarea'): ?>
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
]" maxlength="255" size="35" value="<?php echo $this->_tpl_vars['lastcustom']; ?>
">
            <?php elseif ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'textarea'): ?>
            <textarea id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" rows="10" cols="60" value="<?php echo $this->_tpl_vars['lastcustom']; ?>
"></textarea>
            <?php else: ?>
			  <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?><table cellpadding="0" cellspacing="0" bgcolor="#CCCCCC"><tr><td><?php endif; ?>
            <select id="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]" <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple' || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>multiple size="8"<?php endif; ?> class="default" name="custom_fields[<?php echo $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']; ?>
]<?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple' || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>[]<?php endif; ?>">
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple'): ?><option value=""<?php if ($this->_tpl_vars['lastcustom'][0] == ""):  $this->assign('lastcustom', ""); ?>selected<?php endif; ?>>any</option><?php endif; ?>
              <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] != 'multiple'): ?><option value="none"<?php if ($this->_tpl_vars['lastcustom'][0] == 'none'):  $this->assign('lastcustom', ""); ?>selected<?php endif; ?>>not set</option><?php endif; ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['lastcustom']), $this);?>

            </select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id']); ?>
            <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_type'] == 'multiple' || $this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
			  <?php if ($this->_tpl_vars['custom_fields'][$this->_sections['i']['index']]['fld_id'] == 2): ?>
				</td><td valign="top"><table><tr><td>
				<input type="button" class="shortcut" value="On Campus" onClick="javascript:clearSelectedOptions(getFormElement(this.form, 'custom_fields[2][]'));selectCustomOptions(this.form, 'custom_fields[2][]', onCampusArray);">
				<br /></td></tr><tr><td>
				<input type="button" class="shortcut" value="Off Campus" onClick="javascript:clearSelectedOptions(getFormElement(this.form, 'custom_fields[2][]'));selectCustomOptions(this.form, 'custom_fields[2][]', offCampusArray);">
				<br /></td></tr><tr><td>
				<input type="button" class="shortcut" value="Clear Selections" onClick="javascript:clearSelectedOptions(getFormElement(this.form, 'custom_fields[2][]'));selectCustomOptions(this.form, 'custom_fields[2][]', anyBranchArray);">
				</td></tr></table></td></tr></table>
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

          <td>
			<span class="<?php if ($this->_tpl_vars['options']['time_tracking_category'] != 'all' && $this->_tpl_vars['options']['time_tracking_category'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Time Tracking Category:</span><br />
            <select name="time_tracking_category" class="default">
              <option value="">any</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['time_categories'],'selected' => $this->_tpl_vars['options']['time_tracking_category']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'time_tracking_category')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <span class="<?php if ($this->_tpl_vars['options']['has_attachments'] != ''): ?>default-search-used<?php else: ?>default<?php endif; ?>">Has Attachments?:</span><br />
            <select name="has_attachments" class="default">
              <option value="">any</option>
              <option value="yes" <?php if ($this->_tpl_vars['options']['has_attachments'] == 'yes'): ?>selected<?php endif; ?>>yes</option>
              <option value="no" <?php if ($this->_tpl_vars['options']['has_attachments'] == 'no'): ?>selected<?php endif; ?>>no</option>
            </select>
          </td>
        </tr>
        </form>
      </table>
    </td>
    <td>&nbsp;
      
    </td>
  </tr>
  <tr id="custom_filter_form1" <?php echo smarty_function_get_display_style(array('element_name' => 'custom_filter_form'), $this);?>
>
    <td>&nbsp;
      
    </td>
    <td>
      <table bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%" border="0" cellspacing="0" cellpadding="4">
        <form action="list.php" method="get">
        <tr>
          <td class="default">
            [ <a class="link" href="javascript:void(open('<?php echo $this->_tpl_vars['rel_url']; ?>
searchbar.php', '_search'));">quick search bar</a> ]
          </td>
          <td class="default" align="center">
            <a target="_top" title="create advanced searches" href="<?php echo $this->_tpl_vars['rel_url']; ?>
adv_search.php" class="link">Advanced Search</a>
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
