<?php /* Smarty version 2.6.2, created on 2004-10-19 05:03:23
         compiled from time_tracking.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'time_tracking.tpl.html', 58, false),array('function', 'get_innerhtml', 'time_tracking.tpl.html', 62, false),array('function', 'get_display_style', 'time_tracking.tpl.html', 66, false),array('function', 'cycle', 'time_tracking.tpl.html', 78, false),array('function', 'html_options', 'time_tracking.tpl.html', 123, false),array('function', 'html_select_date', 'time_tracking.tpl.html', 148, false),array('function', 'html_select_time', 'time_tracking.tpl.html', 149, false),)), $this); ?>

<?php echo '

<script language="JavaScript">
<!--
function validateTimeForm(f)
{
    if (isWhitespace(f.summary.value)) {
        alert(\'Please enter the summary for this new time tracking entry.\');
        selectField(f, \'summary\');
        return false;
    }
    if (f.category.options[f.category.selectedIndex].value == \'\') {
        alert(\'Please choose the time tracking category for this new entry.\');
        selectField(f, \'category\');
        return false;
    }
    if (!hasOneSelected(f, \'assignments[]\')) {
        alert(\'Please select an assignment for this timetracking entry\');
        selectField(f, \'assignments[]\');
        return false;
    }

    if ((isWhitespace(f.time_spent.value)) || (!isNumberOnly(f.time_spent.value))) {
        alert(\'Please enter integers (or floating point numbers) on the time spent field.\');
        selectField(f, \'time_spent\');
        return false;
    }
    var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var popupWin = window.open(\'\', \'_time\', features);
    popupWin.focus();
    return true;
}

function deleteTimeEntry(time_id)
{
    if (!confirm(\'This action will permanently delete the specified time tracking entry.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'popup.php?cat=delete_time&id=\' + time_id, \'_popup\', features);
        popupWin.focus();
    }
}
//-->
</script>
'; ?>

<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="add_time_form" onSubmit="javascript:return validateTimeForm(this);" target="_time" method="post" action="<?php echo $this->_tpl_vars['rel_url']; ?>
popup.php">
<input type="hidden" name="cat" value="add_time">
<input type="hidden" name="issue_id" value="<?php echo $_GET['id']; ?>
">
  <tr>
    <td width="100%">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
        <tr>
          <td class="default" nowrap>
            <b>Time Tracking (<?php echo count($this->_tpl_vars['time_entries']); ?>
)</b>
          </td>
          <td align="right" class="default">
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            [ <a id="time_tracker_link" class="link" href="javascript:void(null);" onClick="javascript:toggleVisibility('time_tracker');"><?php echo smarty_function_get_innerhtml(array('element_name' => 'time_tracker'), $this);?>
</a> ]
            <?php endif; ?>
          </td>
        </tr>
        <tr id="time_tracker1" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td colspan="2" class="default" width="100%">
            <table width="100%" cellpadding="2" cellspacing="1">
              <tr bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
                <td valign="top" class="default_white">Date of Work</td>
                <td valign="top" class="default_white">User</td>
                <td valign="top" class="default_white">Time Spent</td>
                <td valign="top" class="default_white">Category</td>
                <td valign="top" width="50%" class="default_white">Summary</td>
                <td valign="top" class="default_white">Team</td>
              </tr>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['time_entries']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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

              <tr bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                <td valign="top" class="default"><?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['ttr_created_date']; ?>
</td>
                <td valign="top" class="default">
                  <?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['usr_full_name']; ?>

                  [ <a class="link" href="javascript:void(null);" onClick="javascript:deleteTimeEntry(<?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['ttr_id']; ?>
);">delete</a> ]
                </td>
                <td valign="top" class="default"><?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['formatted_time']; ?>
</td>
                <td valign="top" class="default"><?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['ttc_title']; ?>
</td>
                <td valign="top" class="default"><?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['ttr_summary']; ?>
</td>
                <td valign="top" class="default"><?php echo $this->_tpl_vars['time_entries'][$this->_sections['i']['index']]['prj_title']; ?>
</td>
              </tr>
              <?php if ($this->_sections['i']['last']): ?>
              <tr>
                <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" colspan="2" class="default_white" align="right">Total Time Spent:</td>
                <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" colspan="4" class="default"><?php echo $this->_tpl_vars['total_time_spent']; ?>
</td>
              </tr>
              <?php endif; ?>
              <?php endfor; else: ?>
              <tr>
                <td colspan="6" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                  <i>No time tracking entries could be found.</i>
                </td>
              </tr>
              <?php endif; ?>
            </table>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
        <tr id="time_tracker2" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td colspan="2" class="default"><b>Record Time Worked:</b></td>
        </tr>
        <tr id="time_tracker3" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Summary:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%"><textarea rows="12" cols="100" class="default" name="summary" size="40"></textarea><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'summary')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
        </tr>

		<tr id="time_tracker8" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Users:</b><br /> (person(s) who spent time)
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" >
            <input type="hidden" name="keep_assignments" value="no">
            <select size="<?php if ($this->_tpl_vars['issue']['has_inactive_users']): ?>3<?php else: ?>4<?php endif; ?>" multiple class="default" name="assignments[]" onChange="javascript:showSelections('add_time_form', 'assignments[]');">
              <?php if ($this->_tpl_vars['issue']['has_inactive_users']): ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users']), $this);?>

              <?php else: ?>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['users'],'selected' => $this->_tpl_vars['user_id']), $this);?>

              <?php endif; ?>
            </select><input type="button" class="shortcut" value="Clear Selections" onClick="javascript:clearSelectedOptions(getFormElement(this.form, 'assignments[]'));showSelections('add_time_form', 'assignments[]');"><br />
            <div class="default" id="selection_assignments[]"><?php if ($this->_tpl_vars['user_fullname']): ?>Current Selections: <?php echo $this->_tpl_vars['user_fullname'];  endif; ?></div>
          </td>
		</tr>
        <tr id="time_tracker4" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Category:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%"> 
            <select name="category" class="default">
              <option value="">Please choose a category</option>
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['time_categories']), $this);?>

            </select>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'category')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr id="time_tracker5" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Time Spent:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%"><input class="default" type="text" size="5" name="time_spent" class="default"> <span class="default">(in minutes)</span><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'time_spent')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
        </tr>
        <tr id="time_tracker6" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Date of Work:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <?php echo smarty_function_html_select_date(array('day_value_format' => '%02d','field_array' => 'date','prefix' => '','all_extra' => ' class="default"'), $this);?>
&nbsp;
            <?php echo smarty_function_html_select_time(array('minute_interval' => 5,'field_array' => 'date','prefix' => '','all_extra' => ' class="default"','display_seconds' => false), $this);?>

            <a href="javascript:void(null);" onClick="javascript:updateTimeFields('add_time_form', 'date[Year]', 'date[Month]', 'date[Day]', 'date[Hour]', 'date[Minute]');"><img src="images/icons/refresh.gif" border="0"></a>
          </td>
        </tr>
        <tr id="time_tracker7" <?php echo smarty_function_get_display_style(array('element_name' => 'time_tracker'), $this);?>
>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center" width="100%" nowrap>
            <input type="submit" value="Clock-In" class="button">
          </td>
        </tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</form>
</table>
<script language="JavaScript">
<!--
updateTimeFields('add_time_form', 'date[Year]', 'date[Month]', 'date[Day]', 'date[Hour]', 'date[Minute]');
//-->
</script>
