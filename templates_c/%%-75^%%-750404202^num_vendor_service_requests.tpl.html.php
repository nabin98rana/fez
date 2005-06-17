<?php /* Smarty version 2.6.2, created on 2004-09-14 14:11:30
         compiled from reports/num_vendor_service_requests.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<h4>Number of Vendor Service Requests for <?php echo $this->_tpl_vars['prj_name']; ?>
 Team</h4>
<?php $this->assign('tempEndTotal', '0'); ?>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['selected_year']; ?>
</b></td>
		<?php if (isset($this->_sections['month_heading'])) unset($this->_sections['month_heading']);
$this->_sections['month_heading']['name'] = 'month_heading';
$this->_sections['month_heading']['loop'] = is_array($_loop=$this->_tpl_vars['month_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['month_heading']['show'] = true;
$this->_sections['month_heading']['max'] = $this->_sections['month_heading']['loop'];
$this->_sections['month_heading']['step'] = 1;
$this->_sections['month_heading']['start'] = $this->_sections['month_heading']['step'] > 0 ? 0 : $this->_sections['month_heading']['loop']-1;
if ($this->_sections['month_heading']['show']) {
    $this->_sections['month_heading']['total'] = $this->_sections['month_heading']['loop'];
    if ($this->_sections['month_heading']['total'] == 0)
        $this->_sections['month_heading']['show'] = false;
} else
    $this->_sections['month_heading']['total'] = 0;
if ($this->_sections['month_heading']['show']):

            for ($this->_sections['month_heading']['index'] = $this->_sections['month_heading']['start'], $this->_sections['month_heading']['iteration'] = 1;
                 $this->_sections['month_heading']['iteration'] <= $this->_sections['month_heading']['total'];
                 $this->_sections['month_heading']['index'] += $this->_sections['month_heading']['step'], $this->_sections['month_heading']['iteration']++):
$this->_sections['month_heading']['rownum'] = $this->_sections['month_heading']['iteration'];
$this->_sections['month_heading']['index_prev'] = $this->_sections['month_heading']['index'] - $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['index_next'] = $this->_sections['month_heading']['index'] + $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['first']      = ($this->_sections['month_heading']['iteration'] == 1);
$this->_sections['month_heading']['last']       = ($this->_sections['month_heading']['iteration'] == $this->_sections['month_heading']['total']);
?>
          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['month_list'][$this->_sections['month_heading']['index']]; ?>
</b></td>
		<?php endfor; endif; ?>
		  <td align="center" class="default_white"><b>Total</b></td>
		</tr>			

<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['display_text']; ?>
</b></td>
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
				<?php $this->assign('tempTotal', '0'); ?>
				<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results'][0][0]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['results_loop']['show'] = true;
$this->_sections['results_loop']['max'] = $this->_sections['results_loop']['loop'];
$this->_sections['results_loop']['step'] = 1;
$this->_sections['results_loop']['start'] = $this->_sections['results_loop']['step'] > 0 ? 0 : $this->_sections['results_loop']['loop']-1;
if ($this->_sections['results_loop']['show']) {
    $this->_sections['results_loop']['total'] = $this->_sections['results_loop']['loop'];
    if ($this->_sections['results_loop']['total'] == 0)
        $this->_sections['results_loop']['show'] = false;
} else
    $this->_sections['results_loop']['total'] = 0;
if ($this->_sections['results_loop']['show']):

            for ($this->_sections['results_loop']['index'] = $this->_sections['results_loop']['start'], $this->_sections['results_loop']['iteration'] = 1;
                 $this->_sections['results_loop']['iteration'] <= $this->_sections['results_loop']['total'];
                 $this->_sections['results_loop']['index'] += $this->_sections['results_loop']['step'], $this->_sections['results_loop']['iteration']++):
$this->_sections['results_loop']['rownum'] = $this->_sections['results_loop']['iteration'];
$this->_sections['results_loop']['index_prev'] = $this->_sections['results_loop']['index'] - $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['index_next'] = $this->_sections['results_loop']['index'] + $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['first']      = ($this->_sections['results_loop']['iteration'] == 1);
$this->_sections['results_loop']['last']       = ($this->_sections['results_loop']['iteration'] == $this->_sections['results_loop']['total']);
?>
					<?php if ($this->_tpl_vars['results'][0][0][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text'] && $this->_tpl_vars['results'][0][0][$this->_sections['results_loop']['index']][1] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>					
						<?php $this->assign('tempTotal', $this->_tpl_vars['results'][0][0][$this->_sections['results_loop']['index']][2]); ?>
					<?php endif; ?>
				<?php endfor; endif; ?>
	          <td class="default_white" align="center"><?php echo $this->_tpl_vars['tempTotal']; ?>
</td>
	        <?php endfor; endif; ?>
			<?php $this->assign('tempTotal', '0'); ?>
			<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results'][0][1]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['results_loop']['show'] = true;
$this->_sections['results_loop']['max'] = $this->_sections['results_loop']['loop'];
$this->_sections['results_loop']['step'] = 1;
$this->_sections['results_loop']['start'] = $this->_sections['results_loop']['step'] > 0 ? 0 : $this->_sections['results_loop']['loop']-1;
if ($this->_sections['results_loop']['show']) {
    $this->_sections['results_loop']['total'] = $this->_sections['results_loop']['loop'];
    if ($this->_sections['results_loop']['total'] == 0)
        $this->_sections['results_loop']['show'] = false;
} else
    $this->_sections['results_loop']['total'] = 0;
if ($this->_sections['results_loop']['show']):

            for ($this->_sections['results_loop']['index'] = $this->_sections['results_loop']['start'], $this->_sections['results_loop']['iteration'] = 1;
                 $this->_sections['results_loop']['iteration'] <= $this->_sections['results_loop']['total'];
                 $this->_sections['results_loop']['index'] += $this->_sections['results_loop']['step'], $this->_sections['results_loop']['iteration']++):
$this->_sections['results_loop']['rownum'] = $this->_sections['results_loop']['iteration'];
$this->_sections['results_loop']['index_prev'] = $this->_sections['results_loop']['index'] - $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['index_next'] = $this->_sections['results_loop']['index'] + $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['first']      = ($this->_sections['results_loop']['iteration'] == 1);
$this->_sections['results_loop']['last']       = ($this->_sections['results_loop']['iteration'] == $this->_sections['results_loop']['total']);
?>
				<?php if ($this->_tpl_vars['results'][0][1][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text']): ?>
					<?php $this->assign('tempTotal', $this->_tpl_vars['results'][0][1][$this->_sections['results_loop']['index']][1]); ?>
				<?php endif; ?>
			<?php endfor; endif; ?>
			<?php $this->assign('tempEndTotal', $this->_tpl_vars['tempEndTotal']+$this->_tpl_vars['tempTotal']); ?>
            <td class="default_white" align="center"><?php echo $this->_tpl_vars['tempTotal']; ?>
</td>
        </tr>
<?php endforeach; unset($_from); endif; ?>
		<tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
		  <td align="center" class="default_white"><b>Total</b></td>
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
				<?php $this->assign('tempBottomTotal', '0'); ?>

				<?php if (count($_from = (array)$this->_tpl_vars['bottom_totals'])):
    foreach ($_from as $this->_tpl_vars['bottom_month'] => $this->_tpl_vars['bottom_month_total']):
?>
					<?php if ($this->_tpl_vars['bottom_month'] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>
						<?php $this->assign('tempBottomTotal', $this->_tpl_vars['bottom_month_total']); ?>
					<?php endif; ?>
		        <?php endforeach; unset($_from); endif; ?>	
	            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempBottomTotal']; ?>
</b></td>
	        <?php endfor; endif; ?>
            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempEndTotal']; ?>
</b></td>
		</tr>


      </table>

    </td>
  </tr>
  <tr>
	<td>&nbsp;
		
	</td>
  </tr>
  <tr>
	<td>
	  <?php $this->assign('tempEndTotal', '0'); ?>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['selected_year']; ?>
</b></td>
		<?php if (isset($this->_sections['month_heading'])) unset($this->_sections['month_heading']);
$this->_sections['month_heading']['name'] = 'month_heading';
$this->_sections['month_heading']['loop'] = is_array($_loop=$this->_tpl_vars['month_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['month_heading']['show'] = true;
$this->_sections['month_heading']['max'] = $this->_sections['month_heading']['loop'];
$this->_sections['month_heading']['step'] = 1;
$this->_sections['month_heading']['start'] = $this->_sections['month_heading']['step'] > 0 ? 0 : $this->_sections['month_heading']['loop']-1;
if ($this->_sections['month_heading']['show']) {
    $this->_sections['month_heading']['total'] = $this->_sections['month_heading']['loop'];
    if ($this->_sections['month_heading']['total'] == 0)
        $this->_sections['month_heading']['show'] = false;
} else
    $this->_sections['month_heading']['total'] = 0;
if ($this->_sections['month_heading']['show']):

            for ($this->_sections['month_heading']['index'] = $this->_sections['month_heading']['start'], $this->_sections['month_heading']['iteration'] = 1;
                 $this->_sections['month_heading']['iteration'] <= $this->_sections['month_heading']['total'];
                 $this->_sections['month_heading']['index'] += $this->_sections['month_heading']['step'], $this->_sections['month_heading']['iteration']++):
$this->_sections['month_heading']['rownum'] = $this->_sections['month_heading']['iteration'];
$this->_sections['month_heading']['index_prev'] = $this->_sections['month_heading']['index'] - $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['index_next'] = $this->_sections['month_heading']['index'] + $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['first']      = ($this->_sections['month_heading']['iteration'] == 1);
$this->_sections['month_heading']['last']       = ($this->_sections['month_heading']['iteration'] == $this->_sections['month_heading']['total']);
?>
          <td align="center" colspan="<?php echo $this->_tpl_vars['count_display_list']; ?>
" class="default_white"><b><?php echo $this->_tpl_vars['month_list'][$this->_sections['month_heading']['index']]; ?>
</b></td>
		<?php endfor; endif; ?>
		  <td align="center" class="default_white"><b>Total</b></td>
		</tr>				
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['selected_year']; ?>
</b></td>
		<?php if (isset($this->_sections['month_heading'])) unset($this->_sections['month_heading']);
$this->_sections['month_heading']['name'] = 'month_heading';
$this->_sections['month_heading']['loop'] = is_array($_loop=$this->_tpl_vars['month_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['month_heading']['show'] = true;
$this->_sections['month_heading']['max'] = $this->_sections['month_heading']['loop'];
$this->_sections['month_heading']['step'] = 1;
$this->_sections['month_heading']['start'] = $this->_sections['month_heading']['step'] > 0 ? 0 : $this->_sections['month_heading']['loop']-1;
if ($this->_sections['month_heading']['show']) {
    $this->_sections['month_heading']['total'] = $this->_sections['month_heading']['loop'];
    if ($this->_sections['month_heading']['total'] == 0)
        $this->_sections['month_heading']['show'] = false;
} else
    $this->_sections['month_heading']['total'] = 0;
if ($this->_sections['month_heading']['show']):

            for ($this->_sections['month_heading']['index'] = $this->_sections['month_heading']['start'], $this->_sections['month_heading']['iteration'] = 1;
                 $this->_sections['month_heading']['iteration'] <= $this->_sections['month_heading']['total'];
                 $this->_sections['month_heading']['index'] += $this->_sections['month_heading']['step'], $this->_sections['month_heading']['iteration']++):
$this->_sections['month_heading']['rownum'] = $this->_sections['month_heading']['iteration'];
$this->_sections['month_heading']['index_prev'] = $this->_sections['month_heading']['index'] - $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['index_next'] = $this->_sections['month_heading']['index'] + $this->_sections['month_heading']['step'];
$this->_sections['month_heading']['first']      = ($this->_sections['month_heading']['iteration'] == 1);
$this->_sections['month_heading']['last']       = ($this->_sections['month_heading']['iteration'] == $this->_sections['month_heading']['total']);
?>
			<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
	          <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['display_text']; ?>
</b></td>
			<?php endforeach; unset($_from); endif; ?>
		<?php endfor; endif; ?>
		  <td align="center" class="default_white"><b>Total</b></td>
		</tr>			
	<?php if (count($_from = (array)$this->_tpl_vars['display_list2'])):
    foreach ($_from as $this->_tpl_vars['display_num2'] => $this->_tpl_vars['display_text2']):
?>
			<tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
			  <td align="center" class="default_white"><b><?php echo $this->_tpl_vars['display_text2']; ?>
</b></td>
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
					<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
						<?php $this->assign('tempTotal', '0'); ?>
						<?php if (isset($this->_sections['results_subcat_loop'])) unset($this->_sections['results_subcat_loop']);
$this->_sections['results_subcat_loop']['name'] = 'results_subcat_loop';
$this->_sections['results_subcat_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results_subcat']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['results_subcat_loop']['show'] = true;
$this->_sections['results_subcat_loop']['max'] = $this->_sections['results_subcat_loop']['loop'];
$this->_sections['results_subcat_loop']['step'] = 1;
$this->_sections['results_subcat_loop']['start'] = $this->_sections['results_subcat_loop']['step'] > 0 ? 0 : $this->_sections['results_subcat_loop']['loop']-1;
if ($this->_sections['results_subcat_loop']['show']) {
    $this->_sections['results_subcat_loop']['total'] = $this->_sections['results_subcat_loop']['loop'];
    if ($this->_sections['results_subcat_loop']['total'] == 0)
        $this->_sections['results_subcat_loop']['show'] = false;
} else
    $this->_sections['results_subcat_loop']['total'] = 0;
if ($this->_sections['results_subcat_loop']['show']):

            for ($this->_sections['results_subcat_loop']['index'] = $this->_sections['results_subcat_loop']['start'], $this->_sections['results_subcat_loop']['iteration'] = 1;
                 $this->_sections['results_subcat_loop']['iteration'] <= $this->_sections['results_subcat_loop']['total'];
                 $this->_sections['results_subcat_loop']['index'] += $this->_sections['results_subcat_loop']['step'], $this->_sections['results_subcat_loop']['iteration']++):
$this->_sections['results_subcat_loop']['rownum'] = $this->_sections['results_subcat_loop']['iteration'];
$this->_sections['results_subcat_loop']['index_prev'] = $this->_sections['results_subcat_loop']['index'] - $this->_sections['results_subcat_loop']['step'];
$this->_sections['results_subcat_loop']['index_next'] = $this->_sections['results_subcat_loop']['index'] + $this->_sections['results_subcat_loop']['step'];
$this->_sections['results_subcat_loop']['first']      = ($this->_sections['results_subcat_loop']['iteration'] == 1);
$this->_sections['results_subcat_loop']['last']       = ($this->_sections['results_subcat_loop']['iteration'] == $this->_sections['results_subcat_loop']['total']);
?>
							<?php if ($this->_tpl_vars['results_subcat'][$this->_sections['results_subcat_loop']['index']][1] == $this->_tpl_vars['display_text2'] && $this->_tpl_vars['results_subcat'][$this->_sections['results_subcat_loop']['index']][0] == $this->_tpl_vars['display_text'] && $this->_tpl_vars['results_subcat'][$this->_sections['results_subcat_loop']['index']][2] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>					
								<?php $this->assign('tempTotal', $this->_tpl_vars['results_subcat'][$this->_sections['results_subcat_loop']['index']][3]); ?>
							<?php endif; ?>
						<?php endfor; endif; ?>
					  <td class="default_white" align="center"><?php echo $this->_tpl_vars['tempTotal']; ?>
</td>
					<?php endforeach; unset($_from); endif; ?>
				<?php endfor; endif; ?>
				<?php $this->assign('tempTotal', '0'); ?>
				<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results_subcat']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['results_loop']['show'] = true;
$this->_sections['results_loop']['max'] = $this->_sections['results_loop']['loop'];
$this->_sections['results_loop']['step'] = 1;
$this->_sections['results_loop']['start'] = $this->_sections['results_loop']['step'] > 0 ? 0 : $this->_sections['results_loop']['loop']-1;
if ($this->_sections['results_loop']['show']) {
    $this->_sections['results_loop']['total'] = $this->_sections['results_loop']['loop'];
    if ($this->_sections['results_loop']['total'] == 0)
        $this->_sections['results_loop']['show'] = false;
} else
    $this->_sections['results_loop']['total'] = 0;
if ($this->_sections['results_loop']['show']):

            for ($this->_sections['results_loop']['index'] = $this->_sections['results_loop']['start'], $this->_sections['results_loop']['iteration'] = 1;
                 $this->_sections['results_loop']['iteration'] <= $this->_sections['results_loop']['total'];
                 $this->_sections['results_loop']['index'] += $this->_sections['results_loop']['step'], $this->_sections['results_loop']['iteration']++):
$this->_sections['results_loop']['rownum'] = $this->_sections['results_loop']['iteration'];
$this->_sections['results_loop']['index_prev'] = $this->_sections['results_loop']['index'] - $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['index_next'] = $this->_sections['results_loop']['index'] + $this->_sections['results_loop']['step'];
$this->_sections['results_loop']['first']      = ($this->_sections['results_loop']['iteration'] == 1);
$this->_sections['results_loop']['last']       = ($this->_sections['results_loop']['iteration'] == $this->_sections['results_loop']['total']);
?>
					<?php if ($this->_tpl_vars['results_subcat'][$this->_sections['results_loop']['index']][1] == $this->_tpl_vars['display_text2']): ?>
						<?php $this->assign('tempTotal', $this->_tpl_vars['results_subcat'][$this->_sections['results_loop']['index']][3]); ?>
					<?php endif; ?>
				<?php endfor; endif; ?>
				<?php $this->assign('tempEndTotal', $this->_tpl_vars['tempEndTotal']+$this->_tpl_vars['tempTotal']); ?>
				<td class="default_white" align="center"><?php echo $this->_tpl_vars['tempTotal']; ?>
</td>
			</tr>
	<?php endforeach; unset($_from); endif; ?>
		<tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
		  <td align="center" class="default_white"><b>Total</b></td>
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
			  <?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
				<?php $this->assign('tempBottomTotal', '0'); ?>
				<?php if (isset($this->_sections['bottom_month_subcat_total'])) unset($this->_sections['bottom_month_subcat_total']);
$this->_sections['bottom_month_subcat_total']['name'] = 'bottom_month_subcat_total';
$this->_sections['bottom_month_subcat_total']['loop'] = is_array($_loop=$this->_tpl_vars['bottom_totals_subcat']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['bottom_month_subcat_total']['show'] = true;
$this->_sections['bottom_month_subcat_total']['max'] = $this->_sections['bottom_month_subcat_total']['loop'];
$this->_sections['bottom_month_subcat_total']['step'] = 1;
$this->_sections['bottom_month_subcat_total']['start'] = $this->_sections['bottom_month_subcat_total']['step'] > 0 ? 0 : $this->_sections['bottom_month_subcat_total']['loop']-1;
if ($this->_sections['bottom_month_subcat_total']['show']) {
    $this->_sections['bottom_month_subcat_total']['total'] = $this->_sections['bottom_month_subcat_total']['loop'];
    if ($this->_sections['bottom_month_subcat_total']['total'] == 0)
        $this->_sections['bottom_month_subcat_total']['show'] = false;
} else
    $this->_sections['bottom_month_subcat_total']['total'] = 0;
if ($this->_sections['bottom_month_subcat_total']['show']):

            for ($this->_sections['bottom_month_subcat_total']['index'] = $this->_sections['bottom_month_subcat_total']['start'], $this->_sections['bottom_month_subcat_total']['iteration'] = 1;
                 $this->_sections['bottom_month_subcat_total']['iteration'] <= $this->_sections['bottom_month_subcat_total']['total'];
                 $this->_sections['bottom_month_subcat_total']['index'] += $this->_sections['bottom_month_subcat_total']['step'], $this->_sections['bottom_month_subcat_total']['iteration']++):
$this->_sections['bottom_month_subcat_total']['rownum'] = $this->_sections['bottom_month_subcat_total']['iteration'];
$this->_sections['bottom_month_subcat_total']['index_prev'] = $this->_sections['bottom_month_subcat_total']['index'] - $this->_sections['bottom_month_subcat_total']['step'];
$this->_sections['bottom_month_subcat_total']['index_next'] = $this->_sections['bottom_month_subcat_total']['index'] + $this->_sections['bottom_month_subcat_total']['step'];
$this->_sections['bottom_month_subcat_total']['first']      = ($this->_sections['bottom_month_subcat_total']['iteration'] == 1);
$this->_sections['bottom_month_subcat_total']['last']       = ($this->_sections['bottom_month_subcat_total']['iteration'] == $this->_sections['bottom_month_subcat_total']['total']);
?>
					<?php if ($this->_tpl_vars['bottom_totals_subcat'][$this->_sections['bottom_month_subcat_total']['index']][1] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']] && $this->_tpl_vars['bottom_totals_subcat'][$this->_sections['bottom_month_subcat_total']['index']][0] == $this->_tpl_vars['display_text']): ?>
						<?php $this->assign('tempBottomTotal', $this->_tpl_vars['bottom_totals_subcat'][$this->_sections['bottom_month_subcat_total']['index']][2]); ?>
					<?php endif; ?>
		        <?php endfor; endif; ?>	
	            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempBottomTotal']; ?>
</b></td>
			  <?php endforeach; unset($_from); endif; ?>
	        <?php endfor; endif; ?>
            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempEndTotal']; ?>
</b></td>
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