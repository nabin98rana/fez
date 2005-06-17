<?php /* Smarty version 2.6.2, created on 2005-03-03 10:38:45
         compiled from select_collection.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'select_collection.tpl.html', 47, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => 'Select Collection')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br /><br />
<?php echo '
<script language="JavaScript">
<!--
function validateForm(f)
{
    if (!hasOneSelected(f, \'collection\')) {
        alert(\'Please choose the collection.\');
        selectField(f, \'collection\');
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<form name="login_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<input type="hidden" name="cat" value="select">
<input type="hidden" name="url" value="<?php echo $_GET['url']; ?>
">
<table align="center" width="400" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td>
      <table bgcolor="#006486" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2" align="center">
            <h1 style="color: white;">Select Collection</h1>
            <hr size="1" noshade color="#000000">
          </td>
        </tr>
        <?php if ($this->_tpl_vars['err'] != 0 || $_GET['err'] != 0): ?>
        <tr>
          <td colspan="2" align="center" class="error">
            <b>
            <?php if ($this->_tpl_vars['err'] == 1 || $_GET['err'] == 1): ?>
              You are not allowed to use the selected collection.
            <?php endif; ?>
            </b>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td align="right" width="40%" class="default_white"><b><u>C</u>ollection:</b></td>
          <td width="60%">
            <select accessKey="c" name="collection" class="default" tabindex="0">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['active_collections']), $this);?>

            </select>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="default_white" align="center">
            <label for="remember" accesskey="r"></label>
            <input type="checkbox" id="remember" name="remember" value="1"> <b><a id="white_link" class="white_link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('login_form', 'remember');"><u>R</u>emember Selection</a></b>
          </td>
        </tr>
        <tr align="center">
          <td colspan="2">
            <input type="submit" name="Submit" value="Continue &gt;&gt;" class="button">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>