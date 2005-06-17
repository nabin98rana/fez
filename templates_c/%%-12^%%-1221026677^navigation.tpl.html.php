<?php /* Smarty version 2.6.2, created on 2005-06-10 14:58:29
         compiled from navigation.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'navigation.tpl.html', 36, false),)), $this); ?>

<body bgcolor="<?php if ($this->_tpl_vars['bgcolor']):  echo $this->_tpl_vars['bgcolor'];  else: ?>#FFFFFF<?php endif; ?>" marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0">
<link rel="stylesheet" href="/css/uql-home.css" type="text/css">
<!--Header starts here -->
<a name="top"></a>
<!--START: Header -->
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-header" summary="UQ header">
     <tr>
       <td width="10%" rowspan="2"><a href="http://www.uq.edu.au/"><img src="/images/2003/uq_logo.gif" width="200" height="72" alt="The University of Queensland Homepage" border="0"></a></td>
       <td width="60%" class="toplinks" align="left"><img src="/images/2003/top-links.gif" width="364" height="17" usemap="#Map" border="0" alt=""><map name="Map" id="map"><area shape="rect" coords="23,2,81,15" href="http://www.uq.edu.au/" alt="UQ Home"><area shape="rect" coords="98,2,151,15" href="http://www.uq.edu.au/search/" alt="Search UQ"><area shape="rect" coords="164,2,204,15" href="http://www.uq.edu.au/maps/" alt="UQ Maps"><area shape="rect" coords="221,2,286,15" href="http://www.uq.edu.au/contacts/" alt="UQ Contacts"><area shape="rect" coords="302,2,356,16" href="http://www.library.uq.edu.au/" alt="Cybrary"></map></td>
       <td width="30%" class="toplinks" align="right"><a href="http://www.my.uq.edu.au/"><img src="/images/2003/my-uq.gif" width="69" height="17" alt="Link to my.UQ" border="0"></a></td>
     </tr>
     <tr>
       <td width="100%" class="header" align="left" background="/images/2003/header.gif" colspan="2">
          <table cellpadding=0 cellspacing=0 border=0>
            <tr><td valign="top"><img src="/images/2003/linktext2.gif" width="155" height="55" alt="CYBRARY - We link people with information" align="absmiddle"></td>
                <td  background="/images/2003/header2.gif" width=100%><img src="/images/2003/top-right2.jpg" width="204" height="55" alt="" border="0" align="right"><div style="font-size: 16px"></div>
<!-- -------------- Put your page heading below here --------------- -->
<img align="top" style="position:absolute; z-index:10; "  src="/images/espace_header.gif"/>
<!-- -------------- Put your page heading above here --------------- -->
              </td></tr>
          </table></td></tr>
   </tbody>
  </table>
<!--END: Header -->

<!-- -- -- -- -- -- -- -- -- -- -- page content starts here -- -- -- -- -- -- -- -- -- -- -->

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
        <tr>
		  
          <td class="default_white">
            <b><?php echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
</b> <?php if ($this->_tpl_vars['isUser']): ?>(<a title="logout from <?php echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
" target="_top" href="<?php echo $this->_tpl_vars['rel_url']; ?>
logout.php" class="white_link">Logout</a>)<?php else: ?>(<a title="login to <?php echo ((is_array($_tmp=@$this->_tpl_vars['app_setup']['tool_caption'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['application_title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['application_title'])); ?>
" target="_top" href="<?php echo $this->_tpl_vars['rel_url']; ?>
login.php" class="white_link">Login</a>)<?php endif; ?>
          </td>
          
          <td align="right" class="default_white">
            <?php if ($this->_tpl_vars['isAdministrator']): ?>
            <img src="/images/administrator_16.png" align="absmiddle"/> <a target="_top" title="manage the application settings, users, etc" href="<?php echo $this->_tpl_vars['rel_url']; ?>
manage/collections.php" class="white_link">Administration</a>&nbsp;|
            <?php endif; ?>
            <img src="/images/community_16.png" align="absmiddle"/> <a target="_top" title="list the communities stored in eSpace" href="<?php echo $this->_tpl_vars['rel_url']; ?>
list.php" class="white_link">List Communities</a>&nbsp;|
            <?php if ($this->_tpl_vars['isAdministrator']): ?><img src="/images/adv_search_16.png" align="absmiddle"/> <a target="_top" title="get access to advanced search parameters" href="<?php echo $this->_tpl_vars['rel_url']; ?>
adv_search.php" class="white_link">Advanced Search</a>&nbsp;|<?php endif; ?>
            <?php if ($this->_tpl_vars['isAdministrator']): ?>
            <img src="/images/my_espace_16.png" align="absmiddle"/> <a target="_top" title="" href="<?php echo $this->_tpl_vars['rel_url']; ?>
list.php?users=<?php echo $this->_tpl_vars['current_user_id']; ?>
" class="white_link">My eSpace</a>&nbsp;|
            <?php endif; ?>
            <img src="/images/help.gif" align="absmiddle"/> <a title="help documentation" href="javascript:void(null);" onClick="javascript:openHelp('<?php echo $this->_tpl_vars['rel_url']; ?>
', 'main');" class="help">Help</a>&nbsp;
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="4" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
">
        <tr>
          <?php echo '
          <script language="JavaScript">
          <!--
          function setCurrentCollection()
          {
              var features = \'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no\';
              var projWin = window.open(\'\', \'_active_collection\', features);
              projWin.focus();
              return true;
          }
          //-->
          </script>
          '; ?>

		  <?php if ($this->_tpl_vars['isUser']): ?>
          <td width="50%" nowrap bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
            <b>User: <?php echo $this->_tpl_vars['current_full_name']; ?>
</b> (<a target="_top" title="modify your account details and preferences" href="<?php echo $this->_tpl_vars['rel_url']; ?>
preferences.php" class="link">Preferences</a>)
          </td>
		  <?php endif; ?>
 <?php if ($this->_tpl_vars['isAdministrator']): ?>
          <form target="_top" method="get" action="<?php echo $this->_tpl_vars['rel_url']; ?>
list.php">
          <td width="5%" nowrap bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
">
            <img src="/images/search_16.png" align="absmiddle"/> <label for="search" accesskey="3"></label>
            <input type="text" id="search" name="terms" value="terms" size="15" 
              onBlur="javascript:if (this.value == '') this.value = 'terms';" onFocus="javascript:if (this.value == 'terms') this.value='';" class="shortcut">
            <input type="submit" value="Search" class="shortcut">
          </td>
          </form>
<?php endif; ?>
          <form target="_top" method="get" action="<?php echo $this->_tpl_vars['rel_url']; ?>
view.php">
          <td width="2%" nowrap bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" align="right">
            <label for="shortcut" accesskey="4"></label>
            <input type="text" id="shortcut" name="id" value="pid #" 
              onBlur="javascript:if (this.value == '') this.value = 'pid #';" onFocus="javascript:if (this.value == 'pid #') this.value='';" size="5" class="shortcut">
            <input type="submit" value="Go" class="shortcut">
          </td>
          </form>
        </tr>
      </table>
    </td>
  </tr>
</table>

<?php if ($this->_tpl_vars['show_line'] != 'no'): ?>
<hr size="1" noshade color="<?php echo $this->_tpl_vars['cell_color']; ?>
">
<?php endif; ?>