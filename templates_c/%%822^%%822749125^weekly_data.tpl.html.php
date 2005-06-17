<?php /* Smarty version 2.6.2, created on 2004-09-14 04:15:29
         compiled from reports/weekly_data.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'str_pad', 'reports/weekly_data.tpl.html', 6, false),array('modifier', 'count', 'reports/weekly_data.tpl.html', 17, false),)), $this); ?>
<?php echo $this->_tpl_vars['data']['user']['usr_full_name']; ?>
 <?php if ($this->_tpl_vars['report_type'] == 'weekly'): ?>Weekly <?php endif; ?>Report <?php echo $this->_tpl_vars['data']['start']; ?>
 - <?php echo $this->_tpl_vars['data']['end']; ?>


<?php echo $this->_tpl_vars['application_title']; ?>
 Issues:

<?php if (isset($this->_sections['issue'])) unset($this->_sections['issue']);
$this->_sections['issue']['name'] = 'issue';
$this->_sections['issue']['loop'] = is_array($_loop=$this->_tpl_vars['data']['issues']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['issue']['show'] = true;
$this->_sections['issue']['max'] = $this->_sections['issue']['loop'];
$this->_sections['issue']['step'] = 1;
$this->_sections['issue']['start'] = $this->_sections['issue']['step'] > 0 ? 0 : $this->_sections['issue']['loop']-1;
if ($this->_sections['issue']['show']) {
    $this->_sections['issue']['total'] = $this->_sections['issue']['loop'];
    if ($this->_sections['issue']['total'] == 0)
        $this->_sections['issue']['show'] = false;
} else
    $this->_sections['issue']['total'] = 0;
if ($this->_sections['issue']['show']):

            for ($this->_sections['issue']['index'] = $this->_sections['issue']['start'], $this->_sections['issue']['iteration'] = 1;
                 $this->_sections['issue']['iteration'] <= $this->_sections['issue']['total'];
                 $this->_sections['issue']['index'] += $this->_sections['issue']['step'], $this->_sections['issue']['iteration']++):
$this->_sections['issue']['rownum'] = $this->_sections['issue']['iteration'];
$this->_sections['issue']['index_prev'] = $this->_sections['issue']['index'] - $this->_sections['issue']['step'];
$this->_sections['issue']['index_next'] = $this->_sections['issue']['index'] + $this->_sections['issue']['step'];
$this->_sections['issue']['first']      = ($this->_sections['issue']['iteration'] == 1);
$this->_sections['issue']['last']       = ($this->_sections['issue']['iteration'] == $this->_sections['issue']['total']);
?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['data']['issues'][$this->_sections['issue']['index']]['iss_id'])) ? $this->_run_mod_handler('str_pad', true, $_tmp, 4, ' ', 'STR_PAD_LEFT') : str_pad($_tmp, 4, ' ', 'STR_PAD_LEFT')); ?>
 <?php echo $this->_tpl_vars['data']['issues'][$this->_sections['issue']['index']]['iss_summary']; ?>

<?php endfor; else: ?>
No issues touched this time period
<?php endif; ?>

New Issues Assigned:  <?php echo $this->_tpl_vars['data']['new_assigned_count']; ?>


<?php if (isset($this->_sections['status'])) unset($this->_sections['status']);
$this->_sections['status']['name'] = 'status';
$this->_sections['status']['loop'] = is_array($_loop=$this->_tpl_vars['data']['status_counts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['status']['show'] = true;
$this->_sections['status']['max'] = $this->_sections['status']['loop'];
$this->_sections['status']['step'] = 1;
$this->_sections['status']['start'] = $this->_sections['status']['step'] > 0 ? 0 : $this->_sections['status']['loop']-1;
if ($this->_sections['status']['show']) {
    $this->_sections['status']['total'] = $this->_sections['status']['loop'];
    if ($this->_sections['status']['total'] == 0)
        $this->_sections['status']['show'] = false;
} else
    $this->_sections['status']['total'] = 0;
if ($this->_sections['status']['show']):

            for ($this->_sections['status']['index'] = $this->_sections['status']['start'], $this->_sections['status']['iteration'] = 1;
                 $this->_sections['status']['iteration'] <= $this->_sections['status']['total'];
                 $this->_sections['status']['index'] += $this->_sections['status']['step'], $this->_sections['status']['iteration']++):
$this->_sections['status']['rownum'] = $this->_sections['status']['iteration'];
$this->_sections['status']['index_prev'] = $this->_sections['status']['index'] - $this->_sections['status']['step'];
$this->_sections['status']['index_next'] = $this->_sections['status']['index'] + $this->_sections['status']['step'];
$this->_sections['status']['first']      = ($this->_sections['status']['iteration'] == 1);
$this->_sections['status']['last']       = ($this->_sections['status']['iteration'] == $this->_sections['status']['total']);
?>
<?php $this->assign('title', ($this->_tpl_vars['data']['status_counts'][$this->_sections['status']['index']]['sta_title']).":"); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['title'])) ? $this->_run_mod_handler('str_pad', true, $_tmp, '22') : str_pad($_tmp, '22'));  echo $this->_tpl_vars['data']['status_counts'][$this->_sections['status']['index']]['total']; ?>

<?php endfor; endif; ?>
Total Issues:         <?php echo count($this->_tpl_vars['data']['issues']); ?>


Total Phone Calls:    <?php echo $this->_tpl_vars['data']['phone_count']; ?>

Eventum Emails:       <?php echo $this->_tpl_vars['data']['email_count']['associated']; ?>

Other Emails:         <?php echo $this->_tpl_vars['data']['email_count']['other']; ?>

Total Notes:          <?php echo $this->_tpl_vars['data']['note_count']; ?>


Phone Time Spent:     <?php if ($this->_tpl_vars['data']['time_tracking']['Telephone_Discussion']['formatted_time'] == ''): ?>00h 00m<?php else:  echo $this->_tpl_vars['data']['time_tracking']['Telephone_Discussion']['formatted_time'];  endif; ?>

Email Time Spent:     <?php if ($this->_tpl_vars['data']['time_tracking']['Email_Discussion']['formatted_time'] == ''): ?>00h 00m<?php else:  echo $this->_tpl_vars['data']['time_tracking']['Email_Discussion']['formatted_time'];  endif; ?>

Login Time Spent:     <?php if ($this->_tpl_vars['data']['time_tracking']['Login_Work']['formatted_time'] == ''): ?>00h 00m<?php else:  echo $this->_tpl_vars['data']['time_tracking']['Login_Work']['formatted_time'];  endif; ?>


Total Time Spent:     <?php echo $this->_tpl_vars['data']['total_time']; ?>
