<?php /* Smarty version 2.6.2, created on 2005-06-16 16:37:48
         compiled from list_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'list_form.tpl.html', 151, false),)), $this); ?>
<?php echo '
<script language="JavaScript">
<!--
//var page_url = \'';  echo $_SERVER['PHP_SELF'];  echo '\';
//var current_page = ';  echo $this->_tpl_vars['list_info']['current_page'];  echo ';
//var last_page = ';  echo $this->_tpl_vars['list_info']['last_page'];  echo ';
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
    var f = document.list_form;
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


<?php echo '
<script language="JavaScript">
<!--
function purgeObject(pid)
{
    if (!confirm(\'This action will permanently delete the selected object.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'popup.php?cat=purge_object&pid=\' + pid, \'_popup\', features);
        popupWin.focus();
    }
}
//-->
</script>
'; ?>

<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <form name="list_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
  <input type="hidden" name="cat" value="assign">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">

		<?php if ($this->_tpl_vars['list_type'] == 'collection_records_list'): ?>
		<tr>
		  <td align="left"> <br />
			<table bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="1">
				<tr>
				  <td align="left" class="default"> <b>Parent Communities: </b></td>
				  <td align="left" class="default"> &nbsp;
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
						<a href="/list.php?community_pid=<?php echo $this->_tpl_vars['parents'][$this->_sections['i']['index']]['pid']; ?>
"><?php echo $this->_tpl_vars['parents'][$this->_sections['i']['index']]['title']; ?>
</a> &nbsp;
					<?php endfor; endif; ?>
				  </td>
				</tr>
			</table>
		  </td>
		</tr>
		<?php endif; ?>
        <tr>
			<td align="left" class="default"><br />
		<?php if ($this->_tpl_vars['list_type'] == 'all_records_list' || $this->_tpl_vars['list_type'] == 'collection_records_list'): ?>
			<img src="/images/record_32.gif" align="absmiddle"/>		
		<?php elseif ($this->_tpl_vars['list_type'] == 'community_list'): ?>
			<img src="/images/community_32.png" align="absmiddle"/>
		<?php elseif ($this->_tpl_vars['list_type'] == 'collection_list'): ?>
			<img src="/images/collection_32.png" align="absmiddle"/>
		<?php endif; ?>
           &nbsp;<b><?php echo $this->_tpl_vars['list_heading']; ?>
: </b></td>
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
		 <?php if (( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isLister'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?>
        <tr>
          <td align="left" class="default"><br /> 
			<?php if (( $this->_tpl_vars['list_type'] == 'all_records_list' || $this->_tpl_vars['list_type'] == 'collection_records_list' )): ?>
				
				<?php if (( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isViewer'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?><a href='view.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><?php endif; ?><img align="absmiddle" src="/images/record.png" border="0"><?php if (( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isViewer'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?></a> <a href='view.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><?php endif;  echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  if (( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isViewer'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?></a><?php endif; ?>  &nbsp;&nbsp;<?php if ($this->_tpl_vars['isUser'] && ( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isEditor'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?> <a href='update.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img title="Edit" align="absmiddle" src="/images/edit.png" border="0"></a><?php endif;  if ($this->_tpl_vars['isAdministrator']): ?> &nbsp; <a href='javascript:void(null);' onClick="javascript:purgeObject('<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
');"><img title="Delete" align="absmiddle" src="/images/delete.png" border="0"></a><?php endif; ?>
			<?php elseif ($this->_tpl_vars['list_type'] == 'community_list'): ?>
				<a href='list.php?community_pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img align="absmiddle" src="/images/community.png" border="0"></a> <a href='list.php?community_pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a> &nbsp;&nbsp;<?php if ($this->_tpl_vars['isAdministrator']): ?> <a href='update.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img title="Edit" align="absmiddle" src="/images/edit.png" border="0"></a> &nbsp; <a href='javascript:void(null);' onClick="javascript:purgeObject('<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
');"><img title="Delete" align="absmiddle" src="/images/delete.png" border="0"></a><?php endif; ?>
			<?php elseif ($this->_tpl_vars['list_type'] == 'collection_list'): ?>
				 <a href='list.php?collection_pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img align="absmiddle" src="/images/collection.png" border="0"></a> <a href='list.php?collection_pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>  &nbsp;&nbsp; <?php if ($this->_tpl_vars['isUser'] && ( $this->_tpl_vars['list'][$this->_sections['i']['index']]['isEditor'] == 1 || $this->_tpl_vars['isAdministrator'] )): ?>  <a href='update.php?pid=<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'><img title="Edit" align="absmiddle" src="/images/edit.png" border="0"></a> <?php endif;  if ($this->_tpl_vars['isAdministrator']): ?>&nbsp; <a href='javascript:void(null);' onClick="javascript:purgeObject('<?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
');"><img title="Delete" align="absmiddle" src="/images/delete.png" border="0"></a> <?php endif; ?>
			<?php endif; ?>
		  </td>
        </tr>
		<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['creator'] != ''): ?>
        <tr>
          <td align="left" class="default"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['creator'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>. <?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['date'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <br /></td>
        </tr>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['description']): ?> 
        <tr>
          <td align="left" class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br /></td>
        </tr>
		<?php endif; ?>
		 <?php endif; ?>
        <?php endfor; else: ?>
        <tr bgcolor="gray">
          <td colspan="13" class="default_white" align="center">
            <b>No records could be found.</b>
          </td>
        </tr>
        <?php endif; ?>
		<?php if ($this->_tpl_vars['isUser']): ?>
        <tr>
          <td align="left" class="default">
			<?php if ($this->_tpl_vars['list_type'] == 'community_list' && $this->_tpl_vars['isAdministrator']): ?>
			<br /> <a target="_top" title="create a new community" href="<?php echo $this->_tpl_vars['rel_url']; ?>
new.php?xdis_id=<?php echo $this->_tpl_vars['xdis_id']; ?>
"><img align="absmiddle" src="/images/folder_new.png" border="0"><b>Create New Community</b></a>&nbsp;
			<?php elseif ($this->_tpl_vars['list_type'] == 'collection_list' && ( $this->_tpl_vars['isAdministrator'] || $this->_tpl_vars['isCreator'] )): ?>
			<br /> <a target="_top" title="create a new collection" href="<?php echo $this->_tpl_vars['rel_url']; ?>
new.php?xdis_id=<?php echo $this->_tpl_vars['xdis_id'];  if ($this->_tpl_vars['community_pid']): ?>&community_pid=<?php echo $this->_tpl_vars['community_pid'];  endif; ?>"><img align="absmiddle" src="/images/folder_new.png" border="0"><b>Create New Collection</b></a>&nbsp;
			<?php elseif ($this->_tpl_vars['list_type'] == 'all_records_list' || $this->_tpl_vars['list_type'] == 'collection_records_list' && ( $this->_tpl_vars['isAdministrator'] || $this->_tpl_vars['isCreator'] )): ?>
			<br /> <a target="_top" title="create a new record" href="<?php echo $this->_tpl_vars['rel_url']; ?>
new.php<?php if ($this->_tpl_vars['collection_pid']): ?>?collection_pid=<?php echo $this->_tpl_vars['collection_pid'];  endif; ?>"><img align="absmiddle" src="/images/folder_new.png" border="0"><b>Create New Record</b></a>&nbsp;
			<?php endif; ?>
		  </td>
        </tr>
		<?php endif; ?>



      </table>
    </td>
  </tr>
  </form>
</table>
