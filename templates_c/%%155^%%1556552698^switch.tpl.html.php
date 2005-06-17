<?php /* Smarty version 2.6.2, created on 2004-07-15 14:57:13
         compiled from switch.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />
<center>
  <span class="default">
  <b>Thank you, your current selected team was changed successfully.</b>
  </span>
</center>

<script language="JavaScript">
<!--
<?php if ($_GET['is_frame'] == 'yes'): ?>
opener.parent.location.href = opener.parent.location;
<?php else: ?>
opener.location.href = opener.location;
<?php endif; ?>
setTimeout('window.close()', 2000);
//-->
</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>