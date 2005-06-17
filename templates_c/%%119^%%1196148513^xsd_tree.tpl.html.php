<?php /* Smarty version 2.6.2, created on 2005-03-31 16:14:53
         compiled from manage/xsd_tree.tpl.html */ ?>
<html>
<head>
<link rel="StyleSheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/dtree.css" type="text/css" />
<script type="text/javascript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/dtree.js"></script>
</head>

<body topmargin="5" marginheight="5">

<div class="dtree">
<script type="text/javascript">
<!--
tree = new dTree('tree');
tree.config.useCookies = false;
tree.icon.root = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/base.gif';
tree.icon.folder = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/element.gif';
tree.icon.folderOpen = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/element.gif';
tree.icon.node = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/element.gif';
tree.icon.empty = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/empty.gif';
tree.icon.line = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/line.gif';
tree.icon.join= '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/join.gif';
tree.icon.joinBottom = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/joinbottom.gif';
tree.icon.plus = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/plus.gif';
tree.icon.plusBottom = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/plusbottom.gif';
tree.icon.minus = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/minus.gif';
tree.icon.minusBottom = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/minusbottom.gif';
tree.icon.nlPlus = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/nolines_plus.gif';
tree.icon.nlMinus = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/nolines_minus.gif';

<?php echo $this->_tpl_vars['xsd_tree']; ?>


document.write(tree);

tree.openAll();
//-->
</script>
</div>

</body>
</html>