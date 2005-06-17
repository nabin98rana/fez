<?php /* Smarty version 2.6.2, created on 2005-01-28 14:25:40
         compiled from reports/askit_reports.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_select_date', 'reports/askit_reports.tpl.html', 35, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<br />

<input type="hidden" name="cat" value="generate">
<table bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center" width="400">
  <tr>
    <td>
      <table bgcolor="#FFFFFF" width="100%" cellspacing="1" cellpadding="2" border="0" >
        <tr>
          <td bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" colspan="4" class="default_white">
            <b>AskIT Reports Statistics</b>
          </td>
        </tr>
 
        <tr bgcolor="#FFCC99">
          <td align="left"  class="default">
			<b>Report Title</b>
          </td>
          <td colspan="2" align="left" class="default" nowrap>
			<b>Selection Criteria</b>
          </td>
          <td align="left" class="default" nowrap>
			<b>Delivery Method</b>
          </td>
        </tr>
		<tr>
		<form action="askit_desk_stats.php" name="email_enquiry" method="get">
          <td align="left" class="default" nowrap>
			Desk Enquiry Statistics
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => true), $this);?>

			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'EndDate','time' => $this->_tpl_vars['time'],'display_years' => false,'display_days' => false,'display_months' => true), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="desk_enquiry_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_email_enq.php" name="email_enquiry" method="get">
          <td align="left" class="default" nowrap>
			Email Enquiries Received Statistics
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => true), $this);?>

			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'EndDate','time' => $this->_tpl_vars['time'],'display_years' => false,'display_days' => false,'display_months' => true), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="email_enquiry_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_phone_enq.php" name="phone_enquiry" method="get">
          <td align="left" class="default" nowrap>
			Phone Enquiries Received Statistics
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => true), $this);?>

			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'EndDate','time' => $this->_tpl_vars['time'],'display_years' => false,'display_days' => false,'display_months' => true), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="phone_enquiry_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_num_emails.php" name="num_emails" method="get">
          <td align="left" class="default" nowrap>
			Email Enquiries (Total Inclusive)
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="num_emails_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_types_phone.php" name="phone_category" method="get">
          <td align="left" class="default" nowrap>
			Telephone Enquiries by Category
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="phone_category_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_types_face_to_face.php" name="desk_category" method="get">
          <td align="left" class="default" nowrap>
			Desk Enquiries by Category
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="desk_category_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
		<tr>
		<form action="askit_total_logged.php" name="all_enquiries" method="get">
          <td align="left" class="default" nowrap>
			All Enquiries
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td nowrap>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="all_enquiries_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>

        <tr>
          <td align="left" class="default" nowrap>&nbsp;
			
          </td>
          <td align="left" class="default">&nbsp;
			
          </td>
          <td align="left" class="default">&nbsp;
			
          </td>
        </tr>




      </table>
    </td>
  </tr>
</table>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>