<?php /* Smarty version 2.6.2, created on 2005-03-03 15:51:43
         compiled from app_info.tpl.html */ ?>



<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <?php if ($this->_tpl_vars['benchmark_total']): ?>
  <?php echo '
  <script language="JavaScript">
  <!--
  function openBenchmark()
  {
      var f = getForm(\'benchmark_form\');
      var width = 500;
      var height = 450;
      var w_offset = 30;
      var h_offset = 30;
      var location = \'top=\' + h_offset + \',left=\' + w_offset + \',\';
      if (screen.width) {
          location = \'top=\' + h_offset + \',left=\' + (screen.width - (width + w_offset)) + \',\';
      }
      var features = \'width=\' + width + \',height=\' + height + \',\' + location + \'resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
      var benchmarkWin = window.open(\'\', \'_benchmark\', features);
      f.submit();
      benchmarkWin.focus();
  }
  //-->
  </script>
  '; ?>

  <form name="benchmark_form" target="_benchmark" method="post" action="<?php echo $this->_tpl_vars['rel_url']; ?>
benchmark.php">
  <input type="hidden" name="encoded_stats" value="<?php echo $this->_tpl_vars['benchmark_results']; ?>
">
  <?php endif; ?>
  <tr>
	<td class="feedback">&nbsp;
		
	</td>
    <TD class="feedback"  class=uqfooterfeedback align=right colSpan=2 height=10><A
     class="uqfeedback" style="TEXT-DECORATION: none"
      href="http://www.library.uq.edu.au/privacy.html">privacy</A> | <A class="uqfeedback"
      style="TEXT-DECORATION: none"
      href="http://www.library.uq.edu.au/webmaster.html">feedback</A>&nbsp;</TD>
</tr>
  <tr>
    <td <?php if ($this->_tpl_vars['benchmark_total']): ?>width="60%"<?php endif; ?> valign="top" class="footer">
      &nbsp;<?php echo $this->_tpl_vars['application_title']; ?>
 <?php echo $this->_tpl_vars['application_version']; ?>
<br />
      &nbsp;Copyright © 2005 <a href="http://www.library.uq.edu.au/escholarship" class="link" title="APSR eScholarship">APSR UQ eScholarship</a>.
    <?php if ($this->_tpl_vars['benchmark_total']): ?>
	  <br />
      &nbsp;Page generated in <?php echo $this->_tpl_vars['benchmark_total']; ?>
 seconds <?php if ($this->_tpl_vars['total_queries']): ?>(<?php echo $this->_tpl_vars['total_queries']; ?>
 queries)<?php endif; ?>    
    <?php endif; ?>
    </td>
	<td>
<TABLE cellSpacing=2 cellPadding=0 width="100%" border=0>
  <TBODY>
  <TR>
    <TD width="50%"></TD>
    <TD class=uqfooter vAlign=top align=right>©2004 The University of
      Queensland, Brisbane Australia</TD></TR>
  <TR>
    <TD width="50%"></TD>
    <TD class=uqfooter vAlign=top align=right>ABN 63 942 912 684</TD></TR>
  <TR>
    <TD width="50%"></TD>
    <TD class=uqfooter vAlign=top align=right>University Provider Number:
      00025B</TD></TR>
  <TR>
    <TD width="50%"></TD>
    <TD class=uqfooter vAlign=top align=right>Authorised by: University
      Librarian</TD></TR>
  <TR>
    <TD width="50%"></TD>
    <TD class=uqfooter vAlign=top align=right>Maintained by: <A
      class=uqfooterlink href="mailto:webmaster@library.uq.edu.au">UQ
    Library</A></TD></TR>
  </TBODY>
</TABLE>
	</td>
  </tr>
  <?php if ($this->_tpl_vars['benchmark_total']): ?>
  </form>
  <?php endif; ?>
</table>