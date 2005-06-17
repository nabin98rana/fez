<?php /* Smarty version 2.6.2, created on 2005-06-05 21:40:49
         compiled from popup.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>



<br />
<center>
  <span class="default">

<?php if ($this->_tpl_vars['purge_datastream_result'] == -1): ?>
  <b>An error occurred while trying to purge the datastream</b>
<?php elseif ($this->_tpl_vars['purge_datastream_result'] == 1): ?>
  <b>Thank you, the datastream was purged successfully.</b>
<?php endif; ?>
<?php if ($this->_tpl_vars['update_form_result'] == -1): ?>
  <b>An error occurred while trying to update the record</b>
<?php elseif ($this->_tpl_vars['update_form_result'] == 1): ?>
  <b>Thank you, the record was updated successfully.</b>
<?php endif; ?>
<?php if ($this->_tpl_vars['purge_object_result'] == -1): ?>
  <b>An error occurred while trying to purge the object</b>
<?php elseif ($this->_tpl_vars['purge_object_result'] == 1): ?>
  <b>Thank you, the object was purged successfully.</b>
<?php endif; ?>
  </span>
</center>


<script language="JavaScript">
<!--
<?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows'] == '1'): ?>
setTimeout('closeAndRefresh()', 1500);
<?php endif; ?>
//-->
</script>
<br />
<?php if (! $this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
<center>
  <span class="default"><a class="link" href="javascript:void(null);" onClick="<?php if ($this->_tpl_vars['new_project_result'] == 1): ?>javascript:closeAndGotoList();<?php else: ?>javascript:closeAndRefresh();<?php endif; ?>">Continue</a></span>
</center>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>