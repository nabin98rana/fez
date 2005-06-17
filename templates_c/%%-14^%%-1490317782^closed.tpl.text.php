<?php /* Smarty version 2.6.2, created on 2004-07-26 09:41:00
         compiled from notifications/closed.tpl.text */ ?>
This is an automated message sent at your request from <?php echo $this->_tpl_vars['app_title']; ?>
.

This issue was just closed.

To view more details of this issue, or to update it, please visit the 
following URL:
<?php echo $this->_tpl_vars['app_base_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['data']['iss_id']; ?>


----------------------------------------------------------------------
               ID: <?php echo $this->_tpl_vars['data']['iss_id']; ?>

          Summary: <?php echo $this->_tpl_vars['data']['iss_summary']; ?>

           Status: <?php echo $this->_tpl_vars['data']['sta_title']; ?>

          Project: <?php echo $this->_tpl_vars['data']['prj_title']; ?>

      Reported By: <?php echo $this->_tpl_vars['data']['usr_full_name']; ?>

         Priority: <?php echo $this->_tpl_vars['data']['pri_title']; ?>

      Description:
----------------------------------------------------------------------
<?php echo $this->_tpl_vars['data']['iss_description']; ?>

----------------------------------------------------------------------

Please Note: If you do not wish to receive any future email 
notifications from <?php echo $this->_tpl_vars['app_title']; ?>
, please change your account preferences by 
visiting the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
preferences.php
