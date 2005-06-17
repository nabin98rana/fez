<?php /* Smarty version 2.6.2, created on 2004-12-17 12:28:54
         compiled from reports/quarterly_reports.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_select_date', 'reports/quarterly_reports.tpl.html', 45, false),)), $this); ?>
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
            <b>Quarterly Reports Statistics</b>
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
          <td align="left" class="default" nowrap>
			Total Number of Jobs Logged
          </td>
          <td align="left" class="default" colspan="2">
			N/A
          </td>
          <td align="center" class="default">
			 <a href="total_logged.php">Link</a> &nbsp;
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
        <tr>		  <form action="completion_rate_jobs.php" name="completion_rate_jobs" method="get">
          <td align="left" class="default" nowrap>
			Completion Rate of Jobs by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="completion_rate_jobs_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>
        </tr>
		</form>
        <tr><form action="num_jobs_branch.php" name="num_jobs_branch" method="get">
          <td align="left" class="default" nowrap>
			Number of Jobs Logged by Branch, Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="num_jobs_branch_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
</form>
        <tr>

          <td align="left" class="default" nowrap><form action="method_jobs_logged.php" name="methods_jobs_logged" method="get">
			Methods by which Jobs were Logged by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="method_jobs_logged_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
</form>
        <tr><form action="num_phone_enquiries.php" name="num_phone_enquiries" method="get">
          <td align="left" class="default" nowrap>
			Number of Telephone Enquiries
          </td>
          <td align="left" class="default" colspan="2">
			N/A
          </td>
          <td align="center" class="default">
			 <input type="submit" name="num_phone_enquiries_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
</form>
        <tr><form action="num_emails.php" name="num_emails" method="get">
          <td align="left" class="default" nowrap>
			Number of Emails to Current Team Email Addresses
          </td>
          <td align="left" class="default" colspan="2">
			N/A
          </td>
          <td align="center" class="default">
			 <input type="submit" name="num_emails_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
</form>
        <tr><form action="visits_off_campus_branches.php" name="visits_off_campus_branches" method="get">
          <td align="left" class="default" nowrap>
			Visits to Off Campus Branches by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="visits_off_campus_branches_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
</form>
        <tr><form action="types_jobs_logged.php" name="types_jobs_logged" method="get">
          <td align="left" class="default" nowrap>
			Types of Jobs Logged by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="types_jobs_logged_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
		</form>
        <tr><form action="num_vendor_service_requests.php" name="num_vendor_service_requests" method="get">
          <td align="left" class="default" nowrap>
			Number of Vendor Service Requests by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="num_vendor_service_requests_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
		</form>
        <tr>
          <td align="left" class="default" nowrap>
			Number of Jobs Completed by WSS / LTS by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
        <tr>
          <td align="left" class="default" nowrap>
			Time Spent Completing Jobs by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
        <tr>
          <td align="left" class="default" nowrap>
			Number of Jobs Logged by Branch, Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
        <tr>
          <td align="left" class="default" nowrap>
			Email Stats by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
	<form action="branch_stats.php" name="branch_stats" method="get">
        <tr>
          <td align="left" class="default" nowrap>
			Branch Stats of Months, Hours, Methods Logged by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="branch_stats_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
	</form>
	<form action="branch_time.php" name="branch_time" method="get">
        <tr>
          <td align="left" class="default" nowrap>
			Branch Time of Months by Year
          </td>
          <td align="left" class="default">
			Year:
		  </td>
		  <td>
			<?php echo smarty_function_html_select_date(array('all_extra' => "class='default'",'prefix' => 'StartDate','time' => $this->_tpl_vars['time'],'start_year' => "-5",'end_year' => "+1",'display_days' => false,'display_months' => false), $this);?>

          </td>
          <td align="center" class="default">
			 <input type="submit" name="branch_time_button" value="Link">
			 <input alt="generate excel-friendly report" type="image" src="<?php echo $this->_tpl_vars['app_base_url']; ?>
images/excel.jpg" class="shortcut" value="Export to Excel">
          </td>        </tr>
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