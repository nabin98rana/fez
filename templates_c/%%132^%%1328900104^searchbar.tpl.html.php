<?php /* Smarty version 2.6.2, created on 2004-06-30 10:51:22
         compiled from searchbar.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'searchbar.tpl.html', 78, false),array('function', 'html_options', 'searchbar.tpl.html', 86, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('bgcolor' => ($this->_tpl_vars['dark_color']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<script language="JavaScript">
<!--
function clearFilters(f)
{
    f.keywords.value = \'\';
    f.users.selectedIndex = 0;
    f.status.selectedIndex = 0;
    f.category.selectedIndex = 0;
    f.priority.selectedIndex = 0;
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
//-->
</script>
'; ?>

<table width="100%" border="0" cellspacing="0" cellpadding="4">
  <form target="_main" action="list.php" method="get">
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
    <td class="default">
      <span style="font-size: 25px; color: black; font-weight: bold;">Quick Search</span>
    </td>
  </tr>
</table>
<hr size="1" noshade color="<?php echo $this->_tpl_vars['cell_color']; ?>
">
<table width="100%" border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td>
      <span class="default">Keyword(s):</span><br />
      <input class="default" type="text" name="keywords" size="15" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['options']['keywords'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
    </td>
  </tr>
  <tr>
    <td>
      <span class="default">Assigned:</span><br />
      <select name="users" class="default">
        <option value="">any</option>
        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['options']['users']), $this);?>

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
" <?php if ($this->_tpl_vars['sta_id'] == $this->_tpl_vars['options']['status']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['sta_title']; ?>
</option>
        <?php endforeach; unset($_from); endif; ?>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      <span class="default">Category:</span><br />
      <select name="category" class="default">
        <option value="">any</option>
        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['categories'],'selected' => $this->_tpl_vars['options']['category']), $this);?>

      </select>
    </td>
  </tr>
  <tr>
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
" <?php if ($this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_id'] == $this->_tpl_vars['options']['priority']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['priorities'][$this->_sections['i']['index']]['pri_title']; ?>
</option>
        <?php endfor; endif; ?>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      <input class="button" type="submit" value="Search">
      <input class="button" type="button" value="Clear" onClick="javascript:clearFilters(this.form);">
    </td>
  </tr>
  </form>
</table>

<br />
<?php $this->assign('benchmark_total', ""); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "app_info.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>