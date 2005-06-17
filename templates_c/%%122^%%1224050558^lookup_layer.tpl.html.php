<?php /* Smarty version 2.6.2, created on 2004-06-25 14:06:52
         compiled from lookup_layer.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'lookup_layer.tpl.html', 1, false),)), $this); ?>
<select size=\'6\' <?php if ($this->_tpl_vars['multiple']): ?>multiple<?php endif; ?> name=\'lookup<?php if ($this->_tpl_vars['multiple']): ?>[]<?php endif; ?>\' class=\'default_overlib\'><?php if (count($_from = (array)$this->_tpl_vars['list'])):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
?><option value=\'<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('replace', true, $_tmp, "'", "&#146;") : smarty_modifier_replace($_tmp, "'", "&#146;")))) ? $this->_run_mod_handler('replace', true, $_tmp, "\"", "&quot;") : smarty_modifier_replace($_tmp, "\"", "&quot;")); ?>
\'><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['item'])) ? $this->_run_mod_handler('replace', true, $_tmp, "'", "&#146;") : smarty_modifier_replace($_tmp, "'", "&#146;")))) ? $this->_run_mod_handler('replace', true, $_tmp, "\"", "&quot;") : smarty_modifier_replace($_tmp, "\"", "&quot;")); ?>
</option><?php endforeach; unset($_from); endif; ?></select>