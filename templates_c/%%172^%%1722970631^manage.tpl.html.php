<?php /* Smarty version 2.6.2, created on 2005-06-10 09:54:36
         compiled from manage/manage.tpl.html */ ?>

<?php if ($this->_tpl_vars['show_not_allowed_msg']): ?>
<center>
<span class="default">
<b>Sorry, but you do not have the required permission level to access this screen.</b>
<br /><br />
<a class="link" href="javascript:history.go(-1);">Go Back</a>
</span>
</center>
<?php else: ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td width="100%">
            <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
              <?php if ($this->_tpl_vars['isAdministrator']): ?>
              <tr>
                <td>
                  <span class="default"><b>Configuration:</b></span>
                </td>
              </tr>
              <tr>
                <td nowrap class="default">
                  <ul>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/general.php" class="link">General Setup</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/email_accounts.php" class="link">Manage Email Accounts</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/doctypexsds.php" class="link">Manage Document Type XSDs</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/controlledvocabs.php" class="link">Manage Controlled Vocabularies</a></li>
                  </ul>
                </td>
              </tr>
              <?php endif; ?>
              <tr>
                <td>
                  <span class="default"><b>Areas:</b></span>
                </td>
              </tr>
              <tr>
                <td nowrap class="default">
                  <ul>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/news.php" class="link">Manage News</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/statuses.php" class="link">Manage Statuses</a></li><br />
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/communities.php" class="link">Manage Communities</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/collections.php" class="link">Manage Collections</a></li>
                    <li><a href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/users.php" class="link">Manage Users</a></li>
                  </ul>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td>&nbsp;
      
    </td>
    <td width="100%" valign="top">
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "manage/".($this->_tpl_vars['type']).".tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
</table>
<?php endif; ?>
