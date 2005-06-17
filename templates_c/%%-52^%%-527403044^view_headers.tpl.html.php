<?php /* Smarty version 2.6.2, created on 2004-06-28 15:16:11
         compiled from view_headers.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'view_headers.tpl.html', 20, false),array('modifier', 'nl2br', 'view_headers.tpl.html', 20, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form>
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <b>View Email Raw Headers</b>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <input type="button" class="button" value="Close" onClick="javascript:window.close();">
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['headers'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <input type="button" class="button" value="Close" onClick="javascript:window.close();">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

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