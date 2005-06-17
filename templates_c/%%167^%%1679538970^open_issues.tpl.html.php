<?php /* Smarty version 2.6.2, created on 2004-09-14 14:15:10
         compiled from reports/open_issues.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'reports/open_issues.tpl.html', 32, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" colspan="3" class="default_white">
            <b>Showing all open issues older than <?php echo $this->_tpl_vars['cutoff_days']; ?>
 days.</b>
          </td>
        </tr>
        <tr>
          <td width="120" class="default">
            <b>Number of Days:</b>
          </td>
          <td width="100">
            <input class="default" type="text" size="5" name="cutoff_days" value="<?php echo $this->_tpl_vars['cutoff_days']; ?>
">
          </td>
          <td>
            <input type="submit" value="Submit" class="shortcut">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

<?php if (count($_from = (array)$this->_tpl_vars['users'])):
    foreach ($_from as $this->_tpl_vars['user_full_name'] => $this->_tpl_vars['assigned_issues']):
?>
<h4><?php echo ((is_array($_tmp=$this->_tpl_vars['user_full_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h4>
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td rowspan="2" align="center" class="default_white">Issue ID</td>
          <td rowspan="2" align="center" class="default_white">Summary</td>
          <td rowspan="2" align="center" class="default_white">Status</td>
          <td rowspan="2" align="center" class="default_white">Time Spent</td>
          <td rowspan="2" align="center" class="default_white">Created</td>
          <td colspan="2" align="center" class="default_white">Days and Hours Since</td>
        </tr>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td align="center" class="default_white">Last Update</td>
          <td align="center" class="default_white">Last Outgoing Msg</td>
        </tr>
        <?php if (count($_from = (array)$this->_tpl_vars['assigned_issues'])):
    foreach ($_from as $this->_tpl_vars['issue_id'] => $this->_tpl_vars['issue']):
?>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><a target="_top" href="<?php echo $this->_tpl_vars['rel_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['issue_id']; ?>
" class="link" title="view issue details"><?php echo $this->_tpl_vars['issue_id']; ?>
</a></td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default"><a target="_top" href="<?php echo $this->_tpl_vars['rel_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['issue_id']; ?>
" class="link" title="view issue details"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']['iss_summary'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a></td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']['sta_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><?php echo $this->_tpl_vars['issue']['time_spent']; ?>
</td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><?php echo $this->_tpl_vars['issue']['iss_created_date']; ?>
</td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><?php echo $this->_tpl_vars['issue']['last_update']; ?>
</td>
          <td bgcolor="<?php echo $this->_tpl_vars['issue']['status_color']; ?>
" class="default" align="center"><?php echo $this->_tpl_vars['issue']['last_email_response']; ?>
</td>
        </tr>
        <?php endforeach; unset($_from); endif; ?>
      </table>
    </td>
  </tr>
</table>
<br />
<?php endforeach; unset($_from); endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>