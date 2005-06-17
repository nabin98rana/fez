<?php /* Smarty version 2.6.2, created on 2005-06-16 13:28:25
         compiled from view_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'view_form.tpl.html', 99, false),array('modifier', 'replace', 'view_form.tpl.html', 154, false),array('modifier', 'cat', 'view_form.tpl.html', 154, false),array('function', 'fetch', 'view_form.tpl.html', 157, false),)), $this); ?>

<script language="JavaScript">
<!--
//var page_url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
//var current_page = <?php echo $this->_tpl_vars['details_info']['current_page']; ?>
;
//var last_page = <?php echo $this->_tpl_vars['details_info']['last_page']; ?>
;
<?php echo '
function assignItems(f)
{
    if (!hasOneChecked(f, \'item[]\')) {
        alert(\'Please choose which entries to assign.\');
        return false;
    }
    if (f.users.options[f.users.selectedIndex].value == \'\') {
        alert(\'Please choose the user to assign these entries to.\');
        f.users.focus();
        selectField(f, \'users\');
        return false;
    }
    var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var popupWin = window.open(\'\', \'_popup\', features);
    popupWin.focus();
    f.action = \'popup.php\';
    f.target = \'_popup\';
    f.submit();
}
function hideClosed(f)
{
    if (f.hide_closed.checked) {
        window.location.href = page_url + "?" + replaceParam(window.location.href, \'hide_closed\', \'1\');
    } else {
        window.location.href = page_url + "?" + replaceParam(window.location.href, \'hide_closed\', \'0\');
    }
}
function resizePager(f)
{
    var pagesize = f.page_size.options[f.page_size.selectedIndex].value;
    window.location.href = page_url + "?" + replaceParam(window.location.href, \'rows\', pagesize);
}
function checkPageField(ev)
{
    // check if the user is trying to submit the form by hitting <enter>
    if (((window.event) && (window.event.keyCode == 13)) ||
            ((ev) && (ev.which == 13))) {
        return false;
    }
}
function goPage(f, new_page)
{
    if ((new_page > last_page+1) || (new_page <= 0) ||
            (new_page == current_page+1) || (!isNumberOnly(new_page))) {
        f.page.value = current_page+1;
        return false;
    }
    setPage(new_page-1);
}
function setPage(new_page)
{
    if ((new_page > last_page) || (new_page < 0) ||
            (new_page == current_page)) {
        return false;
    }
    window.location.href = page_url + "?" + replaceParam(window.location.href, \'pagerRow\', new_page);
}
function downloadCSV()
{
    var f = this.document.csv_form;
    f.submit();
    return false;
}
//window.onload = disableFields;
function disableFields()
{
    var f = document.details_form;
    if (current_page == 0) {
        f.first.disabled = true;
        f.previous.disabled = true;
    }
    if ((current_page == last_page) || (last_page <= 0)) {
        f.next.disabled = true;
        f.last.disabled = true;
    }
    if ((current_page == 0) && (last_page <= 0)) {
        f.page.disabled = true;
        f.go.disabled = true;
    }
}
//-->
</script>
'; ?>

<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <form name="details_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
  <input type="hidden" name="cat" value="assign">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">

        <tr>
          <td align="left" class="default" nowrap colspan="2"><br /> <b>View Details: </b> <?php if ($this->_tpl_vars['isEditor'] || $this->_tpl_vars['isAdministrator']): ?><a href='update.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img title="Edit" align="absmiddle" src="/images/edit.png" border="0"></a> <?php endif; ?><br /><br /></td>
        </tr>

		<tr>
		  <td align="left"> 
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
			</table><br />
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
		<?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'static' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsdmf_id_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != 'xsd_ref' && $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_html_input'] != ''): ?>
		<?php $this->assign('temp_fld_id', $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_id']); ?>
		  <?php if ($this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_show_in_view'] == 1): ?>
			<tr>
			  <td align="left" class="default" width="10%"><b><?php echo $this->_tpl_vars['xsd_display_fields'][$this->_sections['i']['index']]['xsdmf_title']; ?>
</b></td>
			  <td align="left" class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['details'][$this->_tpl_vars['temp_fld_id']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
			</tr>
			<?php endif; ?>
		<?php endif; ?>	
        <?php endfor; else: ?>
        <tr bgcolor="gray">
          <td colspan="13" class="default_white" align="center">
            <b>No details could be found.</b>
          </td>
        </tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
  </form>
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
</a> </td>
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

</table>
