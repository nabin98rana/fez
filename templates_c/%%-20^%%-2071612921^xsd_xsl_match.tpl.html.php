<?php /* Smarty version 2.6.2, created on 2005-03-14 15:26:33
         compiled from manage/xsd_xsl_match.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'manage/xsd_xsl_match.tpl.html', 16, false),)), $this); ?>

      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td>
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
                    <input type="hidden" name="cat" value="delete">
                    <tr>                
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>XSD Path</b></td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>XSD Element</b></td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;<b>XSD Input Type</b></td>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>Is Multiple?&nbsp;</b><br /><input type="button" value="All" class="shortcut"></td>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" nowrap>&nbsp;<b>Is Used?&nbsp;</b><br /><input type="button" value="All" class="shortcut"></td>
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
                      <td width="50%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]; ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;<?php echo $this->_tpl_vars['values'][$this->_sections['i']['index']]; ?>

                      </td>
                      <td width="20%" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
                        &nbsp;
                      </td>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
						<input type="checkbox" name="is_multiple[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
                      </td>
                      <td width="4" bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
" class="default">
						<input type="checkbox" name="is_used[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]; ?>
" <?php if ($this->_sections['i']['total'] == 0): ?>disabled<?php endif; ?>>
                      </td>
                    </tr>
                    <?php endfor; else: ?>
                    <tr>
                      <td colspan="4" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="center" class="default">
                        No doc type xsds could be found.
                      </td>
                    </tr>
                    <?php endif; ?>
                </form>
            </table>
          </td>
        </tr>
      </table>
