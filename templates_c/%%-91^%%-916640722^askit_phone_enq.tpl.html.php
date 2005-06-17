<?php /* Smarty version 2.6.2, created on 2005-02-07 17:50:24
         compiled from reports/askit_phone_enq.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<h4>Phone Enquiries Receieved for <?php echo $this->_tpl_vars['prj_name']; ?>
 Team <?php echo $this->_tpl_vars['selected_year']; ?>
</h4>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">

        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white">&nbsp;</td>
		<?php if (isset($this->_sections['year_list_loop'])) unset($this->_sections['year_list_loop']);
$this->_sections['year_list_loop']['name'] = 'year_list_loop';
$this->_sections['year_list_loop']['loop'] = is_array($_loop=$this->_tpl_vars['year_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_list_loop']['show'] = true;
$this->_sections['year_list_loop']['max'] = $this->_sections['year_list_loop']['loop'];
$this->_sections['year_list_loop']['step'] = 1;
$this->_sections['year_list_loop']['start'] = $this->_sections['year_list_loop']['step'] > 0 ? 0 : $this->_sections['year_list_loop']['loop']-1;
if ($this->_sections['year_list_loop']['show']) {
    $this->_sections['year_list_loop']['total'] = $this->_sections['year_list_loop']['loop'];
    if ($this->_sections['year_list_loop']['total'] == 0)
        $this->_sections['year_list_loop']['show'] = false;
} else
    $this->_sections['year_list_loop']['total'] = 0;
if ($this->_sections['year_list_loop']['show']):

            for ($this->_sections['year_list_loop']['index'] = $this->_sections['year_list_loop']['start'], $this->_sections['year_list_loop']['iteration'] = 1;
                 $this->_sections['year_list_loop']['iteration'] <= $this->_sections['year_list_loop']['total'];
                 $this->_sections['year_list_loop']['index'] += $this->_sections['year_list_loop']['step'], $this->_sections['year_list_loop']['iteration']++):
$this->_sections['year_list_loop']['rownum'] = $this->_sections['year_list_loop']['iteration'];
$this->_sections['year_list_loop']['index_prev'] = $this->_sections['year_list_loop']['index'] - $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['index_next'] = $this->_sections['year_list_loop']['index'] + $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['first']      = ($this->_sections['year_list_loop']['iteration'] == 1);
$this->_sections['year_list_loop']['last']       = ($this->_sections['year_list_loop']['iteration'] == $this->_sections['year_list_loop']['total']);
?>
				  <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['year_list'][$this->_sections['year_list_loop']['index']]; ?>
</b></td>
		<?php endfor; endif; ?>
		</tr>			
 <?php if (isset($this->_sections['month_list_num_loop'])) unset($this->_sections['month_list_num_loop']);
$this->_sections['month_list_num_loop']['name'] = 'month_list_num_loop';
$this->_sections['month_list_num_loop']['loop'] = is_array($_loop=$this->_tpl_vars['month_list_num']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['month_list_num_loop']['show'] = true;
$this->_sections['month_list_num_loop']['max'] = $this->_sections['month_list_num_loop']['loop'];
$this->_sections['month_list_num_loop']['step'] = 1;
$this->_sections['month_list_num_loop']['start'] = $this->_sections['month_list_num_loop']['step'] > 0 ? 0 : $this->_sections['month_list_num_loop']['loop']-1;
if ($this->_sections['month_list_num_loop']['show']) {
    $this->_sections['month_list_num_loop']['total'] = $this->_sections['month_list_num_loop']['loop'];
    if ($this->_sections['month_list_num_loop']['total'] == 0)
        $this->_sections['month_list_num_loop']['show'] = false;
} else
    $this->_sections['month_list_num_loop']['total'] = 0;
if ($this->_sections['month_list_num_loop']['show']):

            for ($this->_sections['month_list_num_loop']['index'] = $this->_sections['month_list_num_loop']['start'], $this->_sections['month_list_num_loop']['iteration'] = 1;
                 $this->_sections['month_list_num_loop']['iteration'] <= $this->_sections['month_list_num_loop']['total'];
                 $this->_sections['month_list_num_loop']['index'] += $this->_sections['month_list_num_loop']['step'], $this->_sections['month_list_num_loop']['iteration']++):
$this->_sections['month_list_num_loop']['rownum'] = $this->_sections['month_list_num_loop']['iteration'];
$this->_sections['month_list_num_loop']['index_prev'] = $this->_sections['month_list_num_loop']['index'] - $this->_sections['month_list_num_loop']['step'];
$this->_sections['month_list_num_loop']['index_next'] = $this->_sections['month_list_num_loop']['index'] + $this->_sections['month_list_num_loop']['step'];
$this->_sections['month_list_num_loop']['first']      = ($this->_sections['month_list_num_loop']['iteration'] == 1);
$this->_sections['month_list_num_loop']['last']       = ($this->_sections['month_list_num_loop']['iteration'] == $this->_sections['month_list_num_loop']['total']);
?>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
		<?php $this->assign('tempMonth', $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]-1); ?>
          <td align="center" class="default_white"><?php echo $this->_tpl_vars['month_list'][$this->_tpl_vars['tempMonth']]; ?>
</td>

	<?php if (isset($this->_sections['year_heading'])) unset($this->_sections['year_heading']);
$this->_sections['year_heading']['name'] = 'year_heading';
$this->_sections['year_heading']['loop'] = is_array($_loop=$this->_tpl_vars['year_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_heading']['show'] = true;
$this->_sections['year_heading']['max'] = $this->_sections['year_heading']['loop'];
$this->_sections['year_heading']['step'] = 1;
$this->_sections['year_heading']['start'] = $this->_sections['year_heading']['step'] > 0 ? 0 : $this->_sections['year_heading']['loop']-1;
if ($this->_sections['year_heading']['show']) {
    $this->_sections['year_heading']['total'] = $this->_sections['year_heading']['loop'];
    if ($this->_sections['year_heading']['total'] == 0)
        $this->_sections['year_heading']['show'] = false;
} else
    $this->_sections['year_heading']['total'] = 0;
if ($this->_sections['year_heading']['show']):

            for ($this->_sections['year_heading']['index'] = $this->_sections['year_heading']['start'], $this->_sections['year_heading']['iteration'] = 1;
                 $this->_sections['year_heading']['iteration'] <= $this->_sections['year_heading']['total'];
                 $this->_sections['year_heading']['index'] += $this->_sections['year_heading']['step'], $this->_sections['year_heading']['iteration']++):
$this->_sections['year_heading']['rownum'] = $this->_sections['year_heading']['iteration'];
$this->_sections['year_heading']['index_prev'] = $this->_sections['year_heading']['index'] - $this->_sections['year_heading']['step'];
$this->_sections['year_heading']['index_next'] = $this->_sections['year_heading']['index'] + $this->_sections['year_heading']['step'];
$this->_sections['year_heading']['first']      = ($this->_sections['year_heading']['iteration'] == 1);
$this->_sections['year_heading']['last']       = ($this->_sections['year_heading']['iteration'] == $this->_sections['year_heading']['total']);
?>

		<?php $this->assign('tempTotal', '0'); ?>

			<?php if (isset($this->_sections['month_loop'])) unset($this->_sections['month_loop']);
$this->_sections['month_loop']['name'] = 'month_loop';
$this->_sections['month_loop']['loop'] = is_array($_loop=$this->_tpl_vars['month']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['month_loop']['show'] = true;
$this->_sections['month_loop']['max'] = $this->_sections['month_loop']['loop'];
$this->_sections['month_loop']['step'] = 1;
$this->_sections['month_loop']['start'] = $this->_sections['month_loop']['step'] > 0 ? 0 : $this->_sections['month_loop']['loop']-1;
if ($this->_sections['month_loop']['show']) {
    $this->_sections['month_loop']['total'] = $this->_sections['month_loop']['loop'];
    if ($this->_sections['month_loop']['total'] == 0)
        $this->_sections['month_loop']['show'] = false;
} else
    $this->_sections['month_loop']['total'] = 0;
if ($this->_sections['month_loop']['show']):

            for ($this->_sections['month_loop']['index'] = $this->_sections['month_loop']['start'], $this->_sections['month_loop']['iteration'] = 1;
                 $this->_sections['month_loop']['iteration'] <= $this->_sections['month_loop']['total'];
                 $this->_sections['month_loop']['index'] += $this->_sections['month_loop']['step'], $this->_sections['month_loop']['iteration']++):
$this->_sections['month_loop']['rownum'] = $this->_sections['month_loop']['iteration'];
$this->_sections['month_loop']['index_prev'] = $this->_sections['month_loop']['index'] - $this->_sections['month_loop']['step'];
$this->_sections['month_loop']['index_next'] = $this->_sections['month_loop']['index'] + $this->_sections['month_loop']['step'];
$this->_sections['month_loop']['first']      = ($this->_sections['month_loop']['iteration'] == 1);
$this->_sections['month_loop']['last']       = ($this->_sections['month_loop']['iteration'] == $this->_sections['month_loop']['total']);
?>
				<?php if ($this->_tpl_vars['month'][$this->_sections['month_loop']['index']][1] == $this->_tpl_vars['year_list'][$this->_sections['year_heading']['index']] && $this->_tpl_vars['month'][$this->_sections['month_loop']['index']][0] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>
					<?php $this->assign('tempTotal', $this->_tpl_vars['month'][$this->_sections['month_loop']['index']][2]); ?>
				<?php endif; ?>
			<?php endfor; endif; ?>	
	 		  <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempTotal']; ?>
</b></td>
		<?php endfor; endif; ?>
        </tr>
	<?php endfor; endif; ?>
		 <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
			  <td align="center" class="default_white">Total</td>
		<?php if (isset($this->_sections['year_list_loop'])) unset($this->_sections['year_list_loop']);
$this->_sections['year_list_loop']['name'] = 'year_list_loop';
$this->_sections['year_list_loop']['loop'] = is_array($_loop=$this->_tpl_vars['year_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_list_loop']['show'] = true;
$this->_sections['year_list_loop']['max'] = $this->_sections['year_list_loop']['loop'];
$this->_sections['year_list_loop']['step'] = 1;
$this->_sections['year_list_loop']['start'] = $this->_sections['year_list_loop']['step'] > 0 ? 0 : $this->_sections['year_list_loop']['loop']-1;
if ($this->_sections['year_list_loop']['show']) {
    $this->_sections['year_list_loop']['total'] = $this->_sections['year_list_loop']['loop'];
    if ($this->_sections['year_list_loop']['total'] == 0)
        $this->_sections['year_list_loop']['show'] = false;
} else
    $this->_sections['year_list_loop']['total'] = 0;
if ($this->_sections['year_list_loop']['show']):

            for ($this->_sections['year_list_loop']['index'] = $this->_sections['year_list_loop']['start'], $this->_sections['year_list_loop']['iteration'] = 1;
                 $this->_sections['year_list_loop']['iteration'] <= $this->_sections['year_list_loop']['total'];
                 $this->_sections['year_list_loop']['index'] += $this->_sections['year_list_loop']['step'], $this->_sections['year_list_loop']['iteration']++):
$this->_sections['year_list_loop']['rownum'] = $this->_sections['year_list_loop']['iteration'];
$this->_sections['year_list_loop']['index_prev'] = $this->_sections['year_list_loop']['index'] - $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['index_next'] = $this->_sections['year_list_loop']['index'] + $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['first']      = ($this->_sections['year_list_loop']['iteration'] == 1);
$this->_sections['year_list_loop']['last']       = ($this->_sections['year_list_loop']['iteration'] == $this->_sections['year_list_loop']['total']);
?>
			<?php $this->assign('tempEndTotal', '0'); ?>
				<?php if (isset($this->_sections['year_loop'])) unset($this->_sections['year_loop']);
$this->_sections['year_loop']['name'] = 'year_loop';
$this->_sections['year_loop']['loop'] = is_array($_loop=$this->_tpl_vars['year']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_loop']['show'] = true;
$this->_sections['year_loop']['max'] = $this->_sections['year_loop']['loop'];
$this->_sections['year_loop']['step'] = 1;
$this->_sections['year_loop']['start'] = $this->_sections['year_loop']['step'] > 0 ? 0 : $this->_sections['year_loop']['loop']-1;
if ($this->_sections['year_loop']['show']) {
    $this->_sections['year_loop']['total'] = $this->_sections['year_loop']['loop'];
    if ($this->_sections['year_loop']['total'] == 0)
        $this->_sections['year_loop']['show'] = false;
} else
    $this->_sections['year_loop']['total'] = 0;
if ($this->_sections['year_loop']['show']):

            for ($this->_sections['year_loop']['index'] = $this->_sections['year_loop']['start'], $this->_sections['year_loop']['iteration'] = 1;
                 $this->_sections['year_loop']['iteration'] <= $this->_sections['year_loop']['total'];
                 $this->_sections['year_loop']['index'] += $this->_sections['year_loop']['step'], $this->_sections['year_loop']['iteration']++):
$this->_sections['year_loop']['rownum'] = $this->_sections['year_loop']['iteration'];
$this->_sections['year_loop']['index_prev'] = $this->_sections['year_loop']['index'] - $this->_sections['year_loop']['step'];
$this->_sections['year_loop']['index_next'] = $this->_sections['year_loop']['index'] + $this->_sections['year_loop']['step'];
$this->_sections['year_loop']['first']      = ($this->_sections['year_loop']['iteration'] == 1);
$this->_sections['year_loop']['last']       = ($this->_sections['year_loop']['iteration'] == $this->_sections['year_loop']['total']);
?>
				<?php if ($this->_tpl_vars['year'][$this->_sections['year_loop']['index']][0] == $this->_tpl_vars['year_list'][$this->_sections['year_list_loop']['index']]): ?>
					<?php $this->assign('tempEndTotal', $this->_tpl_vars['year'][$this->_sections['year_loop']['index']][1]); ?>
				<?php endif; ?>
			    <?php endfor; endif; ?>
	 		  <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempEndTotal']; ?>
</b></td>
		<?php endfor; endif; ?>

        </tr>
		 <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
			  <td align="center" class="default_white">Avg Per Day</td>
		<?php if (isset($this->_sections['year_list_loop'])) unset($this->_sections['year_list_loop']);
$this->_sections['year_list_loop']['name'] = 'year_list_loop';
$this->_sections['year_list_loop']['loop'] = is_array($_loop=$this->_tpl_vars['year_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_list_loop']['show'] = true;
$this->_sections['year_list_loop']['max'] = $this->_sections['year_list_loop']['loop'];
$this->_sections['year_list_loop']['step'] = 1;
$this->_sections['year_list_loop']['start'] = $this->_sections['year_list_loop']['step'] > 0 ? 0 : $this->_sections['year_list_loop']['loop']-1;
if ($this->_sections['year_list_loop']['show']) {
    $this->_sections['year_list_loop']['total'] = $this->_sections['year_list_loop']['loop'];
    if ($this->_sections['year_list_loop']['total'] == 0)
        $this->_sections['year_list_loop']['show'] = false;
} else
    $this->_sections['year_list_loop']['total'] = 0;
if ($this->_sections['year_list_loop']['show']):

            for ($this->_sections['year_list_loop']['index'] = $this->_sections['year_list_loop']['start'], $this->_sections['year_list_loop']['iteration'] = 1;
                 $this->_sections['year_list_loop']['iteration'] <= $this->_sections['year_list_loop']['total'];
                 $this->_sections['year_list_loop']['index'] += $this->_sections['year_list_loop']['step'], $this->_sections['year_list_loop']['iteration']++):
$this->_sections['year_list_loop']['rownum'] = $this->_sections['year_list_loop']['iteration'];
$this->_sections['year_list_loop']['index_prev'] = $this->_sections['year_list_loop']['index'] - $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['index_next'] = $this->_sections['year_list_loop']['index'] + $this->_sections['year_list_loop']['step'];
$this->_sections['year_list_loop']['first']      = ($this->_sections['year_list_loop']['iteration'] == 1);
$this->_sections['year_list_loop']['last']       = ($this->_sections['year_list_loop']['iteration'] == $this->_sections['year_list_loop']['total']);
?>
			<?php $this->assign('tempEndTotal', '0'); ?>
				<?php if (isset($this->_sections['year_loop'])) unset($this->_sections['year_loop']);
$this->_sections['year_loop']['name'] = 'year_loop';
$this->_sections['year_loop']['loop'] = is_array($_loop=$this->_tpl_vars['year']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['year_loop']['show'] = true;
$this->_sections['year_loop']['max'] = $this->_sections['year_loop']['loop'];
$this->_sections['year_loop']['step'] = 1;
$this->_sections['year_loop']['start'] = $this->_sections['year_loop']['step'] > 0 ? 0 : $this->_sections['year_loop']['loop']-1;
if ($this->_sections['year_loop']['show']) {
    $this->_sections['year_loop']['total'] = $this->_sections['year_loop']['loop'];
    if ($this->_sections['year_loop']['total'] == 0)
        $this->_sections['year_loop']['show'] = false;
} else
    $this->_sections['year_loop']['total'] = 0;
if ($this->_sections['year_loop']['show']):

            for ($this->_sections['year_loop']['index'] = $this->_sections['year_loop']['start'], $this->_sections['year_loop']['iteration'] = 1;
                 $this->_sections['year_loop']['iteration'] <= $this->_sections['year_loop']['total'];
                 $this->_sections['year_loop']['index'] += $this->_sections['year_loop']['step'], $this->_sections['year_loop']['iteration']++):
$this->_sections['year_loop']['rownum'] = $this->_sections['year_loop']['iteration'];
$this->_sections['year_loop']['index_prev'] = $this->_sections['year_loop']['index'] - $this->_sections['year_loop']['step'];
$this->_sections['year_loop']['index_next'] = $this->_sections['year_loop']['index'] + $this->_sections['year_loop']['step'];
$this->_sections['year_loop']['first']      = ($this->_sections['year_loop']['iteration'] == 1);
$this->_sections['year_loop']['last']       = ($this->_sections['year_loop']['iteration'] == $this->_sections['year_loop']['total']);
?>
				<?php if ($this->_tpl_vars['year'][$this->_sections['year_loop']['index']][0] == $this->_tpl_vars['year_list'][$this->_sections['year_list_loop']['index']]): ?>
					<?php $this->assign('tempEndTotal', $this->_tpl_vars['year'][$this->_sections['year_loop']['index']][2]); ?>
				<?php endif; ?>
			    <?php endfor; endif; ?>
	 		  <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempEndTotal']; ?>
</b></td>
		<?php endfor; endif; ?>


        </tr>

		 <tr>
			  <td align="center" class="default_white" colspan="2">&nbsp;</td>
        </tr>




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