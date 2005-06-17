<?php /* Smarty version 2.6.2, created on 2004-08-18 00:25:03
         compiled from phone_support.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'phone_support.tpl.html', 45, false),array('modifier', 'capitalize', 'phone_support.tpl.html', 75, false),array('function', 'get_innerhtml', 'phone_support.tpl.html', 49, false),array('function', 'get_display_style', 'phone_support.tpl.html', 53, false),array('function', 'cycle', 'phone_support.tpl.html', 65, false),array('function', 'html_select_date', 'phone_support.tpl.html', 97, false),array('function', 'html_select_time', 'phone_support.tpl.html', 98, false),)), $this); ?>

<?php echo '
<script language="JavaScript">
<!--
function validatePhoneSupportForm(f)
{
    if ((isWhitespace(f.call_length.value)) || (!isNumberOnly(f.call_length.value))) {
        alert(\'Please enter integers (or floating point numbers) on the time spent field.\');
        selectField(f, \'call_length\');
        return false;
    }
    if (isWhitespace(f.description.value)) {
        alert(\'Please enter the description for this new phone support entry.\');
        selectField(f, \'description\');
        return false;
    }
    var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
    var popupWin = window.open(\'\', \'_phone_support\', features);
    popupWin.focus();
    return true;
}
function deletePhoneEntry(phone_id)
{
    if (!confirm(\'This action will permanently delete the specified phone support entry.\')) {
        return false;
    } else {
        var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
        var popupWin = window.open(\'popup.php?cat=delete_phone&id=\' + phone_id, \'_popup\', features);
        popupWin.focus();
    }
}
//-->
</script>
'; ?>

<br />
<table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
<form name="add_phone_form" target="_phone_support" onSubmit="javascript:return validatePhoneSupportForm(this);" method="post" action="<?php echo $this->_tpl_vars['rel_url']; ?>
popup.php">
<input type="hidden" name="cat" value="add_phone">
<input type="hidden" name="issue_id" value="<?php echo $_GET['id']; ?>
">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
        <tr>
          <td class="default">
          <b>Phone Calls (<?php echo count($this->_tpl_vars['phone_entries']); ?>
)</b>
        </td>
        <td align="right" class="default">
            <?php if ($this->_tpl_vars['browser']['ie5up'] || $this->_tpl_vars['browser']['ns6up'] || $this->_tpl_vars['browser']['gecko']): ?>
            [ <a id="phone_support_link" class="link" href="javascript:void(null);" onClick="javascript:toggleVisibility('phone_support');"><?php echo smarty_function_get_innerhtml(array('element_name' => 'phone_support'), $this);?>
</a> ]
            <?php endif; ?>
        </td>
        </tr>
        <tr id="phone_support1" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td colspan="2">
            <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2">
              <tr bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
">
                <td class="default_white" NOWRAP><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('remote_func' => 'getPhoneSupport','ec_id' => 'phone')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td width="20%" class="default_white">Recorded Date</td>
                <td width="20%" class="default_white">User</td>
                <td width="20%" class="default_white">Call Type</td>
                <td width="20%" class="default_white">Reason</td>
                <td width="20%" class="default_white">Phone Number</td>
              </tr>
              <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['phone_entries']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
              <?php echo smarty_function_cycle(array('values' => $this->_tpl_vars['cycle'],'assign' => 'row_color'), $this);?>

              <tr bgcolor="<?php echo $this->_tpl_vars['row_color']; ?>
">
                <td class="default" NOWRAP><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/buttons.tpl.html", 'smarty_include_vars' => array('ec_id' => 'phone','list_id' => $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <td class="default"><?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_created_date']; ?>
</td>
                <td class="default">
                    <?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['usr_full_name']; ?>

                  <?php if ($this->_tpl_vars['current_user_id'] == $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_usr_id']): ?>
                      [ <a class="link" href="javascript:void(null);" onClick="javascript:deletePhoneEntry(<?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_id']; ?>
);">delete</a> ]
                  <?php endif; ?>
                </td>
                <td class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_type'])) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp)); ?>
</td>
                <td class="default"><?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_reason']; ?>
</td>
                <td class="default"><?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_phone_number']; ?>
 (<?php echo $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_phone_type']; ?>
)</td>
              </tr>
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "expandable_cell/body.tpl.html", 'smarty_include_vars' => array('ec_id' => 'phone','list_id' => $this->_tpl_vars['phone_entries'][$this->_sections['i']['index']]['phs_id'],'colspan' => '6')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
              <?php endfor; else: ?>
              <tr>
                <td colspan="6" bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default" align="center">
                  <i>No phone calls recorded yet.</i>
                </td>
              </tr>
              <?php endif; ?>
            </table>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['customer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
        <tr id="phone_support2" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td colspan="2" class="default"><b>Record Phone Call:</b></td>
        </tr>
        <tr id="phone_support3" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Date of Call:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <?php echo smarty_function_html_select_date(array('day_value_format' => '%02d','field_array' => 'date','prefix' => '','all_extra' => ' class="default"'), $this);?>
&nbsp;
            <?php echo smarty_function_html_select_time(array('minute_interval' => 5,'field_array' => 'date','prefix' => '','all_extra' => ' class="default"','display_seconds' => false), $this);?>

            <a href="javascript:void(null);" onClick="javascript:updateTimeFields('add_phone_form', 'date[Year]', 'date[Month]', 'date[Day]', 'date[Hour]', 'date[Minute]');"><img src="images/icons/refresh.gif" border="0"></a>
          </td>
        </tr>
        <tr id="phone_support4" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Reason:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <select class="default" name="reason">
              <option value="admin">Admin</option>
              <option value="booking">Booking</option>
              <option value="bugfix">Bugfix</option>
              <option value="configure">Configure</option>
              <option value="file transfer">File Transfer</option>
              <option value="imaging">Imaging</option>
              <option value="install">Install</option>
              <option value="meeting">Meeting</option>
              <option value="planning">Planning</option>
              <option value="removal">Removal</option>
              <option value="research">Research</option>
              <option value="service call liaison">Service Call Liaison</option>
              <option value="student support">Student Support</option>
              <option value="tech-support">Tech-Support</option>
              <option value="testing">Testing</option>
              <option value="training">Training</option>
            </select>
          </td>
        </tr>
        <tr id="phone_support5" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Call From:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <input type="text" class="default" name="from_lname" value="last name" onFocus="javascript:if (this.value == 'last name') this.value='';"><span class="default">,</span>
            <input type="text" class="default" name="from_fname" value="first name" onFocus="javascript:if (this.value == 'first name') this.value='';">
          </td>
        </tr>
        <tr id="phone_support6" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Call To:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <input type="text" class="default" name="to_lname" value="last name" onFocus="javascript:if (this.value == 'last name') this.value='';"><span class="default">,</span>
            <input type="text" class="default" name="to_fname" value="first name" onFocus="javascript:if (this.value == 'first name') this.value='';">
          </td>
        </tr>
        <tr id="phone_support7" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Type:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <select class="default" name="type">
              <option value="incoming">Incoming</option>
              <option value="outgoing">Outgoing</option>
            </select>
          </td>
        </tr>
        <tr id="phone_support8" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Customer Phone Number:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%">
            <input type="text" size="20" maxlength="32" name="phone_number" class="default">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'phone_number')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <select class="default" name="phone_type">
              <option value="office">Office</option>
              <option value="home">Home</option>
              <option value="mobile">Mobile</option>
              <option value="temp">Temp Number</option>
              <option value="other">Other</option>
            </select>
          </td>
        </tr>
        <tr id="phone_support9" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Time Spent:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" width="100%"><input type="text" size="5" name="call_length" class="default"> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'call_length')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> <span class="default">(in minutes)</span></td>
        </tr>
        <tr id="phone_support10" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" class="default_white" width="190" nowrap><b>Description:</b></td>
          <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <textarea name="description" rows="8" style="width: 97%"></textarea>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "error_icon.tpl.html", 'smarty_include_vars' => array('field' => 'description')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <span class="small_default"><i>(internal only; not visible to customers)</i></span>
          </td>
        </tr>
		<?php if ($this->_tpl_vars['current_role'] > $this->_tpl_vars['roles']['viewer'] || $this->_tpl_vars['is_user_assigned'] == 'true'): ?>
        <tr id="phone_support11" <?php echo smarty_function_get_display_style(array('element_name' => 'phone_support'), $this);?>
>
          <td colspan="2" bgcolor="<?php echo $this->_tpl_vars['internal_color']; ?>
" align="center">
            <input type="submit" value="Save Phone Call" class="button">
          </td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</form>
</table>
<script language="JavaScript">
<!--
updateTimeFields('add_phone_form', 'date[Year]', 'date[Month]', 'date[Day]', 'date[Hour]', 'date[Minute]');
//-->
</script>
