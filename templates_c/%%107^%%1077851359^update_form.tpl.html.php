<?php /* Smarty version 2.6.2, created on 2005-06-16 13:53:31
         compiled from update_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'update_form.tpl.html', 157, false),array('modifier', 'escape', 'update_form.tpl.html', 164, false),array('modifier', 'replace', 'update_form.tpl.html', 264, false),array('function', 'fetch', 'update_form.tpl.html', 159, false),array('function', 'html_options', 'update_form.tpl.html', 223, false),)), $this); ?>
<?php echo '
<script language="JavaScript">
<!--
function purgeDatastream(ds_id)
{
    if (!confirm(\'This action will permanently delete the selected datastream.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'popup.php?cat=purge_datastream&pid=';  echo $this->_tpl_vars['pid'];  echo '&ds_id=\' + ds_id, \'_popup\', features);
        popupWin.focus();
    }
}
function updateForm()
{
  var features = \'width=420,height=200,top=30,left=30,resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
  var updateWin = window.open(\'\', \'_update_record_details\', features);
  updateWin.focus();
  return true;
}
//-->
</script>
'; ?>



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


<form name="report_form" onSubmit="javascript:return updateForm();" target="_update_record_details" action="/popup.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="cat" value="update_form">
<input type="hidden" name="xdis_id" value="<?php echo $this->_tpl_vars['xdis_id']; ?>
">
<input type="hidden" name="pid" value="<?php echo $this->_tpl_vars['pid']; ?>
">
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
	<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title'] == 'state'): ?>
<input type="hidden" name="state" value="<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_static_text']; ?>
">
	<?php endif; ?>
<?php endfor; endif; ?>
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="0" border="0">
        <tr>
          <td class="default">
            <b>Edit Record</b>
          </td>
          <td align="right" class="default">&nbsp;
            
          </td>
        </tr>
		<tr>
		  <td colspan="2" align="left"> <br />
			<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="1">
				<tr>
				  <td align="left" class="default" nowrap> <b>Parent Collections: </b></td>
				  <td align="left" class="default" nowrap> &nbsp;
					<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['parents']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
						<a href="/list.php?collection_pid=<?php echo $this->_tpl_vars['parents'][$this->_sections['i']['index']]['pid']; ?>
"><?php echo $this->_tpl_vars['parents'][$this->_sections['i']['index']]['title']; ?>
</a> &nbsp;
					<?php endfor; endif; ?>
				  </td>
				</tr>
			</table>
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
		<?php $this->assign('temp_fld_id', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']); ?>
		<?php if (( $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsdmf_id_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != '' )): ?>
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
            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'checkbox'): ?>
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
						<input class="default" type="checkbox" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
][]" <?php if ($this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']][$this->_sections['z']['index']] == 'on'): ?>checked<?php endif; ?> onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');">
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
						<input class="default" type="checkbox" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]" <?php if ($this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']] == 'on'): ?>checked<?php endif; ?>>
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
"> <?php endif; ?><b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_static_text']; ?>
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
"> <?php endif; ?><b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
							<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'] != ""): ?>
								&nbsp;<i><?php echo ((is_array($_tmp=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
							<?php endif; ?>
						  </td>
						</tr>

            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'text'): ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] == 1): ?>
					<?php $this->assign('show_num', 0); ?>
					<?php $this->assign('check_num', 0); ?>						
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
						<?php if ($this->_tpl_vars['show_num'] != 0): ?>
							<?php $this->assign('check_num', $this->_tpl_vars['show_num']-1); ?>
						<?php endif; ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" <?php if ($this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']][$this->_tpl_vars['loop_num']] == '' && $this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']][$this->_tpl_vars['check_num']] == '' && $this->_tpl_vars['show_num'] != 0): ?>style="display:none"<?php endif; ?>>
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 <?php echo $this->_tpl_vars['loop_num']; ?>
 </b><br /><?php if ($this->_tpl_vars['loop_num'] == 1): ?><i>(More <?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
 input boxes will appear as they are used up)</i><?php endif; ?>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
						<input class="default" type="text" id="xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_<?php echo $this->_tpl_vars['loop_num']; ?>
" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
][]" value="<?php echo $this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']][$this->_sections['z']['index']]; ?>
" onChange="javascript:unhideRow('xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
', 'choicexsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
');" maxlength="255" size="50">
						 </td>
						</tr>
						<?php $this->assign('show_num', $this->_tpl_vars['show_num']+1); ?>
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
]" value="<?php echo $this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']]; ?>
"  maxlength="255" size="50">
				<?php endif; ?>
            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'textarea'): ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
				            <textarea name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]" rows="10" cols="60"><?php echo $this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']]; ?>
</textarea>
            <?php elseif ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple' || $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'combo'): ?>
						<tr id="tr_xsd_display_fields_<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
_1">
						  <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
							<b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b>
						  </td>
						  <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
							<select <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>multiple size="3"<?php endif; ?> class="default" name="xsd_display_fields[<?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']; ?>
]<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>[]<?php endif; ?>">
							  <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'multiple'): ?><option value="-1">Please choose an option</option><?php endif; ?>
							  <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['field_options'],'selected' => $this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']]), $this);?>

							</select>
            <?php endif; ?>
            <?php $this->assign('custom_field_id', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']); ?>

            <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] == 'multiple'): ?>
              <?php $this->assign('custom_field_sufix', "[]"); ?>
            <?php else: ?>
              <?php $this->assign('custom_field_sufix', ""); ?>
            <?php endif; ?>
			<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_multiple'] != 1): ?>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => "xsd_display_fields[".($this->_tpl_vars['custom_field_id'])."]".($this->_tpl_vars['custom_field_sufix']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'] != "" && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_loop_subelement' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'static'): ?>
				<span class="small_default">(<?php echo ((is_array($_tmp=$this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
)</span>
				<?php endif; ?>
	
							 </td>
							</tr>
			<?php endif; ?>
	</table>
		</td></tr>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
        <?php endfor; endif; ?>

        <tr>
		  <td colspan="2" class="default">
			<table border="0" cellpadding="2" cellspacing="0" width="100%"  bgcolor="#FFFFFF" >
			  <tr>
				<td class="default" colspan="3"><b>Attached Datastreams</b></td>
			  </tr>

			  <tr class="default_white">
				<td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">Name</td>
				<td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">MIMEType</td>
				<td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">Size</td>
			  </tr>

        <?php if (isset($this->_sections['d'])) unset($this->_sections['d']);
$this->_sections['d']['name'] = 'd';
$this->_sections['d']['loop'] = is_array($_loop=$this->_tpl_vars['datastreams']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['d']['show'] = true;
$this->_sections['d']['max'] = $this->_sections['d']['loop'];
$this->_sections['d']['step'] = 1;
$this->_sections['d']['start'] = $this->_sections['d']['step'] > 0 ? 0 : $this->_sections['d']['loop']-1;
if ($this->_sections['d']['show']) {
    $this->_sections['d']['total'] = $this->_sections['d']['loop'];
    if ($this->_sections['d']['total'] == 0)
        $this->_sections['d']['show'] = false;
} else
    $this->_sections['d']['total'] = 0;
if ($this->_sections['d']['show']):

            for ($this->_sections['d']['index'] = $this->_sections['d']['start'], $this->_sections['d']['iteration'] = 1;
                 $this->_sections['d']['iteration'] <= $this->_sections['d']['total'];
                 $this->_sections['d']['index'] += $this->_sections['d']['step'], $this->_sections['d']['iteration']++):
$this->_sections['d']['rownum'] = $this->_sections['d']['iteration'];
$this->_sections['d']['index_prev'] = $this->_sections['d']['index'] - $this->_sections['d']['step'];
$this->_sections['d']['index_next'] = $this->_sections['d']['index'] + $this->_sections['d']['step'];
$this->_sections['d']['first']      = ($this->_sections['d']['iteration'] == 1);
$this->_sections['d']['last']       = ($this->_sections['d']['iteration'] == $this->_sections['d']['total']);
?>
			  <tr class="default"> 
				<?php $this->assign('image_file_name', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['MIMEType'])) ? $this->_run_mod_handler('replace', true, $_tmp, "/", '_') : smarty_modifier_replace($_tmp, "/", '_')))) ? $this->_run_mod_handler('cat', true, $_tmp, ".png") : smarty_modifier_cat($_tmp, ".png"))); ?>
				<?php $this->assign('image_name', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['espace_root_dir'])) ? $this->_run_mod_handler('cat', true, $_tmp, "images/") : smarty_modifier_cat($_tmp, "images/")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['image_file_name']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['image_file_name']))); ?> 
				<?php $this->assign('image_file_string', ""); ?>
				<?php echo smarty_function_fetch(array('file' => $this->_tpl_vars['image_name'],'assign' => 'image_file_string'), $this);?>
 
				<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><?php if ($this->_tpl_vars['image_file_string'] != ""): ?><img align="absmiddle" src="/images/<?php echo $this->_tpl_vars['image_file_name']; ?>
"><?php else: ?><img align="absmiddle" src="/images/default.png"><?php endif; ?> <a target="_blank" href="eserv.php?pid=<?php echo $this->_tpl_vars['pid']; ?>
&dsID=<?php echo $this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['ID']; ?>
"><?php echo $this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['ID']; ?>
</a> [ <a class="link" title="purge datastream" href="javascript:void(null);" onClick="javascript:purgeDatastream('<?php echo $this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['ID']; ?>
');">purge</a> ]</td>
				<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><?php echo $this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['MIMEType']; ?>
</td>
				<td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
"><?php echo $this->_tpl_vars['datastreams'][$this->_sections['d']['index']]['size']; ?>
 bytes</td>
			  </tr>
        <?php endfor; endif; ?>
		    </table>
		  </td>
		</tr>			

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
                  <input class="button" type="submit" value="Submit" tabindex="12">&nbsp;&nbsp;
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