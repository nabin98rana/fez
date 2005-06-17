<?php /* Smarty version 2.6.2, created on 2004-09-16 11:35:28
         compiled from duplicate.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'duplicate.tpl.html', 82, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "navigation.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />
<?php if ($this->_tpl_vars['duplicate_result'] != ""): ?>
<table width="500" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td class="default">
            <?php if ($this->_tpl_vars['duplicate_result'] == -1): ?>
            <b>Sorry, an error happened while trying to run your query.</b>
            <?php elseif ($this->_tpl_vars['duplicate_result'] == 1): ?>
            <b>Thank you, the issue was marked as a duplicate successfully. Please choose 
            from one of the options below:</b>
            <ul>
              <li><a href="view.php?id=<?php echo $_POST['issue_id']; ?>
" class="link">Open the Issue Details Page</a></li>
              <li><a href="list.php" class="link">Open the Issue Listing Page</a></li>
              <?php if ($this->_tpl_vars['app_setup']['support_email'] == 'enabled' && $this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer']): ?>
              <li><a href="emails.php" class="link">Open the Emails Listing Page</a></li>
              <?php endif; ?>
            </ul>
            <b>Otherwise, you will be automatically redirected to the Issue Details Page in 5 seconds.</b>
            <?php echo '
            <script language="JavaScript">
            <!--
            setTimeout(\'openDetailPage()\', 5000);
            function openDetailPage()
            {
            '; ?>

                window.location.href = 'view.php?id=<?php echo $_POST['issue_id']; ?>
';
            <?php echo '
            }
            //-->
            </script>
            '; ?>

            <?php endif; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php else: ?>
<?php echo '
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="js/overlib_mini.js"></script>
<script language="JavaScript">
<!--
function validateDuplicate(f)
{
    if (hasSelected(f.duplicated_issue, -1)) {
        alert(\'Please choose the duplicated issue.\');
        selectField(f, \'duplicated_issue\');
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<table width="80%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="duplicate_form" onSubmit="javascript:return validateDuplicate(this);" method="post" action="duplicate.php">
<input type="hidden" name="cat" value="mark">
<input type="hidden" name="issue_id" value="<?php echo $_GET['id']; ?>
">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default" nowrap>
            <b>Mark Issue as Duplicate</b> (Issue ID: <a href="<?php echo $this->_tpl_vars['rel_url']; ?>
view.php?id=<?php echo $_GET['id']; ?>
" class="link"><?php echo $_GET['id']; ?>
</a>)
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Duplicated Issue: *</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select name="duplicated_issue" class="default">
              <option value="-1">Please select an issue</option>
              <?php echo smarty_function_html_options(array('values' => $this->_tpl_vars['issues'],'output' => $this->_tpl_vars['issues']), $this);?>

            </select>
            <?php if (! ( $this->_tpl_vars['os']['mac'] && $this->_tpl_vars['browser']['ie'] )): ?><a title="lookup issues by their summaries" href="javascript:void(null);" onClick="javascript:return overlib(getOverlibContents('<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_layer.tpl.html", 'smarty_include_vars' => array('list' => $this->_tpl_vars['assoc_issues'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>', 'duplicate_form', 'duplicated_issue', true), STICKY, HEIGHT, 50, WIDTH, 160, BELOW, RIGHT, CLOSECOLOR, '#FFFFFF', FGCOLOR, '#FFFFFF', BGCOLOR, '#000000', CAPTION, 'Lookup Details', CLOSECLICK);" onMouseOut="javascript:nd();"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" border="0"></a><?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'duplicated_issue')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td width="150" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Comments:</b><br />
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <textarea name="comments" rows="8" style="width: 97%"></textarea>
          </td>
        </tr>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td colspan="2">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td><input class="button" type="button" value="&lt;&lt; Back" onClick="javascript:history.go(-1);"></td>
                <td width="100%" align="center"><input class="button" type="submit" value="Mark Issue as Duplicate"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default">
            <b>* Required fields</b>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</form>
</table>
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