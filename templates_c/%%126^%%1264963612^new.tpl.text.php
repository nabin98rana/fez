<?php /* Smarty version 2.6.2, created on 2004-06-25 14:09:45
         compiled from notifications/new.tpl.text */ ?>
This is an automated message sent at your request from <?php echo $this->_tpl_vars['app_title']; ?>
.

A new issue was just created and assigned to you.

To view more details of this issue, or to update it, please visit the 
following URL:
<?php echo $this->_tpl_vars['app_base_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['issue']['iss_id']; ?>


----------------------------------------------------------------------
               ID: <?php echo $this->_tpl_vars['issue']['iss_id']; ?>

          Summary: <?php echo $this->_tpl_vars['issue']['iss_summary']; ?>
 
          Project: <?php echo $this->_tpl_vars['issue']['prj_title']; ?>

      Reported By: <?php echo $this->_tpl_vars['issue']['usr_full_name']; ?>

         Priority: <?php echo $this->_tpl_vars['issue']['pri_title']; ?>

      Description:
----------------------------------------------------------------------
<?php echo $this->_tpl_vars['issue']['iss_description']; ?>

----------------------------------------------------------------------

Please Note: If you do not wish to receive any future email 
notifications from <?php echo $this->_tpl_vars['app_title']; ?>
, please change your account preferences by 
visiting the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
preferences.php
