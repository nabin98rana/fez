<?php /* Smarty version 2.6.2, created on 2005-06-16 13:42:35
         compiled from view.tpl.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => 'Record Details')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "navigation.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['pid'] == ""): ?>
  <table width="300" align="center">
    <tr>
      <td>
        &nbsp;<span class="default"><b>Error: The object could not be found.</b>
        <br /><br />
        &nbsp;<a class="link" href="javascript:history.go(-1);">Go Back</a></span>
      </td>
    </tr>
  </table>
<?php else: ?>
  <?php if ($this->_tpl_vars['isViewer']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "view_form.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  <?php else: ?>
		  <center>
		<span class="default">
		<b>Sorry, but you do not have the required permission level to access this screen.</b>
		<br /><br />
		<a class="link" href="javascript:history.go(-1);">Go Back</a>
		</span>
		</center>
  <?php endif; ?>
<?php endif; ?>


<br />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "app_info.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>