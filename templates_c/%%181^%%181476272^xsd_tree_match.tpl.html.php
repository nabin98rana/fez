<?php /* Smarty version 2.6.2, created on 2005-03-16 13:35:02
         compiled from manage/xsd_tree_match.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'manage/xsd_tree_match.tpl.html', 3, false),)), $this); ?>
<html>
<head>
<title><?php echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
</title>
</head>

<frameset rows="121,*" frameborder="1" border="1" framespacing="1" bordercolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
  <frame name="_topframe" src="top.php" marginwidth="0" marginheight="0" scrolling="no" frameborder="0" framespacing="0">
  <frameset cols="600,*" frameborder="1" framespacing="6" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" border="8" bordercolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
    <frame name="_treeframe" src="xsd_tree.php?xdis_id=<?php echo $this->_tpl_vars['xdis_id']; ?>
" scrolling="yes" topmargin="10" leftmargin="10" marginheight="10" marginwidth="10" frameborder="1" border="0">
    <frame name="basefrm" src="about:blank" scrolling="auto" topmargin="15" leftmargin="15" marginheight="15" marginwidth="15" frameborder="1" border="0">
  </frameset>
</frameset><noframes></noframes>

</html>