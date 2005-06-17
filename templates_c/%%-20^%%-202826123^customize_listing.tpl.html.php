<?php /* Smarty version 2.6.2, created on 2004-07-02 10:55:41
         compiled from manage/customize_listing.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'manage/customize_listing.tpl.html', 77, false),array('function', 'cycle', 'manage/customize_listing.tpl.html', 157, false),array('modifier', 'escape', 'manage/customize_listing.tpl.html', 107, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
              <script language="JavaScript">
              <!--
              var url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
              var psd_id = '<?php echo $_GET['id']; ?>
';
              <?php echo '
              function retrieveStatuses(f)
              {
                  if (psd_id == \'\') {
                      url += \'?prj_id=\' + getSelectedOption(f, \'project\');
                  } else {
                      url += \'?cat=edit&id=\' + psd_id + \'&prj_id=\' + getSelectedOption(f, \'project\');
                  }
                  window.location.href = url;
              }
              function validateForm(f)
              {
                  if (getSelectedOption(f, \'project\') == \'-1\') {
                      alert(\'Please choose the team that you wish to customize.\');
                      selectField(f, \'project\');
                      return false;
                  }
                  return true;
              }
              //-->
              </script>
              '; ?>

              <form name="customize_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
              <?php if ($_GET['cat'] == 'edit'): ?>
              <input type="hidden" name="cat" value="update">
              <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>
">
              <?php else: ?>
              <input type="hidden" name="cat" value="new">
              <?php endif; ?>
              <tr>
                <td class="default" nowrap>
                  <b>Customize Issue Listing Screen</b>
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
                      An error occurred while trying to add the new customization.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this new customization.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the customization was added successfully.
                    <?php endif; ?>
                  <?php elseif ($_POST['cat'] == 'update'): ?>
                    <?php if ($this->_tpl_vars['result'] == -1): ?>
                      An error occurred while trying to update the customization information.
                    <?php elseif ($this->_tpl_vars['result'] == -2): ?>
                      Please enter the title for this customization.
                    <?php elseif ($this->_tpl_vars['result'] == 1): ?>
                      Thank you, the customization was updated successfully.
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Team:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="80%">
                  <select name="project" class="default" onChange="javascript:retrieveStatuses(this.form);">
                    <option value="-1">Please choose an option</option>
                  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['project_list'],'selected' => $this->_tpl_vars['project_id']), $this);?>

                  </select>
                  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Status:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="status" class="default">
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['status_list'],'selected' => $this->_tpl_vars['info']['psd_sta_id']), $this);?>

                  </select>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Date Field:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <select name="date_field" class="default">
                    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['date_fields'],'selected' => $this->_tpl_vars['info']['psd_date_field']), $this);?>

                  </select>
                </td>
              </tr>
              <tr>
                <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
                  <b>Label:</b>
                </td>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
                  <input type="text" name="label" class="default" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['info']['psd_label'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                </td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
                  <?php if ($_GET['cat'] == 'edit'): ?>
                  <input class="button" type="submit" value="Update Customization">
                  <?php else: ?>
                  <input class="button" type="submit" value="Create Customization">
                  <?php endif; ?>
                  <input class="button" type="reset" value="Reset">
                </td>
              </tr>
              </form>
              <tr>
                <td colspan="2" class="default">
                  <b>Existing Customizations:</b>
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
                          alert(\'Please select at least one of the customizations.\');
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
                    <input type="hidden" name="cat" value="delete">
                    <tr>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" nowrap>&nbsp;</td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Team</b></td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Status</b></td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Label</b></td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>Date Field</b></td>
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
"><input type="checkbox" name="items[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['psd_id']; ?>
"></td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['prj_title']; ?>
</td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<a class="link" href="<?php echo $_SERVER['PHP_SELF']; ?>
?cat=edit&id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['psd_id']; ?>
" title="update this entry"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sta_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
                      </td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['psd_label'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
                      <td width="30%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['date_field']; ?>
</td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        <i>No customizations could be found.</i>
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td colspan="5" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
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
