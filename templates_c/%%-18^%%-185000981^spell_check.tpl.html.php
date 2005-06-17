<?php /* Smarty version 2.6.2, created on 2004-07-19 15:21:08
         compiled from spell_check.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'spell_check.tpl.html', 111, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['show_temp_form'] == 'yes'): ?>
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<br /><br />&nbsp;
<form action="<?php echo $_SERVER['PHP_SELF']; ?>
" method="post">
<input type="hidden" name="form_name" value="<?php echo $_GET['form_name']; ?>
">
<input type="hidden" name="field_name" value="<?php echo $_GET['field_name']; ?>
">
<textarea rows="0" cols="0" name="textarea"></textarea>
</form>
<script language="JavaScript">
<!--
var textarea = window.opener.<?php echo $_GET['form_name']; ?>
.<?php echo $_GET['field_name']; ?>
;
document.forms[0].textarea.value = textarea.value;
document.forms[0].submit();
//-->
</script>
<?php else: ?>
<?php echo '
<script language="JavaScript">
<!--
function fixSpelling()
{
'; ?>

    var f = document.forms[0];
    var old_value = f.misspelled_words.options[f.misspelled_words.selectedIndex].text;
    var new_value = f.suggestion.options[f.suggestion.selectedIndex].text;
    var textarea = window.opener.<?php echo $_POST['form_name']; ?>
.<?php echo $_POST['field_name']; ?>
;
    textarea.value = replaceWords(textarea.value, old_value, new_value);
<?php echo '
}
function buildSuggestionBox()
{
'; ?>

    var suggestions = new Array();
    <?php if (count($_from = (array)$this->_tpl_vars['spell_check']['suggestions'])):
    foreach ($_from as $this->_tpl_vars['word'] => $this->_tpl_vars['suggestions']):
?>
    suggestions.push(new Option('<?php echo $this->_tpl_vars['word']; ?>
', '<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['suggestions']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
 echo $this->_tpl_vars['suggestions'][$this->_sections['i']['index']];  if (! $this->_sections['i']['last']): ?>,<?php endif;  endfor; endif; ?>'));
    <?php endforeach; unset($_from); endif; ?>
<?php echo '
    var f = document.forms[0];
//    var word = f.misspelled_words.options[f.misspelled_words.selectedIndex].text; // removed for testing CK
    var word = f.misspelled_words.options[f.misspelled_words.selectedIndex].value;
    for (var i = 0; i < suggestions.length; i++) {
        if (suggestions[i].text == word) {
            var _suggestions = suggestions[i].value;
            break;
        }
    }
    f.suggestion.length = 0;
    _suggestions = _suggestions.split(\',\');
    for (var i = 0; i < _suggestions.length; i++) {
        f.suggestion.options[f.suggestion.options.length] = new Option(_suggestions[i], _suggestions[i]);
    }
}
//-->
</script>
'; ?>

<form>
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Spell Check</b>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['spell_check']['total_words'] == 0): ?>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default" align="center">
            <b>No spelling mistakes could be found.</b>
          </td>
        </tr>
        <?php else: ?>
        <tr>
          <td width="130" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Misspelled Words:</b>
          </td>
          <td width="60%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="misspelled_words" onChange="javascript:buildSuggestionBox();">
            <?php echo smarty_function_html_options(array('output' => $this->_tpl_vars['spell_check']['words']), $this);?>

            </select>
          </td>
        </tr>
        <tr>
          <td width="130" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Suggestions:</b>
          </td>
          <td width="60%" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <select class="default" name="suggestion">
              <option>Choose a misspelled word</option>
            </select>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <?php if ($this->_tpl_vars['spell_check']['total_words'] > 0): ?><input class="button" type="button" value="Fix Spelling" onClick="javascript:fixSpelling();">&nbsp;&nbsp;<?php endif; ?>
            <input class="button" type="button" value="Close" onClick="javascript:window.close();">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php if ($this->_tpl_vars['spell_check']['total_words'] > 0): ?>
<script language="JavaScript">
<!--
buildSuggestionBox();
//-->
</script>
<?php endif; ?>
<?php endif; ?>

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