<?php /* Smarty version 2.6.2, created on 2004-07-02 06:00:50
         compiled from view_note.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'view_note.tpl.html', 68, false),array('modifier', 'default', 'view_note.tpl.html', 76, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array('extra_title' => $this->_tpl_vars['extra_title'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<script language="JavaScript">
<!--
function reply(id, issue_id)
{
    window.location.href = \'post_note.php?cat=reply&id=\' + id + \'&issue_id=\' + issue_id;
}
function openRawHeaders()
{
'; ?>

    var features = 'width=740,height=580,top=60,left=60,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var headersWin = window.open('view_headers.php?cat=note&id=<?php echo $_GET['id']; ?>
', '_headers', features);
    headersWin.focus();
<?php echo '
}
function viewNote(id)
{
    window.location.href = \'view_note.php?id=\' + id;
}
//-->
</script>
'; ?>

<form method="post" action="popup.php">
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>View Note Details<?php if ($this->_tpl_vars['issue_id']): ?> (Associated with Issue #<?php echo $this->_tpl_vars['issue_id']; ?>
)<?php endif; ?></b>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['next'] != "" || $this->_tpl_vars['previous'] != ""): ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center">
            <table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr>
                <td>
                  <?php if ($this->_tpl_vars['previous'] != ""): ?>
                  <input class="button" type="button" value="&lt;&lt; Previous Note" onClick="javascript:viewNote(<?php echo $this->_tpl_vars['previous']; ?>
);">
                  <?php endif; ?>
                </td>
                <td align="right">
                  <?php if ($this->_tpl_vars['next'] != ""): ?>
                  <input class="button" type="button" value="Next Note &gt;&gt;" onClick="javascript:viewNote(<?php echo $this->_tpl_vars['next']; ?>
);">
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>Posted Date:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo $this->_tpl_vars['note']['not_created_date']; ?>

          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>From:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['note']['not_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>Title:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['note']['not_title'])) ? $this->_run_mod_handler('default', true, $_tmp, "<Empty Title>") : smarty_modifier_default($_tmp, "<Empty Title>")))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

          </td>
        </tr>
        <?php if ($this->_tpl_vars['note']['attachments']): ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white">
            <b>Attachments:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" class="default">
            <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['note']['attachments']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
            <a title="download file" class="link" href="get_attachment.php?cat=blocked_email&note_id=<?php echo $this->_tpl_vars['note']['not_id']; ?>
&filename=<?php echo $this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['filename'];  if ($this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['cid']): ?>&cid=<?php echo ((is_array($_tmp=$this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['cid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/attachment.gif" border="0"></a>
            <a title="download file" class="link" href="get_attachment.php?cat=blocked_email&note_id=<?php echo $this->_tpl_vars['note']['not_id']; ?>
&filename=<?php echo $this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['filename'];  if ($this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['cid']): ?>&cid=<?php echo ((is_array($_tmp=$this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['cid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>"><?php echo $this->_tpl_vars['note']['attachments'][$this->_sections['i']['index']]['filename']; ?>
</a><br />
            <?php endfor; endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
            <table width="100%">
              <tr>
                <td>
                  <span class="default_white"><b>Message:</b></span>
                  <span class="small_default_white">(<a class="white_link" href="javascript:void(null);" onClick="javascript:displayFixedWidth('email_message');">display in fixed width font</a>)</span>
                </td>
                <td align="right" class="default_white">
                  <?php if ($this->_tpl_vars['note']['has_blocked_message']): ?>
                  <a class="white_link" href="javascript:void(null);" onClick="javascript:openRawHeaders();">Blocked Message Raw Headers</a>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" id="email_message" class="default">
<?php echo $this->_tpl_vars['note']['message']; ?>

          </td>
        </tr>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center">
            <input class="button" type="button" value="Reply" onClick="javascript:reply(<?php echo $_GET['id']; ?>
, <?php echo $this->_tpl_vars['issue_id']; ?>
);">&nbsp;&nbsp;
            <input class="button" type="button" value="Close" onClick="javascript:window.close();">
          </td>
        </tr>
        <?php if ($this->_tpl_vars['next'] != "" || $this->_tpl_vars['previous'] != ""): ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center">
            <table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr>
                <td>
                  <?php if ($this->_tpl_vars['previous'] != ""): ?>
                  <input class="button" type="button" value="&lt;&lt; Previous Note" onClick="javascript:viewNote(<?php echo $this->_tpl_vars['previous']; ?>
);">
                  <?php endif; ?>
                </td>
                <td align="right">
                  <?php if ($this->_tpl_vars['next'] != ""): ?>
                  <input class="button" type="button" value="Next Note &gt;&gt;" onClick="javascript:viewNote(<?php echo $this->_tpl_vars['next']; ?>
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