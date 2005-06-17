<?php /* Smarty version 2.6.2, created on 2005-01-28 14:25:37
         compiled from reports/tree.tpl.html */ ?>
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
images/dtree/folder.gif';
tree.icon.folderOpen = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/folderopen.gif';
tree.icon.node = '<?php echo $this->_tpl_vars['rel_url']; ?>
images/dtree/page.gif';
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

tree.add(0, -1, 'Available Reports');
tree.add(1, 0, 'Issues');
tree.add(2, 1, 'Issues by User', 'issue.php?cat=user', '', 'basefrm');
tree.add(3, 1, 'Open Issues Report', 'open_issues.php', '', 'basefrm');
tree.add(4, 0, 'Weekly Report', 'weekly.php', '', 'basefrm');
tree.add(5, 0, 'Workload by time period', 'workload_time_period.php', '', 'basefrm');
tree.add(6, 0, 'Email by time period', 'workload_time_period.php?type=email', '', 'basefrm');
tree.add(7, 0, 'Custom Fields', 'custom_fields.php', '', 'basefrm');
tree.add(8, 0, 'Quarterly Reports', 'quarterly_reports.php', '', 'basefrm');
tree.add(9, 0, 'AskIT Reports', 'askit_reports.php', '', 'basefrm');

document.write(tree);

tree.openAll();
//-->
</script>
</div>

</body>
</html>