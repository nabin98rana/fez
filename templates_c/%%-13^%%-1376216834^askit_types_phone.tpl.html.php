<?php /* Smarty version 2.6.2, created on 2005-01-28 16:13:24
         compiled from reports/askit_types_phone.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<h4>Types of Jobs Submitted Via Phone Logged for <?php echo $this->_tpl_vars['prj_name']; ?>
 Team</h4>
<?php $this->assign('tempEndTotal', '0'); ?>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white" ><b><?php echo $this->_tpl_vars['selected_year']; ?>
</b></td>

<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
	          <td align="center" class="default_white" nowrap><b><?php echo $this->_tpl_vars['display_text']; ?>
</b></td>
<?php endforeach; unset($_from); endif; ?>
		  <td align="center" class="default_white"><b>Total</b></td>
		</tr>
		<tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white" nowrap><b><?php echo $this->_tpl_vars['previous_year']; ?>
 total =</b></td>
<?php $this->assign('tempRowTotal', '0'); ?>
		<?php $this->assign('tempBottomTotal', '0'); ?>
		<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
				<?php $this->assign('tempTotal', '0'); ?>
				<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results_prev']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					<?php if ($this->_tpl_vars['results_prev'][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text']): ?>					
						<?php $this->assign('tempTotal', $this->_tpl_vars['tempTotal']+$this->_tpl_vars['results_prev'][$this->_sections['results_loop']['index']][2]); ?>
					<?php endif; ?>
				<?php endfor; endif; ?>
					<?php $this->assign('tempBottomTotal', $this->_tpl_vars['tempBottomTotal']+$this->_tpl_vars['tempTotal']); ?>
	            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempTotal']; ?>
</b></td>
        <?php endforeach; unset($_from); endif; ?>
            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempBottomTotal']; ?>
</b></td>
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
			  <td align="center" class="default_white" ><b><?php echo $this->_tpl_vars['month_list'][$this->_sections['month_list_num_loop']['index']]; ?>
</b></td>

<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>

				<?php $this->assign('tempTotal', '0'); ?>

				<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					<?php if ($this->_tpl_vars['results'][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text'] && $this->_tpl_vars['results'][$this->_sections['results_loop']['index']][1] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>					
						<?php $this->assign('tempTotal', $this->_tpl_vars['results'][$this->_sections['results_loop']['index']][2]); ?>
					<?php endif; ?>
				<?php endfor; endif; ?>


	          <td class="default_white" align="center"><?php echo $this->_tpl_vars['tempTotal']; ?>
</td>

			<?php $this->assign('tempTotal', '0'); ?>
			<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<?php if ($this->_tpl_vars['results'][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text'] && $this->_tpl_vars['results'][$this->_sections['results_loop']['index']][1] == $this->_tpl_vars['month_list_num'][$this->_sections['month_list_num_loop']['index']]): ?>
					<?php $this->assign('tempRowTotal', $this->_tpl_vars['tempRowTotal']+$this->_tpl_vars['results'][$this->_sections['results_loop']['index']][2]); ?>
				<?php endif; ?>
			<?php endfor; endif; ?>

<?php endforeach; unset($_from); endif; ?>

            <td class="default_white" align="center"><?php echo $this->_tpl_vars['tempRowTotal']; ?>
</td>

			<?php $this->assign('tempEndTotal', $this->_tpl_vars['tempEndTotal']+$this->_tpl_vars['tempRowTotal']); ?>
			<?php $this->assign('tempRowTotal', '0'); ?>
        </tr>
	        <?php endfor; endif; ?>
		<tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">

		  <td align="center" class="default_white"><b>Total</b></td>
		<?php $this->assign('tempBottomTotal', '0'); ?>
		<?php if (count($_from = (array)$this->_tpl_vars['display_list'])):
    foreach ($_from as $this->_tpl_vars['display_num'] => $this->_tpl_vars['display_text']):
?>
				<?php $this->assign('tempTotal', '0'); ?>
				<?php if (isset($this->_sections['results_loop'])) unset($this->_sections['results_loop']);
$this->_sections['results_loop']['name'] = 'results_loop';
$this->_sections['results_loop']['loop'] = is_array($_loop=$this->_tpl_vars['results']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					<?php if ($this->_tpl_vars['results'][$this->_sections['results_loop']['index']][0] == $this->_tpl_vars['display_text']): ?>					
						<?php $this->assign('tempTotal', $this->_tpl_vars['tempTotal']+$this->_tpl_vars['results'][$this->_sections['results_loop']['index']][2]); ?>
					<?php endif; ?>
				<?php endfor; endif; ?>
					<?php $this->assign('tempBottomTotal', $this->_tpl_vars['tempBottomTotal']+$this->_tpl_vars['tempTotal']); ?>
	            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempTotal']; ?>
</b></td>
        <?php endforeach; unset($_from); endif; ?>
            <td class="default_white" align="center"><b><?php echo $this->_tpl_vars['tempBottomTotal']; ?>
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