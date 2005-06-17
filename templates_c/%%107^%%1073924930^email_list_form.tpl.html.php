<?php /* Smarty version 2.6.2, created on 2004-07-23 01:16:01
         compiled from email_list_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'email_list_form.tpl.html', 117, false),array('function', 'html_options', 'email_list_form.tpl.html', 185, false),array('modifier', 'escape', 'email_list_form.tpl.html', 150, false),array('modifier', 'replace', 'email_list_form.tpl.html', 152, false),array('modifier', 'default', 'email_list_form.tpl.html', 161, false),)), $this); ?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/overlib_mini.js"></script>
<script language="JavaScript">
<!--
var page_url = '<?php echo $_SERVER['PHP_SELF']; ?>
';
var current_page = <?php echo $this->_tpl_vars['list_info']['current_page']; ?>
;
var last_page = <?php echo $this->_tpl_vars['list_info']['last_page']; ?>
;
<?php echo '
function viewEmail(account_id, email_id)
{
    var features = \'width=740,height=580,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var emailWin = window.open(\'view_email.php?cat=list_emails&ema_id=\' + account_id + \'&id=\' + email_id, \'_email\', features);
    emailWin.focus();
}
function goPage(f, new_page)
{
    if ((new_page > last_page+1) || (new_page <= 0) ||
            (new_page == current_page+1) || (!isNumberOnly(new_page))) {
        f.page.value = current_page+1;
        return false;
    }
    setPage(new_page-1);
}
function setPage(new_page)
{
    if ((new_page > last_page) || (new_page < 0) ||
            (new_page == current_page)) {
        return false;
    }
    window.location.href = page_url + "?" + replaceParam(window.location.href, \'pagerRow\', new_page);
}
function hideAssociated(f)
{
    if (f.hide_associated.checked) {
        window.location.href = page_url + "?" + replaceParam(window.location.href, \'hide_associated\', \'1\');
    } else {
        window.location.href = page_url + "?" + replaceParam(window.location.href, \'hide_associated\', \'0\');
    }
}
function resizePager(f)
{
    var pagesize = f.page_size.options[f.page_size.selectedIndex].value;
    window.location.href = page_url + "?" + replaceParam(window.location.href, \'rows\', pagesize);
}
window.onload = disableFields;
function disableFields()
{
    var f = document.email_list_form;
    if (current_page == 0) {
        f.first.disabled = true;
        f.previous.disabled = true;
    }
    if (current_page == last_page) {
        f.next.disabled = true;
        f.last.disabled = true;
    }
    if ((current_page == 0) && (current_page == last_page)) {
        f.page.disabled = true;
        f.go.disabled = true;
    }
}
function openRemovedList()
{
    var features = \'width=560,height=460,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var removedEmailWin = window.open(\'removed_emails.php\', \'_removedEmail\', features);
    removedEmailWin.focus();
}
function associateEmails(f)
{
    if (!hasOneChecked(f, \'item[]\')) {
        alert(\'Please choose which emails need to be associated.\');
        return false;
    }
    if (f.issue.options[f.issue.selectedIndex].value == \'support\') {
        f.target = \'\';
        f.action = \'ticket.php\';
    } else if (f.issue.options[f.issue.selectedIndex].value == \'new\') {
        f.target = \'\';
        f.action = \'new.php\';
    } else {
        var features = \'width=420,height=400,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'\', \'_popup\', features);
        popupWin.focus();
    }
    f.submit();
}
function removeEmails(f)
{
    if (!hasOneChecked(f, \'item[]\')) {
        alert(\'Please choose which emails need to be marked as deleted.\');
        return false;
    }
    if (!confirm(\'This action will mark the selected email messages as deleted.\')) {
        return false;
    } else {
        var features = \'width=420,height=400,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'\', \'_popup\', features);
        f.cat.value = \'remove_email\';
        f.method = \'post\';
        f.action = \'popup.php\';
        f.submit();
        popupWin.focus();
    }
}
//-->
</script>
'; ?>

<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="email_list_form" target="_popup" method="get" action="associate.php">
<input type="hidden" name="cat" value="associate">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="7" class="default">
            <b>Viewing Emails (<?php echo $this->_tpl_vars['list_info']['total_rows']; ?>
 emails found<?php if ($this->_tpl_vars['list_info']['end_offset'] > 0): ?>, <?php echo smarty_function_math(array('equation' => "x + 1",'x' => $this->_tpl_vars['list_info']['start_offset']), $this);?>
 - <?php echo $this->_tpl_vars['list_info']['end_offset']; ?>
 shown<?php endif; ?>)</b>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "help_link.tpl.html", 'smarty_include_vars' => array('topic' => 'support_emails')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td width="1%"><?php if ($this->_tpl_vars['list']): ?><input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');"><?php endif; ?></td>
          <td align="center" class="default_white">
            <a title="sort by sender" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_from']; ?>
" class="white_link">Sender</a>
            <?php if ($this->_tpl_vars['sorting']['images']['sup_from'] != ""): ?><a title="sort by sender" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_from']; ?>
" class="white_link"><img border="0" src="<?php echo $this->_tpl_vars['sorting']['images']['sup_from']; ?>
"></a><?php endif; ?>
          </td>
          <td align="center" class="default_white">
            <a title="sort by date" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_date']; ?>
" class="white_link">Date</a>
            <?php if ($this->_tpl_vars['sorting']['images']['sup_date'] != ""): ?><a title="sort by date" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_date']; ?>
" class="white_link"><img border="0" src="<?php echo $this->_tpl_vars['sorting']['images']['sup_date']; ?>
"></a><?php endif; ?>
          </td>
          <td align="center" class="default_white">
            <a title="sort by recipient" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_to']; ?>
" class="white_link">To</a>
            <?php if ($this->_tpl_vars['sorting']['images']['sup_to'] != ""): ?><a title="sort by recipient" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_to']; ?>
" class="white_link"><img border="0" src="<?php echo $this->_tpl_vars['sorting']['images']['sup_to']; ?>
"></a><?php endif; ?>
          </td>
          <td align="center" class="default_white" nowrap>
            <a title="sort by status" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_iss_id']; ?>
" class="white_link">Status</a>
            <?php if ($this->_tpl_vars['sorting']['images']['sup_iss_id'] != ""): ?><a title="sort by status" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_iss_id']; ?>
" class="white_link"><img border="0" src="<?php echo $this->_tpl_vars['sorting']['images']['sup_iss_id']; ?>
"></a><?php endif; ?>
          </td>
          <td class="default_white" width="45%">
            &nbsp;<a title="sort by subject" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_subject']; ?>
" class="white_link">Subject</a>
            <?php if ($this->_tpl_vars['sorting']['images']['sup_subject'] != ""): ?><a title="sort by subject" href="<?php echo $this->_tpl_vars['sorting']['links']['sup_subject']; ?>
" class="white_link"><img border="0" src="<?php echo $this->_tpl_vars['sorting']['images']['sup_subject']; ?>
"></a><?php endif; ?>
          </td>
          <td class="default_white" nowrap>
            &nbsp;Spam Score
          </td>
        </tr>
        <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
        <tr bgcolor="<?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_iss_id'] != 0):  echo $this->_tpl_vars['light_color'];  else: ?>#99CCFF<?php endif; ?>">
          <td align="center" width="1%" class="default"><input type="checkbox" name="item[]" value="<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_id']; ?>
" <?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_iss_id'] != 0): ?>disabled<?php endif; ?>></td>
          <td class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
          <td align="center" class="default" nowrap><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_date']; ?>
</td>
          <td class="default"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('replace', true, $_tmp, ",", ' ') : smarty_modifier_replace($_tmp, ",", ' ')); ?>
</td>
          <td align="center" class="default" nowrap>
            <?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_iss_id'] != 0): ?>
            associated (<a class="link" title="view issue details" href="view.php?id=<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_iss_id']; ?>
"><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_iss_id']; ?>
</a>)
            <?php else: ?>
            <b>pending</b>
            <?php endif; ?>
          </td>
          <td class="default" width="45%">
            &nbsp;<a href="javascript:void(null);" title="view email details" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_ema_id']; ?>
, <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_id']; ?>
);" class="link"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_subject'])) ? $this->_run_mod_handler('default', true, $_tmp, "<Empty Subject Header>") : smarty_modifier_default($_tmp, "<Empty Subject Header>")))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
            <?php if ($this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_has_attachment']): ?>
            <a href="javascript:void(null);" title="view email details" onClick="javascript:viewEmail(<?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_ema_id']; ?>
, <?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_id']; ?>
);" class="link"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/attachment.gif" border="0"></a
            <?php endif; ?>
         </td>
          <td align="center" class="default" nowrap><?php echo $this->_tpl_vars['list'][$this->_sections['i']['index']]['sup_spam_score']; ?>
</td>
        </tr>
        <?php endfor; else: ?>
        <tr bgcolor="gray">
          <td colspan="7" class="default_white" align="center">
            <i>No emails could be found.</i>
          </td>
        </tr>
        <?php endif; ?>
        <tr bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
          <td colspan="7">
            <table width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="35%">
                  <?php if ($this->_tpl_vars['list']): ?>
                  <input type="button" value="All" class="shortcut" onClick="javascript:toggleSelectAll(this.form, 'item[]');">
                  <input type="button" value="Associate &gt;" class="shortcut" onClick="javascript:associateEmails(this.form);">
                  <select name="issue" class="default">
                    <option value="new">New Issue</option>
                    <?php echo smarty_function_html_options(array('output' => $this->_tpl_vars['issues'],'values' => $this->_tpl_vars['issues']), $this);?>

                  </select>
                  <?php if (! ( $this->_tpl_vars['os']['mac'] && $this->_tpl_vars['browser']['ie'] )): ?><a title="lookup issues by their summaries" href="javascript:void(null);" onClick="return overlib(getOverlibContents('<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_layer.tpl.html", 'smarty_include_vars' => array('list' => $this->_tpl_vars['assoc_issues'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>', 'email_list_form', 'issue'), STICKY, HEIGHT, 50, WIDTH, 160, BELOW, RIGHT, CLOSECOLOR, '#FFFFFF', FGCOLOR, '#FFFFFF', BGCOLOR, '#333333', CAPTION, 'Lookup Details', CLOSECLICK);" onMouseOut="javascript:nd();"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" border="0"></a><?php endif; ?>
                  <?php endif; ?>
                </td>
                <td width="40%" align="center">
                  <input name="first" type="button" value="|&lt;" class="shortcut" onClick="javascript:setPage(0);">
                  <input name="previous" type="button" value="&lt;&lt;" class="shortcut" onClick="javascript:setPage(<?php echo $this->_tpl_vars['list_info']['previous_page']; ?>
);">
                  <input type="text" name="page" size="3" maxlength="3" value="<?php echo smarty_function_math(array('equation' => "x + 1",'x' => $this->_tpl_vars['list_info']['current_page']), $this);?>
" style="background: <?php echo $this->_tpl_vars['cell_color']; ?>
;" class="paging_input">
                  <input name="go" type="button" value="Go" class="shortcut" onClick="javascript:goPage(this.form, this.form.page.value);">
                  <input name="next" type="button" value="&gt;&gt;" class="shortcut" onClick="javascript:setPage(<?php echo $this->_tpl_vars['list_info']['next_page']; ?>
);">
                  <input name="last" type="button" value="&gt;|" class="shortcut" onClick="javascript:setPage(<?php echo $this->_tpl_vars['list_info']['last_page']; ?>
);">
                </td>
                <td nowrap align="center">
                  <span class="default_white">Rows:</span>
                  <select name="page_size" class="default" onChange="javascript:resizePager(this.form);">
                    <option value="5" <?php if ($this->_tpl_vars['options']['rows'] == 5): ?>selected<?php endif; ?>>5</option>
                    <option value="10" <?php if ($this->_tpl_vars['options']['rows'] == 10): ?>selected<?php endif; ?>>10</option>
                    <option value="25" <?php if ($this->_tpl_vars['options']['rows'] == 25): ?>selected<?php endif; ?>>25</option>
                    <option value="50" <?php if ($this->_tpl_vars['options']['rows'] == 50): ?>selected<?php endif; ?>>50</option>
                    <option value="100" <?php if ($this->_tpl_vars['options']['rows'] == 100): ?>selected<?php endif; ?>>100</option>
                    <option value="ALL" <?php if ($this->_tpl_vars['options']['rows'] == 'ALL'): ?>selected<?php endif; ?>>ALL</option>
                  </select>
                  <input type="button" value="Set" class="shortcut" onClick="javascript:resizePager(this.form);">
                </td>
                <td width="25%" class="default_white" align="right">
                  <input type="checkbox" id="hide_associated" name="hide_associated" <?php if ($this->_tpl_vars['options']['hide_associated']): ?>checked<?php endif; ?> onClick="javascript:hideAssociated(this.form);"> <label for="hide_associated">Hide Associated Emails</label>&nbsp;
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
">
          <td colspan="7">
            <table width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="3">
                  <?php if ($this->_tpl_vars['list']): ?>
                  <input type="button" value="Remove Selected Emails" class="shortcut" onClick="javascript:removeEmails(this.form);">
                  <?php endif; ?>
                </td>
                <td align="right" class="default">
                  <a title="list all removed emails" class="link" href="javascript:void(null);" onClick="javascript:openRemovedList();">List Removed Emails</a>&nbsp;
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</form>
</table>
