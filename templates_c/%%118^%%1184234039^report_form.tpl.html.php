<?php /* Smarty version 2.6.2, created on 2005-06-02 16:12:22
         compiled from report_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'report_form.tpl.html', 88, false),array('modifier', 'escape', 'report_form.tpl.html', 95, false),array('function', 'fetch', 'report_form.tpl.html', 90, false),array('function', 'html_options', 'report_form.tpl.html', 169, false),)), $this); ?>

<?php if ($this->_tpl_vars['new_record_id'] != "" && $_POST['report_stays'] != 'yes'): ?>
<table width="500" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <b>Thank you, the new record was created successfully. Please choose 
            from one of the options below:</b>
            <ul>
              <li><a href="view.php?id=<?php echo $this->_tpl_vars['new_record_id']; ?>
" class="link">Open the Record Details Page</a></li>
              <li><a href="list.php" class="link">Open the Record Listing Page</a></li>
              <?php if ($this->_tpl_vars['app_setup']['support_email'] == 'enabled' && $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer']): ?>
              <li><a href="emails.php" class="link">Open the Emails Listing Page</a></li>
              <?php endif; ?>
              <li><a href="new.php" class="link">Report a New Record</a></li>
            </ul>
            <b>Otherwise, you will be automatically redirected to the Record Details Page in 5 seconds.</b>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php echo '
<script language="JavaScript">
<!--
setTimeout(\'openDetailPage()\', 5000);
function openDetailPage()
{
'; ?>

    window.location.href = 'view.php?pid=<?php echo $this->_tpl_vars['new_record_id']; ?>
';
<?php echo '
}
//-->
</script>
'; ?>

<?php endif; ?>
<table width="99%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="report_form" action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post" enctype="multipart/form-data">
<input type="hidden" name="cat" value="report">
<input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
<input type="hidden" name="collection_pid" value="<?php echo $this->_tpl_vars['collection_pid']; ?>
">
<input type="hidden" name="community_pid" value="<?php echo $this->_tpl_vars['community_pid']; ?>
">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td class="default">
            <b><?php echo $this->_tpl_vars['form_title']; ?>
</b>
          </td>
          <td align="right" class="default">
            (Current Team: <?php echo $this->_tpl_vars['current_collection_name']; ?>
)
          </td>
        </tr>
        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_fields']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsdmf_id_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != ''): ?>
		  <?php if (( ( ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'static' ) && ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_show_in_view'] == 1 ) ) || ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'static' ) )): ?>
			  <?php if (( ( ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'xsd_loop_subelement' ) && ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_show_in_view'] == 1 ) ) || ( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_loop_subelement' ) )): ?>

        <tr><td colspan="2" class="default">
	<table id="choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
" border="0" cellpadding="2" cellspacing="0" width="100%">
            <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'file_input'): ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] == 1): ?>
					<?php if (isset($this->_sections['z'])) unset($this->_sections['z']);
$this->_sections['z']['name'] = 'z';
$this->_sections['z']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['z']['show'] = true;
$this->_sections['z']['max'] = $this->_sections['z']['loop'];
$this->_sections['z']['step'] = 1;
$this->_sections['z']['start'] = $this->_sections['z']['step'] > 0 ? 0 : $this->_sections['z']['loop']-1;
if ($this->_sections['z']['show']) {
    $this->_sections['z']['total'] = $this->_sections['z']['loop'];
    if ($this->_sections['z']['total'] == 0)
        $this->_sections['z']['show'] = false;
} else
    $this->_sections['z']['total'] = 0;
if ($this->_sections['z']['show']):

            for ($this->_sections['z']['index'] = $this->_sections['z']['start'], $this->_sections['z']['iteration'] = 1;
                 $this->_sections['z']['iteration'] <= $this->_sections['z']['total'];
                 $this->_sections['z']['index'] += $this->_sections['z']['step'], $this->_sections['z']['iteration']++):
$this->_sections['z']['rownum'] = $this->_sections['z']['iteration'];
$this->_sections['z']['index_prev'] = $this->_sections['z']['index'] - $this->_sections['z']['step'];
$this->_sections['z']['index_next'] = $this->_sections['z']['index'] + $this->_sections['z']['step'];
$this->_sections['z']['first']      = ($this->_sections['z']['iteration'] == 1);
$this->_sections['z']['last']       = ($this->_sections['z']['iteration'] == $this->_sections['z']['total']);
?>
						<?php $this->assign('loop_num', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array'][$this->_sections['z']['index']]); ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['loop_num'] != 1): ?>style="display:none"<?php endif; ?>>
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 <?php echo $this->_tpl_vars['loop_num']; ?>
</b><br /><?php if ($this->_tpl_vars['loop_num'] == 1): ?><i>(More <?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 input boxes will appears as they are used up)</i><?php endif; ?>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<input class="default" type="file" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
][]" onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');" maxlength="255" size="50">
						 </td>
						</tr>
					<?php endfor; endif; ?>
				<?php else: ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<input class="default" type="file" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]"  maxlength="255" size="50">
						 </td>
						</tr>
				<?php endif; ?>
			<?php elseif (( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'static' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_show_in_view'] == 1 && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_static_text'] != '' )): ?>
				<?php $this->assign('image_name', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['espace_root_dir'])) ? $this->_run_mod_handler('cat', true, $_tmp, "images/") : smarty_modifier_cat($_tmp, "images/")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']))); ?> 
				<?php $this->assign('image_file_string', ""); ?>
				<?php echo smarty_function_fetch(array('file' => $this->_tpl_vars['image_name'],'assign' => 'image_file_string'), $this);?>
 
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" colspan="2" nowrap>
							<?php if ($this->_tpl_vars['image_file_string'] != ""): ?><img align="absmiddle" src="/images/<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']; ?>
"><?php endif; ?><b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_static_text']; ?>
</b>
							<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'] != ""): ?>
								&nbsp;<i><?php echo ((is_array($_tmp=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
							<?php endif; ?>
						  </td>
						</tr>
			<?php elseif (( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'xsd_loop_subelement' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_show_in_view'] == 1 )): ?>
				<?php $this->assign('image_name', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['espace_root_dir'])) ? $this->_run_mod_handler('cat', true, $_tmp, "images/") : smarty_modifier_cat($_tmp, "images/")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']))); ?> 
				<?php $this->assign('image_file_string', ""); ?>
				<?php echo smarty_function_fetch(array('file' => $this->_tpl_vars['image_name'],'assign' => 'image_file_string'), $this);?>
 
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white" colspan="2" nowrap>
							<?php if ($this->_tpl_vars['image_file_string'] != ""): ?><img align="absmiddle" src="/images/<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_image_location']; ?>
"><?php endif; ?><b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
							<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'] != ""): ?>
								&nbsp;<i><?php echo ((is_array($_tmp=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
							<?php endif; ?>
						  </td>
						</tr>

            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'text'): ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] == 1): ?>
					<?php if (isset($this->_sections['z'])) unset($this->_sections['z']);
$this->_sections['z']['name'] = 'z';
$this->_sections['z']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['z']['show'] = true;
$this->_sections['z']['max'] = $this->_sections['z']['loop'];
$this->_sections['z']['step'] = 1;
$this->_sections['z']['start'] = $this->_sections['z']['step'] > 0 ? 0 : $this->_sections['z']['loop']-1;
if ($this->_sections['z']['show']) {
    $this->_sections['z']['total'] = $this->_sections['z']['loop'];
    if ($this->_sections['z']['total'] == 0)
        $this->_sections['z']['show'] = false;
} else
    $this->_sections['z']['total'] = 0;
if ($this->_sections['z']['show']):

            for ($this->_sections['z']['index'] = $this->_sections['z']['start'], $this->_sections['z']['iteration'] = 1;
                 $this->_sections['z']['iteration'] <= $this->_sections['z']['total'];
                 $this->_sections['z']['index'] += $this->_sections['z']['step'], $this->_sections['z']['iteration']++):
$this->_sections['z']['rownum'] = $this->_sections['z']['iteration'];
$this->_sections['z']['index_prev'] = $this->_sections['z']['index'] - $this->_sections['z']['step'];
$this->_sections['z']['index_next'] = $this->_sections['z']['index'] + $this->_sections['z']['step'];
$this->_sections['z']['first']      = ($this->_sections['z']['iteration'] == 1);
$this->_sections['z']['last']       = ($this->_sections['z']['iteration'] == $this->_sections['z']['total']);
?>
						<?php $this->assign('loop_num', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array'][$this->_sections['z']['index']]); ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['loop_num'] != 1): ?>style="display:none"<?php endif; ?>>
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 <?php echo $this->_tpl_vars['loop_num']; ?>
</b><br /><?php if ($this->_tpl_vars['loop_num'] == 1): ?><i>(More <?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 input boxes will appears as they are used up)</i><?php endif; ?>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<input class="default" type="text" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
][]" onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');" maxlength="255" size="50">
						 </td>
						</tr>
					<?php endfor; endif; ?>
				<?php else: ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<input class="default" type="text" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]"  maxlength="255" size="50">
						 </td>
						</tr>
				<?php endif; ?>
            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'textarea'): ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] == 1): ?>
					<?php if (isset($this->_sections['z'])) unset($this->_sections['z']);
$this->_sections['z']['name'] = 'z';
$this->_sections['z']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['z']['show'] = true;
$this->_sections['z']['max'] = $this->_sections['z']['loop'];
$this->_sections['z']['step'] = 1;
$this->_sections['z']['start'] = $this->_sections['z']['step'] > 0 ? 0 : $this->_sections['z']['loop']-1;
if ($this->_sections['z']['show']) {
    $this->_sections['z']['total'] = $this->_sections['z']['loop'];
    if ($this->_sections['z']['total'] == 0)
        $this->_sections['z']['show'] = false;
} else
    $this->_sections['z']['total'] = 0;
if ($this->_sections['z']['show']):

            for ($this->_sections['z']['index'] = $this->_sections['z']['start'], $this->_sections['z']['iteration'] = 1;
                 $this->_sections['z']['iteration'] <= $this->_sections['z']['total'];
                 $this->_sections['z']['index'] += $this->_sections['z']['step'], $this->_sections['z']['iteration']++):
$this->_sections['z']['rownum'] = $this->_sections['z']['iteration'];
$this->_sections['z']['index_prev'] = $this->_sections['z']['index'] - $this->_sections['z']['step'];
$this->_sections['z']['index_next'] = $this->_sections['z']['index'] + $this->_sections['z']['step'];
$this->_sections['z']['first']      = ($this->_sections['z']['iteration'] == 1);
$this->_sections['z']['last']       = ($this->_sections['z']['iteration'] == $this->_sections['z']['total']);
?>
						<?php $this->assign('loop_num', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array'][$this->_sections['z']['index']]); ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['loop_num'] != 1): ?>style="display:none"<?php endif; ?>>
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 <?php echo $this->_tpl_vars['loop_num']; ?>
</b><br /><?php if ($this->_tpl_vars['loop_num'] == 1): ?><i>(More <?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 input boxes will appears as they are used up)</i><?php endif; ?>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				            <textarea id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
][]" onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');" rows="10" cols="60"></textarea>
						 </td>
						</tr>
					<?php endfor; endif; ?>
				<?php else: ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				            <textarea id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]" rows="10" cols="60"></textarea>
						 </td>
						</tr>
				<?php endif; ?>
            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple' || $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'combo'): ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] == 1): ?>
					<?php if (isset($this->_sections['z'])) unset($this->_sections['z']);
$this->_sections['z']['name'] = 'z';
$this->_sections['z']['loop'] = is_array($_loop=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['z']['show'] = true;
$this->_sections['z']['max'] = $this->_sections['z']['loop'];
$this->_sections['z']['step'] = 1;
$this->_sections['z']['start'] = $this->_sections['z']['step'] > 0 ? 0 : $this->_sections['z']['loop']-1;
if ($this->_sections['z']['show']) {
    $this->_sections['z']['total'] = $this->_sections['z']['loop'];
    if ($this->_sections['z']['total'] == 0)
        $this->_sections['z']['show'] = false;
} else
    $this->_sections['z']['total'] = 0;
if ($this->_sections['z']['show']):

            for ($this->_sections['z']['index'] = $this->_sections['z']['start'], $this->_sections['z']['iteration'] = 1;
                 $this->_sections['z']['iteration'] <= $this->_sections['z']['total'];
                 $this->_sections['z']['index'] += $this->_sections['z']['step'], $this->_sections['z']['iteration']++):
$this->_sections['z']['rownum'] = $this->_sections['z']['iteration'];
$this->_sections['z']['index_prev'] = $this->_sections['z']['index'] - $this->_sections['z']['step'];
$this->_sections['z']['index_next'] = $this->_sections['z']['index'] + $this->_sections['z']['step'];
$this->_sections['z']['first']      = ($this->_sections['z']['iteration'] == 1);
$this->_sections['z']['last']       = ($this->_sections['z']['iteration'] == $this->_sections['z']['total']);
?>
						<?php $this->assign('loop_num', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['multiple_array'][$this->_sections['z']['index']]); ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['loop_num'] != 1): ?>style="display:none"<?php endif; ?>>
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 <?php echo $this->_tpl_vars['loop_num']; ?>
</b><br /><?php if ($this->_tpl_vars['loop_num'] == 1): ?><i>(More <?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 input boxes will appears as they are used up)</i><?php endif; ?>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
							<select id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>[]<?php endif; ?>" onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');">
							  <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'multiple'): ?><option value="-1">Please choose an option</option><?php endif; ?>
							  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['selected_option']), $this);?>

							</select>
						 </td>
						</tr>
					<?php endfor; endif; ?>
				<?php else: ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
							<select id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1" <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>[]<?php endif; ?>">
							  <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'multiple'): ?><option value="-1">Please choose an option</option><?php endif; ?>
								  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['selected_option']), $this);?>

							</select>
						 </td>
						</tr>
				<?php endif; ?>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']); ?>
            <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "xsd_display_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'] != "" && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_loop_subelement' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'static'): ?>
            <span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
            <?php endif; ?>
	</table>
		</td></tr>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
        <?php endfor; endif; ?>

        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td width="10" nowrap class="default_white">
                  <nobr>
                  <input type="checkbox" name="report_stays" value="yes" tabindex="11"> <b><a id="white_link" class="white_link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('report_form', 'report_stays');">Keep Form Open</a></b>
                  </nobr>
                </td>
                <td width="100%" align="center">
                  <input class="button" type="submit" value="<?php echo $this->_tpl_vars['form_submit_button']; ?>
" tabindex="12">&nbsp;&nbsp;
                  <input class="button" type="reset" value="Reset" tabindex="13">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            <b>* Required fields</b>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</form>
</table>