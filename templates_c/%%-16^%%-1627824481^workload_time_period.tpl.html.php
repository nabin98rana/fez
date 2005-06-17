<?php /* Smarty version 2.6.2, created on 2004-09-14 04:15:38
         compiled from reports/workload_time_period.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'reports/workload_time_period.tpl.html', 36, false),array('modifier', 'round', 'reports/workload_time_period.tpl.html', 41, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['type'] == 'email'): ?>
    <h3 align="center">Email Workload by Time of day</h3>
    <p align="center" class="default" width="80%">Based on all issues recorded in Eventum since start to present.</p>
<?php else: ?>
    <h3 align="center">Workload by Time of day</h3>
    <p align="center" class="default" width="80%">Based on all issues recorded in Eventum since start to present. 
        Actions are any event that shows up in the history of an issue, such as a user or a developer updating an issue, uploading a file, sending an email, etc.
    </p>
<?php endif; ?>
<div align="center">
<img src="workload_time_period_graph.php?type=<?php echo $this->_tpl_vars['type']; ?>
">
</div>
<br />
<table width="400" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <th class="default_white">
            Time Period<br />
            (GMT)
          </th>
          <th class="default_white">
            Developer <?php if ($this->_tpl_vars['type'] == 'email'): ?>Emails<?php else: ?>Actions<?php endif; ?>
          </th>
          <th class="default_white">
            Customer <?php if ($this->_tpl_vars['type'] == 'email'): ?>Emails<?php else: ?>Actions<?php endif; ?>
          </th>
          <th class="default_white">
            Time Period<br />
            (<?php echo $this->_tpl_vars['user_tz']; ?>
)
          </th>
        </tr>
        <?php if (isset($this->_sections['workload'])) unset($this->_sections['workload']);
$this->_sections['workload']['name'] = 'workload';
$this->_sections['workload']['loop'] = is_array($_loop=$this->_tpl_vars['data']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['workload']['show'] = true;
$this->_sections['workload']['max'] = $this->_sections['workload']['loop'];
$this->_sections['workload']['step'] = 1;
$this->_sections['workload']['start'] = $this->_sections['workload']['step'] > 0 ? 0 : $this->_sections['workload']['loop']-1;
if ($this->_sections['workload']['show']) {
    $this->_sections['workload']['total'] = $this->_sections['workload']['loop'];
    if ($this->_sections['workload']['total'] == 0)
        $this->_sections['workload']['show'] = false;
} else
    $this->_sections['workload']['total'] = 0;
if ($this->_sections['workload']['show']):

            for ($this->_sections['workload']['index'] = $this->_sections['workload']['start'], $this->_sections['workload']['iteration'] = 1;
                 $this->_sections['workload']['iteration'] <= $this->_sections['workload']['total'];
                 $this->_sections['workload']['index'] += $this->_sections['workload']['step'], $this->_sections['workload']['iteration']++):
$this->_sections['workload']['rownum'] = $this->_sections['workload']['iteration'];
$this->_sections['workload']['index_prev'] = $this->_sections['workload']['index'] - $this->_sections['workload']['step'];
$this->_sections['workload']['index_next'] = $this->_sections['workload']['index'] + $this->_sections['workload']['step'];
$this->_sections['workload']['first']      = ($this->_sections['workload']['iteration'] == 1);
$this->_sections['workload']['last']       = ($this->_sections['workload']['iteration'] == $this->_sections['workload']['total']);
?>
        <?php echo smarty_function_cycle(array('values' => $this->_tpl_vars['cycle'],'assign' => 'row_color'), $this);?>

        <tr bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
          <td align="center" class="default"><?php echo $this->_tpl_vars['data'][$this->_sections['workload']['index']]['display_time_gmt']; ?>
</td>
          <td align="center" class="default" bgcolor="#ffcc00">
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['developer']['rank'] == 1): ?><b><?php endif; ?>
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['developer']['count'] != ''):  echo $this->_tpl_vars['data'][$this->_sections['workload']['index']]['developer']['count']; ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['data'][$this->_sections['workload']['index']]['developer']['percentage'])) ? $this->_run_mod_handler('round', true, $_tmp) : round($_tmp)); ?>
%)<?php endif; ?>
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['developer']['rank'] == 1): ?></b><?php endif; ?>
          </td>
          <td align="center" class="default" bgcolor="#99ccff">
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['customer']['rank'] == 1): ?><b><?php endif; ?>
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['customer']['count']):  echo $this->_tpl_vars['data'][$this->_sections['workload']['index']]['customer']['count']; ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['data'][$this->_sections['workload']['index']]['customer']['percentage'])) ? $this->_run_mod_handler('round', true, $_tmp) : round($_tmp)); ?>
%)<?php endif; ?>
              <?php if ($this->_tpl_vars['data'][$this->_sections['workload']['index']]['customer']['rank'] == 1): ?></b><?php endif; ?>
          </td>
          <td align="center" class="default"><?php echo $this->_tpl_vars['data'][$this->_sections['workload']['index']]['display_time_user']; ?>
</td>
        </tr>
        <?php endfor; endif; ?>
      </table>
    </td>
  </tr>
</table>
<br />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>