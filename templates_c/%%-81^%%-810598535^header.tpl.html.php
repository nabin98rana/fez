<?php /* Smarty version 2.6.2, created on 2004-08-30 12:50:15
         compiled from header.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'header.tpl.html', 3, false),)), $this); ?>
<html>
<head>
<title><?php if ($this->_tpl_vars['extra_title'] != ""):  echo $this->_tpl_vars['extra_title']; ?>
 - <?php endif;  echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
</title>
<link rel="Bookmark" href="<?php echo $this->_tpl_vars['rel_url']; ?>
favicon.ico" />
<link rel="SHORTCUT ICON" href="<?php echo $this->_tpl_vars['rel_url']; ?>
favicon.ico" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/dynCalendar.css" type="text/css" media="screen">
<?php if ($this->_tpl_vars['user_agent'] == 'ie'): ?>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/style.css" type="text/css">
<?php else: ?>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/other.css" type="text/css">
<?php endif; ?>
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/browserSniffer.js"></script>
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/global.js"></script>
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/validation.js"></script>
<?php if ($this->_tpl_vars['refresh_rate']): ?>
<meta http-equiv="Refresh" content="<?php echo $this->_tpl_vars['refresh_rate']; ?>
;URL=<?php echo $this->_tpl_vars['app_base_url'];  echo $this->_tpl_vars['refresh_page']; ?>
">
<?php endif; ?>
</head>