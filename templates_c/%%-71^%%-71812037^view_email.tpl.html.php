<?php /* Smarty version 2.6.2, created on 2004-06-28 05:05:22
         compiled from view_email.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'view_email.tpl.html', 104, false),array('function', 'html_options', 'view_email.tpl.html', 182, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => $this->_tpl_vars['extra_title'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script language="JavaScript">
<!--
var issue_id = '<?php echo $this->_tpl_vars['issue_id']; ?>
';
<?php echo '
function reply(ema_id, id)
{
    if (issue_id != \'\') {
        window.location.href = \'send.php?issue_id=\' + issue_id + \'&ema_id=\' + ema_id + \'&id=\' + id;
    } else {
        window.location.href = \'send.php?ema_id=\' + ema_id + \'&id=\' + id;
    }
}
function loadReport(id)
{
'; ?>

    document.writeln('<link rel="stylesheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/style.css" type="text/css">');
    document.writeln('<span class="default"><b>Please wait a few moments.</b></span><br />');
    document.writeln('<br /><span class="default"><b>Re-directing the parent window to the issue report page. This window will be closed automatically.</b></span>');
    window.opener.location.href = 'new.php?cat=associate&item[]=' + id;
    window.setTimeout('window.close()', 2000);
<?php echo '
}
function associate(f)
{
    var field = getFormElement(f, \'issue\');
    if (field.options[field.selectedIndex].value == \'new\') {
'; ?>

        loadReport(<?php echo $_GET['id']; ?>
);
<?php echo '
    } else {
        var hidden_field = getFormElement(f, \'issue\');
        hidden_field.value = field.options[field.selectedIndex].value;
        f.submit();
    }
}
function openRawHeaders()
{
'; ?>

    var features = 'width=740,height=580,top=60,left=60,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var headersWin = window.open('view_headers.php?id=<?php echo $_GET['id']; ?>
', '_headers', features);
    headersWin.focus();
<?php echo '
}
function viewEmail(ema_id, id)
{
'; ?>

<?php if ($_GET['cat'] == 'list_emails'): ?>
    window.location.href = 'view_email.php?cat=list_emails&ema_id=' + ema_id + '&id=' + id;
<?php else: ?>
    window.location.href = 'view_email.php?issue_id=' + issue_id + '&ema_id=' + ema_id + '&id=' + id;
<?php endif; ?>
<?php echo '
}
//-->
</script>
'; ?>

<form method="post" action="popup.php">
<input type="hidden" name="cat" value="associate">
<input type="hidden" name="item[]" value="<?php echo $_GET['id']; ?>
">
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>View Email Details<?php if ($this->_tpl_vars['issue_id']): ?> (Associated with Issue #<?php echo $this->_tpl_vars['issue_id']; ?>
)<?php endif; ?></b>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['next']['sup_id'] != "" || $this->_tpl_vars['previous']['sup_id'] != ""): ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr>
                <td>
                  <?php if ($this->_tpl_vars['previous']['sup_id'] != ""): ?>
                  <input class="button" type="button" value="&lt;&lt; Previous Message" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['previous']['ema_id']; ?>
, <?php echo $this->_tpl_vars['previous']['sup_id']; ?>
);">
                  <?php endif; ?>
                </td>
                <td align="right">
                  <?php if ($this->_tpl_vars['next']['sup_id'] != ""): ?>
                  <input class="button" type="button" value="Next Message &gt;&gt;" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['next']['ema_id']; ?>
, <?php echo $this->_tpl_vars['next']['sup_id']; ?>
);">
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Received:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo $this->_tpl_vars['email']['sup_date']; ?>

          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>From:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>To:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php if ($this->_tpl_vars['email']['sup_to'] == ""): ?>
            <i>sent to notification list</i>
            <?php else: ?>
            <?php echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Cc:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_cc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Subject:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <?php if ($this->_tpl_vars['email']['attachments']): ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Attachments:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="default">
            <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['email']['attachments']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?>
            <a title="download file" class="link" href="get_attachment.php?sup_id=<?php echo $this->_tpl_vars['email']['sup_id']; ?>
&filename=<?php echo $this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['filename'];  if ($this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['cid']): ?>&cid=<?php echo ((is_array($_tmp=$this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['cid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/attachment.gif" border="0"></a>
            <a title="download file" class="link" href="get_attachment.php?sup_id=<?php echo $this->_tpl_vars['email']['sup_id']; ?>
&filename=<?php echo $this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['filename'];  if ($this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['cid']): ?>&cid=<?php echo ((is_array($_tmp=$this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['cid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>"><?php echo $this->_tpl_vars['email']['attachments'][$this->_sections['i']['index']]['filename']; ?>
</a><br />
            <?php endfor; endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
            <table width="100%">
              <tr>
                <td>
                  <span class="default_white"><b>Message:</b></span>
                  <span class="small_default_white">(<a class="white_link" href="javascript:void(null);" onClick="javascript:displayFixedWidth('email_message');">display in fixed width font</a>)</span>
                </td>
                <td align="right" class="default_white">
                  <a class="white_link" href="javascript:void(null);" onClick="javascript:openRawHeaders();">Raw Headers</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" id="email_message" class="default">
<?php echo $this->_tpl_vars['email']['message']; ?>

          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr>
                <td>
                  <input class="button" type="button" value="Reply" onClick="javascript:reply(<?php echo $_GET['ema_id']; ?>
, <?php echo $_GET['id']; ?>
);">
                  <input class="button" type="button" value="Close" onClick="javascript:window.close();">
                </td>
                <?php if ($this->_tpl_vars['email']['sup_iss_id'] == 0): ?>
                <td align="right">
                  <!-- XXX: fix this later
                  <input type="submit" value="Associate &gt;" class="button" onClick="javascript:associate(this.form);">
                  <select name="issue" class="default">
                    <option value="new">New Issue</option>
                    <?php echo smarty_function_html_options(array('output' => $this->_tpl_vars['issues'],'values' => $this->_tpl_vars['issues']), $this);?>

                  </select>
                  -->
                </td>
                <?php endif; ?>
              </tr>
            </table>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['next']['sup_id'] != "" || $this->_tpl_vars['previous']['sup_id'] != ""): ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" align="center">
            <table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr>
                <td>
                  <?php if ($this->_tpl_vars['previous']['sup_id'] != ""): ?>
                  <input class="button" type="button" value="&lt;&lt; Previous Message" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['previous']['ema_id']; ?>
, <?php echo $this->_tpl_vars['previous']['sup_id']; ?>
);">
                  <?php endif; ?>
                </td>
                <td align="right">
                  <?php if ($this->_tpl_vars['next']['sup_id'] != ""): ?>
                  <input class="button" type="button" value="Next Message &gt;&gt;" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['next']['ema_id']; ?>
, <?php echo $this->_tpl_vars['next']['sup_id']; ?>
);">
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</table>
</form>


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