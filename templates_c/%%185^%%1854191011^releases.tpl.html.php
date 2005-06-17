<?php /* Smarty version 2.6.2, created on 2004-07-02 11:00:19
         compiled from manage/releases.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_select_date', 'manage/releases.tpl.html', 81, false),array('function', 'cycle', 'manage/releases.tpl.html', 153, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/dynCalendar.js"></script>
              <?php echo '
              <script language="JavaScript">
              <!--
              function validateForm(f)
              {
                  if (isWhitespace(f.title.value)) {
                      alert(\'Please enter the title of this release.\');
                      selectField(f, \'title\');
                      return false;
                  }
                  return true;
              }
              function calendarCallback(day, month, year)
              {
                  selectOption(this.document.release_form, \'scheduled_date[Day]\', day);
                  selectOption(this.document.release_form, \'scheduled_date[Month]\', month);
                  selectOption(this.document.release_form, \'scheduled_date[Year]\', year);
              }
              //-->
              </script>
              '; ?>

              <form name="release_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <input type="hidden" name="prj_id" value="<?php echo $this->_tpl_vars['project']['prj_id']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td class="default">
                  <b>Manage Releases</b>
                </td>
                <td class="default" align="right">
                  (Current Team: <?php echo $this->_tpl_vars['project']['prj_title']; ?>
)
                </td>
              </tr>
              <?php if ($this->_tpl_vars['result'] != ""): ?>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center" class="error">
                  <?php if ($_POST['cat'] == 'new'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to add the new release.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this new release.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the release was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the release information.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this release.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the release was updated successfully.
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
                  <input type="text" name="title" size="40" class="default" value="<?php echo $this->_tpl_vars['info']['pre_title']; ?>
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
                  <b>Tentative Date:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <?php echo smarty_function_html_select_date(array('field_array' => 'scheduled_date','prefix' => "",'time' => $this->_tpl_vars['info']['pre_scheduled_date'],'start_year' => "-10",'end_year' => "+10",'all_extra' => 'class="default"'), $this);?>

                  <script language="JavaScript" type="text/javascript">
                  <!--
                  tCalendar = new dynCalendar('tCalendar', 'calendarCallback', '<?php echo $this->_tpl_vars['rel_url']; ?>
images/');
                  tCalendar.setMonthCombo(false);
                  tCalendar.setYearCombo(false);
                  <?php if ($this->_tpl_vars['info']['scheduled_month']): ?>
                  tCalendar.setCurrentMonth(<?php echo $this->_tpl_vars['info']['scheduled_month']; ?>
-1);
                  tCalendar.setCurrentYear(<?php echo $this->_tpl_vars['info']['scheduled_year']; ?>
);
                  <?php endif; ?>
                  //-->
                  </script>
                </td>
              </tr>
              <tr>
                <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Status:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="status" class="default">
                    <option value="available" <?php if ($this->_tpl_vars['info']['pre_status'] == 'available'): ?>selected<?php endif; ?>>Available - Users may use this release</option>
                    <option value="unavailable" <?php if ($this->_tpl_vars['info']['pre_status'] == 'unavailable'): ?>selected<?php endif; ?>>Unavailable - Users may NOT use this release</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Release">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create Release">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Releases:</b>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <?php echo '
                  <script language="JavaScript">
                  <!--
                  function checkDelete(f)
                  {
                      if (!hasOneChecked(f, \'items[]\')) {
                          alert(\'Please select at least one of the releases.\');
                          return false;
                      }
                      if (!confirm(\'This action will remove the selected entries.\')) {
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
                    <input type="hidden" name="prj_id" value="<?php echo $this->_tpl_vars['project']['prj_id']; ?>
">
                    <input type="hidden" name="cat" value="delete">
                    <tr>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Title</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Tentative Date</b></td>
                      <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Status</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pre_id']; ?>
"></td>
                      <td width="40%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pre_id']; ?>
&prj_id=<?php echo $this->_tpl_vars['project']['prj_id']; ?>
" title="update this entry"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pre_title']; ?>
</a>
                      </td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pre_scheduled_date']; ?>
</td>
                      <td bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">&nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['pre_status']; ?>
</td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No releases could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
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
