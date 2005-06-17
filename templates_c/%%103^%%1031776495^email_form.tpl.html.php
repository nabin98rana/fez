<?php /* Smarty version 2.6.2, created on 2004-11-03 05:54:20
         compiled from email_form.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'email_form.tpl.html', 58, false),array('modifier', 'escape', 'email_form.tpl.html', 211, false),array('function', 'html_options', 'email_form.tpl.html', 263, false),)), $this); ?>

<?php if ($this->_tpl_vars['send_result'] != '' && $_POST['form_stays'] != 1): ?>
<br />
<center>
  <span class="default">
<?php if ($this->_tpl_vars['send_result'] == -1): ?>
  <b>An error occurred while trying to run your query</b>
<?php elseif ($this->_tpl_vars['send_result'] == -2): ?>
  <b>Sorry, but the email could not be queued. This might be related to problems with your SMTP account settings. 
  Please contact the administrator of this application for further assistance.</b>
<?php elseif ($this->_tpl_vars['send_result'] == 1): ?>
  <b>Thank you, the email was queued to be sent successfully.</b>
<?php endif; ?>
  </span>
</center>
<script language="JavaScript">
<!--
<?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
setTimeout('closeAndRefresh()', 2000);
<?php endif; ?>
//-->
</script>
<br />
<?php if (! $this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
  <center>
    <span class="default"><a class="link" href="javascript:void(null);" onClick="javascript:closeAndRefresh();">Continue</a></span>
  </center>
<?php endif; ?>
<?php elseif ($this->_tpl_vars['draft_result'] != ''): ?>
<br />
<center>
  <span class="default">
<?php if ($this->_tpl_vars['draft_result'] == -1): ?>
  <b>An error occurred while trying to run your query</b>
<?php elseif ($this->_tpl_vars['draft_result'] == 1): ?>
  <b>Thank you, the email message was saved as a draft successfully.</b>
<?php endif; ?>
  </span>
</center>
<script language="JavaScript">
<!--
<?php if ($this->_tpl_vars['current_user_prefs']['close_popup_windows'] == '1'): ?>
setTimeout('closeAndRefresh()', 2000);
<?php endif; ?>
//-->
</script>
<br />
<?php if (! $this->_tpl_vars['current_user_prefs']['close_popup_windows']): ?>
  <center>
    <span class="default"><a class="link" href="javascript:void(null);" onClick="javascript:closeAndRefresh();">Continue</a></span>
  </center>
<?php endif; ?>
<?php else: ?>
<script language="JavaScript">
<!--
var contact_list = new Array();
<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['assoc_emails']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
contact_list[contact_list.length] = '<?php echo ((is_array($_tmp=$this->_tpl_vars['assoc_emails'][$this->_sections['i']['index']])) ? $this->_run_mod_handler('replace', true, $_tmp, "'", "\\'") : smarty_modifier_replace($_tmp, "'", "\\'")); ?>
';
<?php endfor; endif; ?>

var email_responses = new Array();
<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['js_canned_responses']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
email_responses[<?php echo $this->_tpl_vars['js_canned_responses'][$this->_sections['i']['index']]['ere_id']; ?>
] = "<?php echo $this->_tpl_vars['js_canned_responses'][$this->_sections['i']['index']]['ere_response_body']; ?>
";
<?php endfor; endif; ?>
<?php echo '

function validate(f)
{
	if (f.to != null) {
		if (isWhitespace(f.to.value)) {
			alert(\'Please enter the recipient of this email.\');
			selectField(f, \'to\');
			return false;
		} else {
			var emailCheck = true;
			var tempStr = f.to.value;
			var sArray = tempStr.split(",");
			for (var x=0; x < sArray.length; x++) {
				emailCheck = isValidEmail(sArray[x]);
				if (!emailCheck)  {					
					alert(\'Email Address: \'+sArray[x]+\' is not a valid email address!\');
					return false;
				}
			}

		}
	} else {
		alert(\'Please enter the recipient of this email.\');
		return false;
	}

	if (f.cc != null) {
		if (isWhitespace(f.cc.value)) {
			//nothing
		} else {
			var emailCheck = true;
			var tempStr = f.cc.value;
			var sArray = tempStr.split(",");
			for (var x=0; x < sArray.length; x++) {
				emailCheck = isValidEmail(sArray[x]);
				if (!emailCheck)  {
					alert(\'Email Address: \'+sArray[x]+\' is not a valid email address!\');
					return false;
				}
			}

		}
	}


    if (isWhitespace(f.subject.value)) {
        alert(\'Please enter the subject of this email.\');
        selectField(f, \'subject\');
        return false;
    }
    if (isWhitespace(f.message.value)) {
        alert(\'Please enter the message body of this email.\');
        selectField(f, \'message\');
        return false;
    }
    return true;
}
function setResponseBody(f)
{
    var response_id = getSelectedOption(f, \'email_response\');
    if (email_responses[response_id]) {
        f.message.value = email_responses[response_id];
    }
}
function saveDraft(f)
{
    f.cat.value = \'save_draft\';
    f.submit();
}
function updateDraft(f)
{
    f.cat.value = \'update_draft\';
    f.submit();
}
var old_message = \'\';
function setSignature(f)
{
'; ?>

    var signature = "<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['current_user_prefs']['email_signature'])) ? $this->_run_mod_handler('replace', true, $_tmp, '"', '\"') : smarty_modifier_replace($_tmp, '"', '\"')))) ? $this->_run_mod_handler('replace', true, $_tmp, "\r", "") : smarty_modifier_replace($_tmp, "\r", "")))) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", '\n') : smarty_modifier_replace($_tmp, "\n", '\n')); ?>
";
<?php echo '
    if (f.add_email_signature.checked) {
        old_message = f.message.value;
        f.message.value += "\\n";
        f.message.value += signature;
    } else {
        f.message.value = old_message;
    }
} 
//-->
</script>
'; ?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="js/overlib_mini.js"></script>
<script language="JavaScript" src="js/autocomplete.js"></script>
<form onSubmit="javascript:return validate(this);" name="send_email_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>
">
<input type="hidden" name="cat" value="send_email">
<input type="hidden" name="parent_id" value="<?php echo $this->_tpl_vars['parent_email_id']; ?>
">
<input type="hidden" name="ema_id" value="<?php echo $this->_tpl_vars['ema_id']; ?>
">
<input type="hidden" name="issue_id" value="<?php echo $this->_tpl_vars['issue_id']; ?>
">
<?php if ($_GET['cat'] == 'view_draft'): ?>
<input type="hidden" name="draft_id" value="<?php echo $this->_tpl_vars['draft_id']; ?>
">
<?php endif; ?>
<?php if ($this->_tpl_vars['issue_lock_usr_id'] && $this->_tpl_vars['issue_lock_usr_id'] != $this->_tpl_vars['current_user_id']): ?>
<br />
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/icons/error.gif" hspace="2" vspace="2" border="0" align="left"></td>
          <td width="100%" class="default"><span style="font-weight: bold; font-size: 160%; color: red;">This Issue is Currently Locked By <?php echo $this->_tpl_vars['lock_usr_full_name']; ?>
</span></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php endif; ?>
<table align="center" width="100%" cellpadding="3">
  <tr>
    <td>
      <table width="100%" cellspacing="1" cellpadding="2" border="0">
        <tr>
          <td colspan="2" class="default">
            <b>Send Email</b>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['send_result'] != ""): ?>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" colspan="2" class="error" align="center">
            <?php if ($this->_tpl_vars['send_result'] == -1): ?>
              <b>An error occurred while trying to run your query</b>
            <?php elseif ($this->_tpl_vars['send_result'] == -2): ?>
              <b>Sorry, but the email could not be sent. This might be related to problems with your SMTP account settings. 
              Please contact the administrator of this application for assistance.</b>
            <?php elseif ($this->_tpl_vars['send_result'] == 1): ?>
              <b>Thank you, the email was sent successfully.</b>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>From:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="hidden" name="from" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
            <b><?php echo ((is_array($_tmp=$this->_tpl_vars['from'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</b>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>To: *</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">

                <input type="text" name="to" class="default" size="80" value="<?php if ($_GET['cat'] != 'forward'):  echo ((is_array($_tmp=$this->_tpl_vars['subscribedEmails'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  else:  echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_to'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>" onKeyUp="javascript:autoComplete(this, contact_list);">

                <?php if (! ( $this->_tpl_vars['os']['mac'] || $this->_tpl_vars['browser']['ie'] )): ?><a href="javascript:void(null);" onClick="return overlib(getFillInput('<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_layer.tpl.html", 'smarty_include_vars' => array('list' => $this->_tpl_vars['assoc_users'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>', 'send_email_form', 'to'), STICKY, HEIGHT, 50, WIDTH, 160, BELOW, LEFT, CLOSECOLOR, '#FFFFFF', FGCOLOR, '#FFFFFF', BGCOLOR, '#333333', CAPTION, 'Lookup Details', CLOSECLICK);" onMouseOut="javascript:nd();"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" border="0"></a><?php endif; ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'to')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!--                <span class="default">Issue #<?php echo $this->_tpl_vars['issue_id']; ?>
 Notification List</span> -->

           </td>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Cc:</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="cc" class="default" size="80" value="<?php if ($_GET['cat'] == 'forward'):  echo ((is_array($_tmp=$this->_tpl_vars['subscribedEmails'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  else:  echo ((is_array($_tmp=$this->_tpl_vars['email']['cc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>">
            <?php if (! ( $this->_tpl_vars['os']['mac'] || $this->_tpl_vars['browser']['ie'] )): ?><a href="javascript:void(null);" onClick="return overlib(getFillInput('<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "lookup_layer.tpl.html", 'smarty_include_vars' => array('list' => $this->_tpl_vars['assoc_users'],'multiple' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>', 'send_email_form', 'cc'), STICKY, HEIGHT, 50, WIDTH, 160, BELOW, LEFT, CLOSECOLOR, '#FFFFFF', FGCOLOR, '#FFFFFF', BGCOLOR, '#333333', CAPTION, 'Lookup Details', CLOSECLICK);" onMouseOut="javascript:nd();"><img src="<?php echo $this->_tpl_vars['rel_url']; ?>
images/lookup.gif" border="0"></a><?php endif; ?>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;
            
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="checkbox" name="add_unknown" value="yes">
            <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('send_email_form', 'add_unknown');">Add Unknown Recipients to Issue Notification List</a>
          </td>
        </tr>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">
            <b>Subject: *</b>
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <input type="text" name="subject" class="default" size="50" value="<?php if ($_GET['cat'] == 'view_draft'):  echo ((is_array($_tmp=$this->_tpl_vars['email']['sup_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  else:  echo ((is_array($_tmp=$this->_tpl_vars['email']['reply_subject'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?>">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'subject')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" colspan="2">
            <?php if ($this->_tpl_vars['canned_responses']): ?>
            <span class="default"><b>Canned Responses:</b></span>
            <select name="email_response" class="default">
              <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['canned_responses']), $this);?>

            </select>&nbsp;<input type="button" class="shortcut" value="Use Canned Response" onClick="javascript:setResponseBody(this.form);"><br />
            <?php endif; ?>
            <textarea name="message" rows="22" style="width: 97%"><?php echo ((is_array($_tmp=$this->_tpl_vars['email']['seb_body'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  if ($this->_tpl_vars['current_user_prefs']['auto_append_sig'] == 'yes'): ?>


<?php echo ((is_array($_tmp=$this->_tpl_vars['current_user_prefs']['email_signature'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'));  endif; ?></textarea>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'message')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['current_user_prefs']['email_signature'] != "" && $this->_tpl_vars['current_user_prefs']['auto_append_sig'] != 'yes'): ?>
        <tr>
          <td width="120" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" class="default_white">&nbsp;
            
          </td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <input type="checkbox" name="add_email_signature" value="yes" onClick="javascript:setSignature(this.form);">
            <a id="link" class="link" href="javascript:void(null);" onClick="javascript:toggleCheckbox('send_email_form', 'add_email_signature');setSignature(getForm('send_email_form'));">Add Email Signature To Email Body</a>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center">
                  <input class="button" type="submit" value="Send Email">&nbsp;&nbsp;
                  <input class="button" type="reset" value="Reset">&nbsp;&nbsp;
                  <input class="button" type="button" value="Cancel" onClick="javascript:window.close();">
                </td>
                <?php if ($this->_tpl_vars['app_setup']['spell_checker'] == 'enabled'): ?>
                <td align="right" width="150">
                  <input class="button" type="button" value="Check Spelling" onClick="javascript:checkSpelling('send_email_form', 'message');">
                </td>
                <?php endif; ?>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['dark_color']; ?>
" colspan="2">
            <?php if ($_GET['cat'] == 'view_draft'): ?>
            <input type="button" class="button" value="Save Draft Changes" onClick="javascript:updateDraft(this.form);">
            <?php else: ?>
            <input type="button" class="button" value="Save as Draft" onClick="javascript:saveDraft(this.form);">
            <?php endif; ?>
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
</table>
</form>

<?php if ($this->_tpl_vars['parent_email_id'] || $_GET['cat'] == 'reply' || $_GET['cat'] == 'forward'): ?>
<?php echo '
<script language="JavaScript">
<!--
window.onload = focusMessageBox;
function focusMessageBox()
{
    var f = getForm(\'send_email_form\');
    f.message.focus();
}
//-->
</script>
'; ?>

<?php endif; ?>

<?php endif; ?>
