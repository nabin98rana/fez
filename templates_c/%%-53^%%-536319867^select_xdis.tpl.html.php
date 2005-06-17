<?php /* Smarty version 2.6.2, created on 2005-05-24 11:23:20
         compiled from select_xdis.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'select_xdis.tpl.html', 40, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => 'Select Document Type')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br /><br />
<?php echo '
<script language="JavaScript">
<!--
function validateForm(f)
{
    if (!hasOneSelected(f, \'collection_doc_type\')) {
        alert(\'Please choose the document type.\');
        selectField(f, \'collection_doc_type\');
        return false;
    }
    return true;
}
//-->
</script>
'; ?>

<form name="document_type_selection_form" onSubmit="javascript:return validateForm(this);" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<input type="hidden" name="cat" value="select">
<input type="hidden" name="pid" value="<?php echo $this->_tpl_vars['pid']; ?>
">
<input type="hidden" name="collection_pid" value="<?php echo $this->_tpl_vars['collection_pid']; ?>
">
<input type="hidden" name="community_pid" value="<?php echo $this->_tpl_vars['community_pid']; ?>
">
<input type="hidden" name="return" value="<?php echo $this->_tpl_vars['return']; ?>
">
<input type="hidden" name="url" value="<?php echo $_GET['url']; ?>
">
<table align="center" width="400" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td>
      <table bgcolor="#006486" width="100%" border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td colspan="2" align="center">
            <h4 style="color: white;">Select Document Type</h4>
            <hr size="1" noshade color="#000000">
          </td>
        </tr>
        <tr>
          <td align="right" width="40%" class="default_white"><b><u>D</u>ocument Type:</b></td>
          <td width="60%">
            <select accessKey="d" name="collection_doc_type" class="default" tabindex="0">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['collection_doc_types']), $this);?>

            </select>
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